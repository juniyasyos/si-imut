# Session Lifetime Configuration dari IAM Server

## 📌 Overview

Sistem IAM sekarang bisa **mengontrol session lifetime per aplikasi** melalui field `token_expiry` di database Applications. Session lifetime akan **otomatis tersinkronisasi** dengan token expiry dari IAM.

---

## 🎯 Fitur

### 1. **Token Expiry Tracking**
- Extract `exp` claim dari JWT token
- Store dalam session: `iam.token_exp_at`, `iam.token_expires_minutes`
- Know exactly when token will expire

### 2. **Dynamic Session Lifetime**
- Calculate session lifetime = token TTL - buffer
- Shorter buffer = stricter security
- Per-app configuration possible

### 3. **Session Timeout Enforcement**
- Middleware monitor token expiry
- Force logout if token expired
- Warn user when approaching expiry (< 5 min)

### 4. **Security Benefits**
- Session never outlives token
- No gap where token invalid but session valid
- Consistent across all apps

---

## 🔧 Implementation

### A. IAM Server Setup

#### 1. Set Token Expiry per Application

**In IAM Management Console or via API:**

```bash
# 1. Find application in database
SELECT id, app_key, token_expiry FROM applications WHERE app_key = 'siimut';

# 2. Update token_expiry (in seconds)
# Example: 1800 = 30 minutes
UPDATE applications
SET token_expiry = 1800  -- 30 minutes
WHERE app_key = 'siimut';

# 3. Verify
SELECT app_key, token_expiry FROM applications WHERE app_key = 'siimut';
```

**Default TTL:**
- Per-app: `applications.token_expiry` field
- Env override: `IAM_SSO_TOKEN_EXPIRY_SECONDS`
- Fallback: 3600 (1 hour)

#### 2. Common Token TTL Configurations

```bash
# Short-lived (very secure, frequent re-auth)
UPDATE applications SET token_expiry = 300   WHERE app_key = 'admin';    -- 5 minutes

# Standard (balance security & convenience)
UPDATE applications SET token_expiry = 1800  WHERE app_key = 'siimut';   -- 30 minutes

# Long-lived (less secure, good for background jobs)
UPDATE applications SET token_expiry = 3600  WHERE app_key = 'api';      -- 1 hour
```

### B. Client Application Setup

#### 1. Update Environment Variables

**File: `siimut/.env`**

```bash
# Enable session lifetime sync with token expiry (default: true)
IAM_SYNC_SESSION_LIFETIME=true

# Buffer in minutes subtracted from token TTL
# Prevents edge cases where token expires mid-request
IAM_SESSION_LIFETIME_BUFFER=2
```

#### 2. Add Middleware (Optional but Recommended)

**File: `siimut/bootstrap/app.php`**

Add session timeout enforcement middleware:

```php
->withMiddleware(function (Middleware $middleware) {
    // ... existing middleware ...
    
    // Add this to enforce token expiry-based session timeout
    $middleware->append(
        \Juniyasyos\IamClient\Http\Middleware\EnforceSessionTimeout::class
    );
})
```

#### 3. Cache Clear

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📊 How It Works

### Session Lifetime Calculation

```
Token TTL from IAM:           1800 seconds (30 minutes)
Buffer (IAM_SESSION_LIFETIME_BUFFER):  2 minutes
Session Lifetime:              30 - 2 = 28 minutes

Timeline:
T+0min   User logs in
T+15min  User navigates app (both token & session valid)
T+28min  Session timeout triggers in PHP
T+30min  Token technically expired (but user already logged out)
```

### Login Flow with Token Expiry Tracking

```
1. User clicks "Login via IAM"
   └─ Redirected to IAM login

2. IAM verifies credentials
   └─ Generates JWT with exp = now + token_expiry

3. IAM callback to client (/sso/callback)
   ├─ Token received
   ├─ IamClientManager.loginWithToken() executed
   ├─ TokenExpiryManager extracts exp claim
   ├─ Token expiry info stored in session:
   │  ├─ iam.token_exp_at
   │  ├─ iam.token_expires_minutes
   │  └─ iam.session_lifetime (calculated)
   └─ User logged in

4. Every request in app
   ├─ VerifyIamToken middleware runs
   │  ├─ Check token validity locally
   │  └─ Call IAM /verify endpoint
   │
   ├─ EnforceSessionTimeout middleware runs
   │  ├─ Check if token_exp_at < now
   │  ├─ If yes: force logout
   │  └─ If approaching: set warning flag
   │
   └─ Request proceeds (if both checks pass)

5. When token expires or session timeout reached
   ├─ User automatically logged out
   ├─ Session cleared
   ├─ Redirect to login page
   └─ User must re-authenticate
```

### Debug: Check Session Token Info

**Via Laravel Tinker:**

```bash
php artisan tinker

# Check token expiry info
session('iam.token_exp_at')          # "2024-04-01T15:30:45.000Z"
session('iam.token_expires_minutes')  # 28
session('iam.session_lifetime')       # 28 (minutes)
session('iam.token_expiring_soon')    # true if < 5 min left

# Check user
auth('web')->user()

# Calculate remaining
\Carbon\Carbon::parse(session('iam.token_exp_at'))->diffInMinutes(now());
```

---

## 📈 Scenarios

### Scenario 1: Standard Usage
```
Config:
  - Token TTL: 30 min (set in IAM)
  - Buffer: 2 min
  - Session lifetime: 28 min

Timeline:
  T+15min  User active (all OK)
  T+28min  Session timeout → auto logout
  T+30min  Token would have expired anyway
  
Result: ✅ Perfect alignment
```

### Scenario 2: Short-Lived Sensitive Operations
```
Config:
  - Token TTL: 5 min (admin panel)
  - Buffer: 1 min
  - Session lifetime: 4 min

Timeline:
  T+2min   User completes action
  T+4min   Session timeout → must re-auth
  T+5min   Token expired

Result: ✅ Strict security, frequent re-auth
```

### Scenario 3: Long-Running Jobs (API)
```
Config:
  - Token TTL: 2 hours
  - Buffer: 5 min
  - Session lifetime: 115 min

Timeline:
  T+60min    Job still running (all OK)
  T+115min   Session timeout → refresh if needed
  T+120min   Token expired

Result: ✅ Long jobs can complete
```

---

## 🛡️ Security Considerations

### Best Practices

| Setting | Value | Reason |
|---------|-------|--------|
| Token TTL | 15-60 min | Balance security & UX |
| Buffer | 2-5 min | Prevent mid-request expiry |
| Session Sync | true | Always enable |
| Remote Verify | true | Catch token revocations |

### Production Checklist

- [ ] Set per-app token_expiry in IAM database
- [ ] Enable `IAM_SYNC_SESSION_LIFETIME=true`
- [ ] Add `EnforceSessionTimeout` middleware
- [ ] Test token expiry logout
- [ ] Monitor logs for expiry events
- [ ] Set HTTPS-only cookies
- [ ] Use `HttpOnly` flag for session cookie

---

## 🧪 Testing

### Test 1: Verify Token Expiry Tracking

```bash
cd /home/juni/projects/siimut

# 1. Login to app
# Go to http://127.0.0.1:8088 and login via IAM

# 2. Check session
php artisan tinker
session('iam.token_exp_at')
session('iam.token_expires_minutes')
session('iam.session_lifetime')

# Should show token expiry info
```

### Test 2: Force Token Expiry (Development)

```bash
# 1. Modify IAM token_expiry to very short (testing only!)
# In IAM database:
UPDATE applications SET token_expiry = 10 WHERE app_key = 'siimut';  -- 10 seconds!

# 2. Restart app, login
# 3. Wait 10+ seconds
# 4. Try to navigate → should auto-logout

# 5. Remember to restore original TTL after test!
UPDATE applications SET token_expiry = 1800 WHERE app_key = 'siimut';  -- Back to 30 min
```

### Test 3: Monitor Logs

```bash
# Watch for session enforcement events
tail -f storage/logs/laravel.log | grep -E "EnforceSessionTimeout|Token"

# Should see entries like:
# - "Token approaching expiry" (< 5 min)
# - "Token has expired, logging out"
# - "Token expiry information stored"
```

---

## 📋 Configuration Reference

### Environment Variables

```bash
# Core Settings
IAM_SYNC_SESSION_LIFETIME=true           # Enable/disable feature
IAM_SESSION_LIFETIME_BUFFER=2            # Minutes to subtract from token TTL

# Token Verification
IAM_VERIFY_EACH_REQUEST=true             # Check token every request
IAM_VERIFY_REMOTE_EACH_REQUEST=true      # Call IAM /verify endpoint

# Session Management
SESSION_DRIVER=database                  # MUST be database (not file)
SESSION_LIFETIME=120                     # Fallback session lifetime (minutes)
```

### Config File Values

**File: `config/iam.php`**

```php
'sync_session_lifetime' => env('IAM_SYNC_SESSION_LIFETIME', true),
'session_lifetime_buffer_minutes' => env('IAM_SESSION_LIFETIME_BUFFER', 2),
```

---

## 🔗 Related Files

- **Token Expiry Manager**: `laravel-iam-client/src/Support/TokenExpiryManager.php`
- **Session Manager**: `laravel-iam-client/src/Services/IamClientManager.php`
- **Timeout Enforcement**: `laravel-iam-client/src/Http/Middleware/EnforceSessionTimeout.php`
- **Config**: `siimut/config/iam.php`
- **Environment**: `siimut/.env`

---

## 🎓 Architecture Decision

### Why Token TTL controls Session Lifetime

```
Session Lifetime Options:

Option A: Hardcoded (❌ BEFORE)
  └─ Session: 120 min (always)
  └─ Token: 30-3600 min (varies per app)
  └─ Gap: Token expires but session valid → SECURITY RISK

Option B: Synced with Token (✅ AFTER)
  └─ Session: = Token TTL - buffer
  └─ Token: per-app configured
  └─ Alignment: Session never outlives token → SECURE

Option C: Configured separately (❌ Not recommended)
  └─ Two config values to manage
  └─ Easy misconfiguration
  └─ Complex debugging
```

**Chosen: Option B (Token-driven Session Lifetime)**

---

## 💡 FAQ

### Q: Bagaimana jika token TTL < session lifetime?
A: Session lifetime akan di-set = token TTL - buffer. Pipeline lebih pendek.

### Q: Apakah bisa auto-refresh token sebelum expire?
A: Saat ini tidak. Best practice: set Token TTL = Session Lifetime. User re-login saat expire.

### Q: Apa jika buffer terlalu besar?
A: Session akan timeout sebelum token. User may lose unsaved work. Rekomendasi: 2-5 menit.

### Q: Bagaimana monitor expiry events?
A: Check logs: `grep "Token" storage/logs/laravel.log`. Atau setup log aggregation.

### Q: Bisa set expiry per-user?
A: Saat ini per-aplikasi. Per-user memerlukan custom JWT payload.

---

## 📝 Changelog

### Version 1.0 (April 2026)
- ✅ Token expiry tracking via JWT exp claim
- ✅ Dynamic session lifetime calculation
- ✅ EnforceSessionTimeout middleware
- ✅ Per-app token TTL configuration
- ✅ Session expiry warning for frontend
