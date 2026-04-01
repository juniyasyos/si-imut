# Session Lifetime from IAM - Quick Reference

## 🎯 What's New

Session lifetime sekarang **dinamis dan dikontrol dari IAM server**!

```
SEBELUM:
├─ Session lifetime: hardcoded 120 min
├─ Token TTL: varies (15-60 min dari IAM)
└─ Gap: Token expires tapi session masih valid ❌

SESUDAH:
├─ Session lifetime: = Token TTL - buffer minutes
├─ Token TTL: per-app dari IAM (stored in applications.token_expiry)
└─ Aligned: Session expires saat token expires ✅
```

---

## ⚡ Quick Setup (5 minutes)

### Step 1: Set Token TTL in IAM Database

```bash
# Open IAM, go to Applications management
# Or run this SQL:

UPDATE applications 
SET token_expiry = 1800  -- 30 minutes (in seconds)
WHERE app_key = 'siimut';
```

### Step 2: Client Config (siimut/.env)

Already configured! Check:

```bash
# Should see:
IAM_SYNC_SESSION_LIFETIME=true      # Enabled
IAM_SESSION_LIFETIME_BUFFER=2       # 2 min buffer
```

### Step 3: Verify

```bash
# Login to app, then check:
php artisan tinker
session('iam.token_exp_at')
session('iam.session_lifetime')
```

---

## 📊 How It Works

### Session Lifetime Calculation

```
TOKEN_TTL (from IAM)  = 1800 seconds (30 min)
BUFFER (config)       = 2 minutes
SESSION_LIFETIME      = 30 - 2 = 28 minutes
```

### Timeline

```
T+0min    User Login
T+15min   User navigates (token & session valid) ✅
T+28min   Session timeout → auto logout
T+30min   Token would have expired anyway
```

### Per-App Configuration

Each application in IAM can have different token TTL:

```sql
-- Admin panel: 15 min (strict)
UPDATE applications SET token_expiry = 900 WHERE app_key = 'admin_panel';

-- Reporting: 30 min (balanced)
UPDATE applications SET token_expiry = 1800 WHERE app_key = 'reporting';

-- API: 60 min (relaxed)
UPDATE applications SET token_expiry = 3600 WHERE app_key = 'api_service';
```

---

## 🔧 Configuration

### Environment Variables (siimut/.env)

```bash
# Enable/disable dynamic session lifetime
IAM_SYNC_SESSION_LIFETIME=true

# Minutes to subtract from token TTL
# Prevents edge cases (token expires mid-request)
IAM_SESSION_LIFETIME_BUFFER=2
```

### Config File (siimut/config/iam.php)

```php
'sync_session_lifetime' => env('IAM_SYNC_SESSION_LIFETIME', true),
'session_lifetime_buffer_minutes' => env('IAM_SESSION_LIFETIME_BUFFER', 2),
```

---

## 📋 Available Components

### 1. TokenExpiryManager (New)

Extract & analyze token expiry:

```php
use Juniyasyos\IamClient\Support\TokenExpiryManager;

// Get expiry info
$expiry = TokenExpiryManager::extractExpiry($token);
// Returns: [exp, exp_at, remaining_seconds, remaining_minutes]

// Calculate session lifetime
$sessionTTL = TokenExpiryManager::calculateSessionLifetime($token); // 28 (minutes)

// Check if approaching expiry
if (TokenExpiryManager::isApproachingExpiry($token, 5)) {
    // Less than 5 minutes left
}

// Human-readable remaining time
$time = TokenExpiryManager::getRemainingTimeString($token); // "15m"
```

### 2. EnforceSessionTimeout Middleware (New)

Automatically added to handle session timeout:

```
✓ Monitor token expiry each request
✓ Force logout if token expired
✓ Warn user when < 5 min remaining
✓ Log all expiry events
```

### 3. Session Data (After Login)

Session automatically contains:

```php
// Token expiry information
session('iam.token_exp_at')              // "2024-04-01T15:30:45Z"
session('iam.token_expires_seconds')     // 1800
session('iam.token_expires_minutes')     // 30

// Calculated session lifetime
session('iam.session_lifetime')          // 28 (minutes)

// Warning flags
session('iam.token_expiring_soon')       // true if < 5 min
session('iam.token_minutes_remaining')   // 3 (if approaching expiry)
```

---

## 🧪 Testing

### Test 1: Check Token Expiry Info

```bash
# 1. Login to app
curl -X POST http://127.0.0.1:8088/login

# 2. In tinker, check session:
php artisan tinker
session('iam.token_exp_at')
session('iam.token_expires_minutes')
session('iam.session_lifetime')

# Should show token expiry information
```

### Test 2: Force Short Token TTL

```bash
# 1. Set very short TTL (development only!)
mysql siimut
UPDATE applications SET token_expiry = 10 WHERE app_key = 'siimut';  # 10 seconds

# 2. Restart app, login
# 3. Wait 10+ seconds
# 4. Try to navigate → should auto-logout ✅

# 5. RESTORE original TTL!
UPDATE applications SET token_expiry = 1800 WHERE app_key = 'siimut';
```

### Test 3: Monitor Logs

```bash
tail -f storage/logs/laravel.log | grep -i "token\|enhance"

# Should see entries:
# - "Token expiry information stored"
# - "Session lifetime synced"
# - "Token approaching expiry"
# - "Token expired, logging out"
```

---

## 🎓 Best Practices

### Token TTL Selection

| TTL | Use Case | Security | UX |
|-----|----------|----------|-----|
| 5-10 min | Admin/sensitive ops | ⭐⭐⭐⭐⭐ | ⭐ |
| 15-30 min | Standard web apps | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| 60 min | APIs/background jobs | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| 120 min | Long-running processes | ⭐⭐ | ⭐⭐⭐⭐⭐ |

### Production Checklist

- [ ] Set `token_expiry` per application in IAM
- [ ] Test token expiry scenario
- [ ] Monitor logs for expiry events
- [ ] Set `IAM_SYNC_SESSION_LIFETIME=true`
- [ ] Keep `IAM_SESSION_LIFETIME_BUFFER` at 2-5 min
- [ ] Enable HTTPS & HttpOnly cookies
- [ ] Document token TTL for ops team

---

## 🔗 References

- **Full Documentation**: `docs/SESSION-LIFETIME-CONFIGURATION.md`
- **Token Manager**: `laravel-iam-client/src/Support/TokenExpiryManager.php`
- **Enforcer Middleware**: `laravel-iam-client/src/Http/Middleware/EnforceSessionTimeout.php`
- **Config**: `config/iam.php`
- **Environment**: `.env`

---

## 🚀 Example Scenarios

### Scenario 1: Admin Panel (High Security)

```sql
-- IAM Database
UPDATE applications SET token_expiry = 600 WHERE app_key = 'admin';  -- 10 min
```

**Result**: Users auto-logout every 10 min of inactivity
```
Login → T+10min → Auto Logout → Must Re-Authenticate
```

### Scenario 2: Reporting App (Balanced)

```sql
UPDATE applications SET token_expiry = 1800 WHERE app_key = 'siimut';  -- 30 min
```

**Result**: Normal usage with session protection
```
Login → T+15min (working) → T+28min (timeout) → Login required
```

### Scenario 3: API Service (Relaxed)

```sql
UPDATE applications SET token_expiry = 7200 WHERE app_key = 'api';  -- 2 hours
```

**Result**: Long-running jobs can complete
```
Login → T+60min (API call running) → T+120min → Token expires
```

---

## ❓ FAQ

**Q: Gimana caranya set token TTL?**
A: Update `applications.token_expiry` di IAM database (dalam detik)

**Q: Kalau token TTL < session lifetime?**
A: Session akan auto-logout saat token expires. Ini yang kita mau! ✅

**Q: Bisa configure per-user?**
A: Saat ini per-aplikasi saja. Per-user perlu custom JWT payload.

**Q: Gimana kalau user sedang mengerjakan form 28 menit?**
A: Token akan expire, session logout. User perlu re-login. Ini by-design untuk security.

**Q: Traffic ke IAM endpoint /verify akan naik?**
A: Ya, karena setiap request di-verify. Tapi hanya di-call jika config `verify_remote_each_request=true`.

---

## ✅ Status

✓ Token expiry tracking implemented
✓ Dynamic session lifetime calculation implemented  
✓ EnforceSessionTimeout middleware created
✓ Configuration added to siimut/.env
✓ Documentation complete

**Ready for production!** 🚀
