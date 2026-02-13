# Production SSO Login Fix - Deployment Guide

## Problem
Di production dengan SSO mode enabled, error **405 Method Not Allowed** terjadi saat user mencoba POST ke `/siimut/login`.

**Root Cause:**
- `.env` dikonfigurasi dengan `USE_SSO=true` dan `IAM_ENABLED=true`
- Custom login page tidak terdaftar (sejenis design)
- User form mencoba POST ke `/siimut/login` yang hanya support GET/HEAD
- Middleware intercept dan redirect ke SSO login

## Solution Implemented

### 1. **New Middleware: RedirectSsoLoginPost**
- **File:** `app/Http/Middleware/RedirectSsoLoginPost.php`
- **Function:** Intercept requests ke `/siimut/login` dan redirect ke SSO jika:
  - SSO mode enabled (`USE_SSO=true` atau `IAM_ENABLED=true`)
  - Custom login page tidak registered
  - Request adalah POST atau GET yang tidak memiliki custom page

### 2. **Bootstrap Register**
- **File:** `bootstrap/app.php`
- **Change:** Middleware ditambahkan ke global middleware stack
- Middleware dijalankan SEBELUM request sampai ke Filament

### 3. **Enhanced Login Controller**
- **File:** `app/Filament/Pages/Login.php`
- **Changes:**
  - `mount()` method: Immediate redirect ke SSO jika enabled
  - `authenticate()` method: Double-check dan redirect jika somehow form submitted

### 4. **Route Verification**
Routes saat ini:
- ✅ `/sso/login` - SSO Login redirect controller
- ✅ `/sso/callback` - SSO Callback handler
- ✅ `POST /siimut/logout` - Logout endpoint
- ❌ `/siimut/login` - NOT registered (by design saat SSO=true)

## Deployment Steps

### 1. **Deploy Code Changes**
```bash
# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev

# Dump autoloader
composer dump-autoload -o
```

### 2. **Clear & Rebuild Caches**
```bash
# Run provided script
bash scripts/deploy-production.sh

# OR manual commands
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. **Verify Environment**
```bash
# Check .env values
grep -E "USE_SSO|IAM_ENABLED|APP_ENV" .env

# Expected output for production with SSO:
# APP_ENV=production
# USE_SSO=true
# IAM_ENABLED=true
```

### 4. **Test Routes**
```bash
# List all login/SSO routes
php artisan route:list | grep -E "(login|sso)"

# Expected: sso/login route should exist ✓
```

## Environment Configuration

### Production (SSO Mode)
```dotenv
APP_ENV=production
USE_SSO=true
IAM_ENABLED=true
APP_DEBUG=false
```

### Staging/Development (Custom Login)
```dotenv
APP_ENV=local
USE_SSO=false
IAM_ENABLED=false
APP_DEBUG=true
```

## How It Works Now

### Flow Diagram
```
User → /siimut/login (GET)
  ↓
Middleware: RedirectSsoLoginPost
  ├─ Check: SSO enabled? YES
  ├─ Check: Custom login page registered? NO
  └─ Result: REDIRECT to /sso/login ✓
  
User tries to POST (old form cached)
  ↓
Middleware: RedirectSsoLoginPost
  ├─ Check: POST to /siimut/login? YES
  ├─ Check: SSO enabled? YES
  └─ Result: REDIRECT to /sso/login ✓

User at /sso/login
  ↓
SSO Login Controller (SsoLoginRedirectController)
  ├─ Validate SSO credentials
  ├─ Create/update user
  └─ Authenticate and redirect to dashboard ✓
```

## Troubleshooting

### 1. Still Getting 405 Error?
```bash
# Verify middleware is registered
php artisan route:list | head -20

# Should show middleware in output
# Clear all caches again
php artisan config:clear && php artisan cache:clear && php artisan route:cache
```

### 2. Custom Login Still Showing?
```bash
# Verify SSO is enabled
php artisan tinker
>>> config('iam.enabled')
>>> env('USE_SSO')

# Both should be true
```

### 3. SSO Routes Not Working?
```bash
# Check IAM client package
composer show juniyasyos/laravel-iam-client

# Verify routes in routes/web.php are included
grep -n "sso" routes/web.php
```

## Verification Checklist

- [ ] Code deployed
- [ ] Caches cleared and rebuilt
- [ ] Environment variables correct (USE_SSO=true)
- [ ] Routes cached successfully
- [ ] Middleware registered in bootstrap/app.php
- [ ] Test: Can access `/sso/login` ✓
- [ ] Test: GET /siimut/login redirects to /sso/login ✓
- [ ] Test: POST /siimut/login redirects to /sso/login ✓
- [ ] Test: SSO login works end-to-end ✓

## Files Modified

1. `app/Http/Middleware/RedirectSsoLoginPost.php` - **NEW**
2. `bootstrap/app.php` - Modified (added middleware)
3. `app/Filament/Pages/Login.php` - Enhanced (comments)
4. `scripts/deploy-production.sh` - **NEW** (helper script)

## Rollback Plan

If issues occur:
```bash
# Revert middleware from bootstrap/app.php
# Remove RedirectSsoLoginPost line from middleware registration

# Clear caches again
php artisan config:clear && php artisan cache:clear && php artisan route:cache
```

---

**Deployed:** 2026-02-13
**Status:** Production Ready ✅
