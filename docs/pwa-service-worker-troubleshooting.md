# PWA Service Worker Troubleshooting Guide

## Masalah Umum

### 1. Service Worker Tidak Mengcache File
**Gejala**: File statis terus di-load dari server meski sudah ada service worker.

**Penyebab**:
- Cache name tidak diupdate setelah perubahan
- Excluded paths terlalu luas
- Query parameters tidak di-handle dengan baik
- Cache strategy tidak optimal

**Solusi**:
- Update cache name di `serviceworker.js` (misal: `v3` -> `v4`)
- Perbaiki excluded paths untuk tidak terlalu luas
- Gunakan `ignoreSearch: true` saat matching cache
- Implementasi cache strategy yang tepat untuk berbagai jenis file

### 2. Cache Lama Tidak Terhapus
**Gejala**: Perubahan tidak terlihat meski sudah update service worker.

**Solusi**:
1. Hard refresh browser (Ctrl+Shift+R)
2. Clear browser cache di DevTools
3. Unregister service worker di DevTools > Application > Service Workers
4. Update cache name di service worker

### 3. File dengan Query Parameters Tidak Tercache
**Gejala**: File seperti `/js/app.js?v=1.0.0` tidak di-cache.

**Solusi**: Gunakan `ignoreSearch: true` dan cache versi clean tanpa query params.

## Best Practices

### 1. Cache Strategy
```javascript
// Pre-cache files penting saat install
self.addEventListener("install", function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(FILES_TO_CACHE);
        })
    );
    self.skipWaiting();
});

// Cache first untuk static assets
// Network first untuk dynamic content
```

### 2. Excluded Paths
Jangan exclude path yang terlalu luas:
```javascript
// ❌ Buruk - exclude semua dari root
const excludedPaths = ["/"];

// ✅ Baik - exclude hanya yang perlu
const excludedPaths = [
    "/livewire",
    "/api/", 
    "/siimut/login",
    "/broadcasting/"
];
```

### 3. Cache Versioning
Selalu update cache name saat ada perubahan:
```javascript
const CACHE_NAME = "siimut-cache-v3"; // increment version
const RUNTIME_CACHE = "siimut-runtime-v3";
```

## Testing Service Worker

### 1. Browser DevTools
1. Buka DevTools (F12)
2. Go to Application tab
3. Check Service Workers section
4. Monitor Cache Storage

### 2. Console Logs
Service worker sudah dilengkapi logging:
- `PWA Service Worker installing...`
- `PWA Serving from cache: [file]`
- `PWA Fetching and caching: [file]`

### 3. Network Tab
Monitor apakah file benar-benar dari cache atau network.

## Maintenance

### Regenerate Cache Files
```bash
./generate-cache-pwa.sh
```

### Clear Laravel Cache
```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

### Force Service Worker Update
1. Update cache version
2. Hard refresh browser
3. Clear browser cache jika perlu

## Monitoring

File yang should be cached:
- `/build/assets/*` - Vite assets
- `/css/*` - CSS files
- `/js/*` - JavaScript files
- `/images/*` - Images
- Static PWA files (manifest, icons)

File yang should NOT be cached:
- `/livewire/*` - Dynamic Livewire requests
- `/api/*` - API calls
- Authentication pages
- AJAX/JSON requests