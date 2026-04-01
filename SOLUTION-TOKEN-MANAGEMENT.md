# IAM App Switcher - Token Management & Fallback Solution

## 🔍 Masalah yang Diidentifikasi

Dari logs:
```
"config_apps":null
"Token tidak ada, fallback ke session"
"final_name":"Siimut" (hanya generated name)
```

**Root Causes:**
1. ❌ **Token hilang** - Session `iam.access_token` tidak tersimpan dengan baik
2. ❌ **Config fallback missing** - `config('iam.apps')` belum di-setup
3. ❌ **Default fallback** - Jadi nama jadi "Siimut" (dari ucfirst app_key)

---

## ✅ Solutions Implemented

### **1. Config Fallback (config/iam.php)**

**Ditambahkan section baru:**
```php
'apps' => [
    'siimut' => [
        'name' => 'SIIMUT - Sistem Informasi Manajemen Indikator Mutu Terpadu',
        'description' => 'Aplikasi manajemen indikator...',
        'url' => 'http://127.0.0.1:8088',
        'logo_url' => null,
    ],
]
```

**Keuntungan:**
- ✅ Nama lengkap tersedia bahkan ketika token hilang
- ✅ Support multiple applications
- ✅ Bisa dikonfigurasi via environment variables
- ✅ Fallback ketika API error

---

### **2. Token Management Service (IamTokenManager)**

**File:** `app/Services/IamTokenManager.php`

**Features:**
```php
// Get token (dengan auto-refresh jika perlu)
$token = $tokenManager->getValidToken();

// Check token valid
if ($tokenManager->hasValidToken()) { ... }

// Refresh token
$newToken = $tokenManager->refreshToken($refreshToken);

// Clear tokens (logout)
$tokenManager->clearTokens();

// Debug info
$info = $tokenManager->getDebugInfo();
```

**Keuntungan:**
- ✅ Auto-refresh token yang expired
- ✅ Fallback ke refresh_token jika access_token hilang
- ✅ Log detail untuk debugging
- ✅ Session persistence management

---

### **3. Updated IamAppSwitcher Component**

**File:** `app/Livewire/IamAppSwitcher.php`

**Flow:**
```
loadApplications()
  ↓
  1️⃣ Check user authenticated
  ↓
  2️⃣ Get valid token (auto-refresh jika perlu)
    ├─ Token ada? → Call API
    │   ├─ Success? → Use API data ✅
    │   └─ Error? → Fallback ke config
    └─ Token tidak ada? → Fallback ke config
  ├─ Config ada? → Use config data ✅
  └─ Default? → Generate dari app_key
```

**Code Flow:**
```php
// Priority:
1. API endpoint (/api/users/applications)  → Full data dari IAM server
2. Config fallback (config/iam.apps)        → Nama lengkap + desc
3. Generated (ucfirst app_key)              → Last resort "Siimut"
```

---

## 🚀 How It Works

### **Scenario 1: Normal (Token Available)**
```
Session: "iam.access_token" = "eyJ0eXAi..."
         ↓
IamTokenManager::getValidToken() → Returns token
         ↓
UserApplicationsService::getApplications() → API call
         ↓
Success → Show full data from IAM API ✅
```

**Result:** Nama "SIIMUT - Sistem Informasi..." dari database IAM

### **Scenario 2: Token Expired (Refresh Available)**
```
Session: "iam.access_token" = expired
         "iam.refresh_token" = valid
         ↓
IamTokenManager::getValidToken()
  → Detect expired
  → Call /api/auth/refresh
  → Get new access_token
  → Store in session
  → Return new token ✅
         ↓
API call with new token
         ↓
Result: Success ✅
```

**Result:** Nama dari IAM API (dengan token yang di-refresh)

### **Scenario 3: Token Hilang (Config Fallback)**
```
Session: "iam.access_token" = empty
         "iam.refresh_token" = empty
         ↓
IamTokenManager::getValidToken() → null
         ↓
getApplicationsFromFallback():
  → Check config('iam.apps.siimut')
  → Found ✅
  → Use config name ✅
         ↓
Result: "SIIMUT - Sistem Informasi..." ✅
```

**Result:** Nama dari config (tidak perlu token)

### **Scenario 4: Worst Case (No Config)**
```
Config('iam.apps.siimut') = null
         ↓
Fall back to: ucfirst(str_replace('-', ' ', 'siimut'))
         ↓
Result: "Siimut" ❌
```

**Catatan:** Sekarang sudah ada config, jadi scenario ini tidak terjadi

---

## 📋 Configuration

### **.env** (Optional)
```env
# Override config values dari environment
SIIMUT_NAME="SIIMUT - Sistem Informasi Manajemen Indikator Mutu Terpadu"
SIIMUT_DESC="Aplikasi manajemen indikator..."
SIIMUT_URL="http://127.0.0.1:8088"
SIIMUT_LOGO_URL="https://..."
```

### **config/iam.php** (Already configured)
```php
'apps' => [
    'siimut' => [
        'name' => env('SIIMUT_NAME', 'SIIMUT - ...'),
        'description' => env('SIIMUT_DESC', '...'),
        'url' => env('SIIMUT_URL', env('APP_URL')),
        'logo_url' => env('SIIMUT_LOGO_URL', null),
    ],
]
```

---

## 🔧 Token Refresh Flow

### **How Refresh Works**

```
1. IamAppSwitcher calls loadApplications()
   ↓
2. IamTokenManager::getValidToken() check session
   ↓
3a. Token ada & valid?
    → Return token ✅
   ↓
3b. Token tidak ada?
    → Check refresh_token
    → POST /api/auth/refresh
    → Get new access_token
    → Store in session
    → Return new token ✅
   ↓
3c. Refresh gagal?
    → Return null
    → Use fallback config ✅
```

### **Prerequisites untuk Refresh**
- ✅ Session must have `iam.refresh_token`
- ✅ IAM server must provide `/api/auth/refresh` endpoint
- ✅ Refresh token must be valid

---

## 📊 Logging & Debugging

### **Log Messages**

```
[IamAppSwitcher] Token available, calling API endpoint
  → Token found di session, calling API

[IamAppSwitcher] API response received
  → API call successful, got applications data

[IamAppSwitcher] Token tidak tersedia, menggunakan fallback
  → Token hilang, using config fallback

[IamAppSwitcher] Using fallback config
  → SUCCESS: got name dari config

[IamToken] Session token available
  → Token found & valid

[IamToken] Token tidak ada di session, mencoba refresh...
  → Attempting to refresh token

[IamToken] Token refreshed successfully
  → Refresh successful, new token stored

[IamToken] Refresh failed
  → Refresh gagal, fallback to config
```

### **Check Logs**
```bash
tail -50 storage/logs/laravel.log | grep -i "IamAppSwitcher\|IamToken"
```

---

## ✅ Checklist

- [x] Config fallback setup di `config/iam.php`
- [x] Token manager service created (`IamTokenManager.php`)
- [x] IamAppSwitcher updated dengan fallback logic
- [x] Auto-refresh token implemented
- [x] Detailed logging added
- [x] PHP syntax validated
- [x] Graceful fallback untuk semua scenarios

---

## 🧪 Testing

### **Test 1: Normal Case (Token Available)**
```
1. Login via IAM
2. Open IamAppSwitcher
3. Check logs: "Token available, calling API endpoint"
4. Result: Show nama dari API ✅
```

### **Test 2: Token Expired (Auto-Refresh)**
```
1. Wait for token to expire
2. Open IamAppSwitcher
3. Check logs: "Token refreshed successfully"
4. Result: Show data dengan token baru ✅
```

### **Test 3: Token Hilang (Config Fallback)**
```
1. Manually clear 'iam.access_token' from session
2. Open IamAppSwitcher
3. Check logs: "Using fallback config"
4. Result: Show nama dari config ✅
```

### **Test 4: Multiple Refreshes**
```
1. Keep app open, wait > token TTL
2. Open IamAppSwitcher multiple times
3. Check logs: multiple "Token refreshed successfully"
4. Result: Works consistently ✅
```

---

## 🔒 Security Notes

- ✅ Refresh token stored in session (bukan public)
- ✅ Token refresh hanya ke authenticated endpoint
- ✅ Authorization header included di refresh request
- ✅ Error logging tanpa expose sensitive data
- ✅ Graceful fallback jika token invalid

---

## 📞 Support & Debugging

Jika ada issue:

1. **Check logs:**
   ```bash
   tail -100 storage/logs/laravel.log | grep -i "iam"
   ```

2. **Check session data:**
   ```bash
   php artisan tinker
   >>> session('iam')
   ```

3. **Test token manager:**
   ```php
   $tokenManager = app(App\Services\IamTokenManager::class);
   dd($tokenManager->getDebugInfo());
   ```

4. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

---

## 📝 Files Modified

- ✅ `config/iam.php` - Added 'apps' section
- ✅ `app/Livewire/IamAppSwitcher.php` - Complete refactor with fallback logic
- ✅ `app/Services/IamTokenManager.php` - New service for token management
- ✅ `resources/views/livewire/iam-app-switcher.blade.php` - Already updated (tetap sama)

---

## 🎯 Result

**Before:**
```
Token tidak ada → Fallback ke default → "Siimut" ❌
```

**After:**
```
Token ada           → API data        → "SIIMUT - Sistem..." ✅
Token expired       → Auto-refresh    → "SIIMUT - Sistem..." ✅
Token hilang        → Config fallback → "SIIMUT - Sistem..." ✅
Semua scenarios     → Always leng     → Nama lengkap selalu ada ✅
```
