# PWA Production Testing Setup

## Quick Start

Untuk test PWA dalam environment production-like (tanpa Vite dev server):

### 1. Create Production Environment File
```bash
cp .env .env.production.local
```

### 2. Update .env.production.local
```env
APP_ENV=production
APP_DEBUG=false

# Keep other settings same as .env
APP_NAME=SIIMUT
APP_URL=http://127.0.0.1:8000
# ... semua config lainnya
```

### 3. Build Assets (jika belum)
```bash
npm run build
```

### 4. Generate Service Worker Cache List
```bash
bash generate-cache-pwa.sh
```

### 5. Clear Config Cache
```bash
php artisan config:clear
php artisan config:cache
php artisan optimize
```

### 6. Run Server with Production Config
```bash
composer run dev-lara
```

## Verify Setup

### Check Build Files
```bash
ls -lah public/build/
# Should show: manifest.json, assets/ folder dengan CSS/JS files
```

### Check Service Worker Cache
```bash
wc -l public/serviceworker-files.js
# Should show 50+ cached files
```

### Check logs
```bash
tail -f storage/logs/laravel.log
# Look for "Vite manifest" warnings
```

### Browser DevTools (F12)

#### 1. Check Console
```javascript
// Paste this in console to verify Vite is working
console._vite_perf.timings
// Should show asset load times from /build/ not /resources/
```

#### 2. Check Service Worker
- Open DevTools → Application → Service Workers
- Should show: `serviceworker registered`
- Click "Unregister" if testing multiple times

#### 3. Check Cache Storage
- Application → Cache Storage
- Should see: `siimut-cache-v3` dengan 50+ entries
- All entries should be from `/build/assets/`, `/images/`, `/css/`, `/js/`

#### 4. Check Network Tab
- Look for: `GET /build/assets/theme-*.css` (200 OK)
- NOT `/resources/css/filament/admin/theme.css`
- Asset URLs should be from manifest resolution

## Expected Success Indicators

✅ Console shows no "Failed to fetch" errors
✅ Service Worker: "serviceworker registered with scope: http://127.0.0.1:8000/siimut/"
✅ Network tab: CSS/JS from `/build/assets/`
✅ Cache Storage: 50+ entries with /build/ paths
✅ No ERR_CONNECTION_REFUSED errors
✅ Offline page loads correctly

## Troubleshooting

### Error: "Vite manifest not found"
```bash
# Ensure build exists
npm run build
bash generate-cache-pwa.sh
php artisan config:clear
```

### Still getting port 5173 errors
```bash
# Check environment
php artisan tinker
>>> env('APP_ENV')
// Should return 'production' NOT 'local'

# If local, update .env.production.local and run:
php artisan serve --env=production.local
```

### Service Worker still caching old files
```bash
# Clear browser cache and SW
# DevTools → Application → Service Workers → Unregister
# Application → Clear site data → Clear all
# Refresh page
```

### Assets not loading
```bash
# Check if manifest is valid JSON
cat public/build/manifest.json | json_pp

# Check if all entry files exist
ls -la public/build/assets/ | grep -E "(app|theme|vendor)"
```

## Comparison: Local vs Production Mode

### Local Development (APP_ENV=local)
```
✅ Run: composer run dev
✅ Vite dev server running on :5173
✅ Hot reload enabled
✅ Source maps available
❌ No offline functionality during dev
❌ Slower for production-like testing
```

### Production Simulation (APP_ENV=production)
```
✅ No Vite server needed
✅ Assets from /build/ (pre-built)
✅ Tests production environment behavior
✅ Full PWA/offline functionality
✅ Realistic performance testing
❌ No hot reload (need `npm run build` for changes)
```

## CI/CD Integration

For automated PWA caching in deployment:

```bash
#!/bin/bash
# deploy.sh

# 1. Build assets
npm run build

# 2. Generate service worker cache
bash generate-cache-pwa.sh

# 3. Optimize Laravel
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 4. Verify manifest exists
[ -f public/build/manifest.json ] || exit 1
```

## Notes

- Setiap kali ada perubahan assets, jalankan: `npm run build && bash generate-cache-pwa.sh`
- APP_ENV=production tidak menjalankan code untuk local development
- Untuk normal development, gunakan APP_ENV=local dengan `composer run dev`
- PWA offline functionality memerlukan assets di cache, jangan skip `generate-cache-pwa.sh`

