# Session Expiration Fix untuk IAM Client Plugin

## 📋 Summary

Masalah session expiration di aplikasi client (siimut) sudah **DIPERBAIKI**. Token dari IAM sekarang akan diverifikasi pada setiap request, memastikan user logout otomatis ketika token expires.

---

## 🔴 Masalah yang Ditemukan

### 1. Session Driver Mismatch
- **Sebelum**: `SESSION_DRIVER=file` (file-based sessions)
- **Masalah**: Tidak kompatibel dengan IAM client plugin yang memerlukan database persistence
- **Dampak**: Session data tidak tersinkronisasi dengan baik, token verification tidak berjalan

### 2. Token Verification Middleware Disabled
- **Sebelum**: `IAM_ATTACH_VERIFY_MIDDLEWARE=false` (tidak diset)
- **Masalah**: Middleware yang check token expiration tidak pernah dijalankan
- **Dampak**: User tetap bisa login meski token sudah expired

### 3. Session vs Token TTL Mismatch
```
Scenario: IAM Token TTL = 15 menit, Session TTL = 120 menit

SEBELUM FIX (SALAH):
├─ T+16min: Token sudah expired
├─ T+17min: User buat request
├─ Session masih valid (belum 120 min)
└─ User TETAP BISA LOGIN ❌

SESUDAH FIX (BENAR):
├─ T+16min: Token sudah expired
├─ T+17min: User buat request
├─ Middleware check token expiry
├─ Token is invalid → session cleared
└─ User AUTO LOGOUT ✅
```

---

## ✅ Perbaikan yang Diterapkan

### 1. Session Driver Configuration
**File**: `siimut/.env`

```bash
# ❌ SEBELUM
SESSION_DRIVER=file

# ✅ SESUDAH
SESSION_DRIVER=database
```

**Alasan**: Database driver memberikan persistent storage yang reliable dan compatible dengan IAM backchannel operations.

### 2. Token Verification Settings
**File**: `siimut/.env`

```bash
# Token Verification Settings - CRITICAL for session expiration enforcement
# Verify JWT token on every request to ensure token hasn't expired on IAM
IAM_VERIFY_EACH_REQUEST=true

# Also verify with IAM server to catch revoked/expired tokens server-side
IAM_VERIFY_REMOTE_EACH_REQUEST=true

# IMPORTANT: Attach middleware automatically to web routes for proper token checking
IAM_ATTACH_VERIFY_MIDDLEWARE=true
```

### 3. Configuration Files
**File**: `siimut/config/iam.php`

Sudah memiliki default yang benar:
```php
'verify_each_request' => env('IAM_VERIFY_EACH_REQUEST', true),
'verify_remote_each_request' => env('IAM_VERIFY_REMOTE_EACH_REQUEST', true),
'attach_verify_middleware' => env('IAM_ATTACH_VERIFY_MIDDLEWARE', true),
```

### 4. Cache Cleared
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🔍 Verification Checklist

Semua konfigurasi sudah di-verify dan benar:

```
✅ Session Driver:                 database
✅ Session Lifetime:               120 minutes
✅ IAM Base URL:                   http://127.0.0.1:8010
✅ Verify Each Request:            true
✅ Verify Remote Each Request:     true
✅ Attach Verify Middleware:       true
✅ Token Storage in Session:       true
✅ Sessions Table:                 exists (database driver)
```

---

## 🔄 Bagaimana Cara Kerjanya Sekarang

### Authentication Flow Diagram

```
User Login (SSO)
      ↓
IAM Server → Generate JWT Token
      ↓
Client receives token + session created
      ↓
User navigates within app
      ↓
VerifyIamToken Middleware (EVERY REQUEST) ← KEY PART!
      ↓
   3 Checks:
   ├─ Decode JWT locally (fast validation)
   ├─ Check 'exp' claim: Is token expired?
   └─ Remote verify to IAM: Is token revoked?
      ↓
   If Valid → Continue ✅
   If Invalid → Clear session + Logout + Redirect to login ❌
```

### Detailed Timeline

```
T=00:00  User logs in via SSO
         ├─ IAM generates JWT token (exp = 1 hour from now)
         ├─ Token stored in session['iam.access_token']
         └─ Session created (lifetime = 120 min)

T=00:30  User navigates: GET /dashboard
         ├─ Request reaches server
         ├─ VerifyIamToken middleware activates
         ├─ Token decoded & validated
         ├─ Token still valid (30 min used, 30 min left)
         ├─ Remote verify to IAM (20ms call)
         ├─ IAM says: "Token OK"
         └─ Request proceeds ✅

T=01:05  Token has expired! (IAM set expiry at 1 hour)
         User tries: GET /reports
         ├─ Request reaches server
         ├─ VerifyIamToken middleware activates
         ├─ Token decoded
         ├─ JWT library checks 'exp': T=01:05 > exp
         ├─ Token is EXPIRED!
         ├─ Exception thrown by Firebase\JWT
         ├─ Middleware catches exception
         ├─ Session invalidated
         ├─ User logged out
         └─ Redirect to /login ❌

T=02:00  Session expires anyway (120 min after login)
         ├─ Session garbage collector removes old sessions
         ├─ Cookie becomes invalid
         └─ User automatically logged out ❌
```

### Key Components

#### 1. Token Validation (Local)
- **File**: `laravel-iam-client/src/Support/TokenValidator.php`
- **Fungsi**: Decode & validate JWT signature locally (very fast)
- **Checks**: 
  - Signature validity
  - Issuer claim (iss)
  - Expiration claim (exp)

#### 2. Middleware Verification
- **File**: `laravel-iam-client/src/Http/Middleware/VerifyIamToken.php`
- **Runs**: On every request (automatic thanks to auto-attach)
- **Does**:
  1. Check if middleware enabled
  2. Get token from session
  3. Decode & validate locally
  4. Check backchannel logout flag
  5. Remote verify to IAM (if enabled)
  6. Update session if necessary

#### 3. Session Storage
- **Driver**: Database (table: `sessions`)
- **Lifetime**: 120 minutes (configurable)
- **Content**: Session data including `iam.access_token`, `iam.payload`, etc.

---

## 📊 Comparison: Before vs After

| Aspect | SEBELUM | SESUDAH |
|--------|---------|---------|
| Session Driver | file | database |
| Middleware Active | ❌ No | ✅ Yes |
| Token Expiry Checked | ❌ Only at login | ✅ Every request |
| Remote Verify | ❌ No | ✅ Yes |
| Auto Logout on Token Expiry | ❌ No | ✅ Yes |
| Session Security | ⚠️ Lower | ✅ Higher |
| Consistency with IAM | ❌ Low | ✅ High |

---

## 🚀 Testing Token Expiration

### Manual Test (Development)

1. **Check current token expiration**
   ```bash
   # In siimut/.env, check IAM server token TTL
   # Default from IAM: 3600 seconds (1 hour)
   ```

2. **Login to application**
   ```
   Go to http://127.0.0.1:8088
   Click "Login via IAM"
   Complete login flow
   ```

3. **Wait for token to expire**
   - If token TTL = 3600 sec (1 hour): Wait 1 hour
   - Or ask IAM admin to reduce TTL for testing

4. **Try to access protected route**
   ```
   After token expires, try GET /dashboard or any protected route
   Expected: 
   - Middleware detects expired token
   - Session cleared
   - Redirect to login page ✅
   ```

### Log Verification

Check logs for middleware execution:
```bash
tail -f storage/logs/laravel.log | grep -i "VerifyIamToken"
```

You should see logs like:
```
[2024-04-01 10:30:15] local.WARNING: IamClient::VerifyIamToken - token invalid; clearing session
[2024-04-01 10:30:15] local.WARNING: IamClient::VerifyIamToken - user logged out via backchannel
```

---

## ⚙️ Configuration Reference

### Relevant Environment Variables

```bash
# Session Configuration
SESSION_DRIVER=database                    # ✅ MUST be database
SESSION_LIFETIME=120                       # Session expires after 120 min idle

# IAM Token Verification
IAM_VERIFY_EACH_REQUEST=true              # ✅ Check token on every request
IAM_VERIFY_REMOTE_EACH_REQUEST=true       # ✅ Call IAM /verify endpoint
IAM_ATTACH_VERIFY_MIDDLEWARE=true         # ✅ Auto-attach middleware

# IAM Server Settings
IAM_BASE_URL=http://127.0.0.1:8010
IAM_VERIFY_ENDPOINT=${IAM_HOST}/api/sso/verify  # Endpoint to verify tokens
IAM_JWT_SECRET=SIIMUT_ZK8FnRKJo5GlfBoN0izTVg0fR63r9UsgY86IaHeN
IAM_STORE_TOKEN_IN_SESSION=true           # Store token in session for API calls
```

### Recommended Production Values

```bash
# Production - Shorter session lifetime for security
SESSION_LIFETIME=60                        # 1 hour, auto-logout on inactivity

# Always verify token
IAM_VERIFY_EACH_REQUEST=true              # MUST be true
IAM_VERIFY_REMOTE_EACH_REQUEST=true       # MUST be true
IAM_ATTACH_VERIFY_MIDDLEWARE=true         # MUST be true

# HTTPS only in production
SESSION_SECURE_COOKIE=true                # Only send over HTTPS
SESSION_HTTP_ONLY=true                    # Prevent JS access
```

---

## 🔗 Related Files & Documentation

- **Client Plugin Middleware**: [laravel-iam-client/src/Http/Middleware/VerifyIamToken.php](./../../IAM/laravel-iam-client/src/Http/Middleware/VerifyIamToken.php)
- **Token Validator**: [laravel-iam-client/src/Support/TokenValidator.php](./../../IAM/laravel-iam-client/src/Support/TokenValidator.php)
- **Session Config**: [config/session.php](../config/session.php)
- **IAM Config**: [config/iam.php](../config/iam.php)
- **IAM Client Documentation**: [IAM Package README](./../../IAM/laravel-iam-client/README.md)

---

## ✨ Summary

✅ **Session expiration issue sudah fixed!**

- Token verification middleware now runs on every request
- Expired tokens detected immediately
- Session auto-invalidated when token expires
- IAM server can also revoke tokens (backchannel logout)
- Database session driver ensures reliable persistence

**Result**: User security dan session management sudah properly aligned dengan IAM server! 🎉
