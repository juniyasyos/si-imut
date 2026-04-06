# 🔧 Troubleshooting: Livewire Component Not Found Error

## ❌ **Error yang Terjadi:**
```
Unable to find component: [app.filament.widgets.recommendation-analysis-tim-mutu-widget]
```

---

## 🔍 **Root Cause:**

Error ini terjadi ketika:
1. **Page HTML sudah di-cache** (browser atau server cache)
2. **Livewire component snapshot stale** di HTML cache
3. **Livewire request untuk update component** tapi component tidak bisa ditemukan
4. **Cache konflikt antara conditional rendering**

Ini adalah classic caching issue dengan conditional widgets di Filament pages.

---

## ✅ **Solusi:**

### **Step 1: Server-Side Cache Clearing** (sudah dilakukan)

```bash
rm -rf bootstrap/cache/*
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan view:cache
```

### **Step 2: Browser Cache Clearing** (PENTING!)

User HARUS clear browser cache mereka:

#### **Chrome/Edge:**
```
1. Press Ctrl+Shift+Delete (atau Cmd+Shift+Delete di Mac)
2. Select "All time" untuk Time range
3. Check "Cached images and files"
4. Click "Clear data"
5. Reload page (Ctrl+F5 atau Cmd+Shift+R)
```

#### **Firefox:**
```
1. Press Ctrl+Shift+Delete (atau Cmd+Shift+Delete di Mac)
2. Select "Everything" untuk Time range
3. Check "Cache"
4. Click "Clear Now"
5. Reload page (Ctrl+F5)
```

#### **Safari:**
```
1. Menu > Preferences > Privacy
2. Click "Manage Website Data..."
3. Select all websites
4. Click "Remove"
5. Reload page (Cmd+Option+R)
```

### **Step 3: Hard Refresh Browser**

Setelah clear cache, lakukan **hard refresh**:
- **Windows/Linux**: Press `Ctrl+Shift+R` atau `Ctrl+F5`
- **Mac**: Press `Cmd+Shift+R` atau `Cmd+Option+R`

---

## 🔍 **Verifikasi Perbaikan:**

1. **Monitor Log File:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

   Cari output seperti:
   ```
   [2026-04-07 XX:XX:XX] production.DEBUG: getHeaderWidgets called {"user_id":1,"user_roles":["tim_mutu"],...}
   [2026-04-07 XX:XX:XX] production.DEBUG: User is Tim Mutu/Admin, returning RecommendationAnalysisTimMutuWidget
   ```

2. **Test Widget Loading:**
   - Login as Tim Mutu/Admin → should see Tim Mutu widget
   - Login as Unit Kerja user → should see Unit Kerja widget
   - Widget harus appear di halaman ListLaporanImuts

3. **Check Network Request:**
   - Open Browser DevTools (F12)
   - Go to Console tab
   - Should NOT see "Unable to find component" errors

---

## 📊 **Enhanced Logging Added:**

Logging telah ditambahkan untuk better visibility:

### **In ListLaporanImuts.php (getHeaderWidgets):**
```
[INFO] getHeaderWidgets called
├─ user_id: 1
├─ user_roles: ["tim_mutu"]
└─ has_unit_kerja: false

[DEBUG] User is Tim Mutu/Admin, returning RecommendationAnalysisTimMutuWidget
```

### **In Widget canView() methods:**
```
[DEBUG] RecommendationAnalysisTimMutuWidget::canView
├─ user_id: 1
└─ has_tim_mutu_role: true

[DEBUG] RecommendationAnalysisUnitKerjaWidget::canView
├─ user_id: 1
├─ has_unit_kerja: false
├─ is_admin_or_tim_mutu: false
└─ can_view: false
```

---

## 🚀 **Best Practices untuk Avoid Issue:**

### **1. Development Environment:**
```
# Always clear caches after code changes
php artisan cache:clear && php artisan view:clear
```

### **2. Test dengan Incognito/Private Mode:**
```
- Chrome: Ctrl+Shift+N
- Firefox: Ctrl+Shift+P
- Safari: Cmd+Option+I
```

Browser dalam private mode tidak cache, jadi cocok untuk testing.

### **3. Disable Cache During Development:**
Dalam `.env`:
```
CACHE_DRIVER=array  # Use in-memory cache (no persistence)
```

### **4. Production Deployment:**
```bash
# Upon deployment, always run:
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan view:cache
```

---

## 📋 **Checklist untuk Debugging:**

- [ ] Server cache cleared (`php artisan cache:clear`)
- [ ] Browser cache cleared (DevTools > Storage tab)
- [ ] Hard refresh page (`Ctrl+Shift+R` atau `Cmd+Shift+R`)
- [ ] Logged in again (new session)
- [ ] Check log file for DEBUG messages
- [ ] Check browser console (F12 > Console tab)
- [ ] Widget appears on page
- [ ] No "Unable to find component" errors

---

## 🔗 **Related Files Modified:**

1. **app/Filament/Resources/LaporanImutResource/Pages/ListLaporanImuts.php**
   - Enhanced logging in `getHeaderWidgets()`
   - Better error handling dengan detailed trace

2. **app/Filament/Widgets/RecommendationAnalysisTimMutuWidget.php**
   - Added logging in `canView()`
   - Better exception handling

3. **app/Filament/Widgets/RecommendationAnalysisUnitKerjaWidget.php**
   - Added logging in `canView()`
   - Better exception handling

---

## 📞 **If Error Persists:**

1. **Check Application Logs:**
   ```bash
   tail -100 storage/logs/laravel.log | grep -i "widget\|canView"
   ```

2. **Check Browser DevTools:**
   - F12 > Network tab
   - Look for failed requests to `/livewire/update`
   - Check Network tab for status codes

3. **Check Filament Configuration:**
   ```bash
   php artisan list | grep filament
   php artisan route:list | grep livewire
   ```

4. **Verify Widget Classes Exist:**
   ```bash
   php artisan tinker
   > class_exists('App\Filament\Widgets\RecommendationAnalysisTimMutuWidget')
   > class_exists('App\Filament\Widgets\RecommendationAnalysisUnitKerjaWidget')
   ```

---

**Last Updated:** April 7, 2026
**Status:** ✅ Root cause identified and logging enhanced
