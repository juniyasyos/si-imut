# Browsershot PDF Export - Setup Guide

## 📋 Requirements

Browsershot memerlukan **Node.js** dan **Puppeteer** (headless Chrome) untuk generate PDF.

### 1. Install Node.js & NPM

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y nodejs npm

# Verify installation
node --version
npm --version
```

### 2. Install Puppeteer

```bash
# Install Puppeteer globally (atau di project)
npm install -g puppeteer

# Atau install di project Laravel
cd /home/juni/skripsi-ahmad-ilyas/application/SI-IMUT
npm install puppeteer
```

### 3. Install Chrome/Chromium Dependencies (Linux)

Jika menggunakan Linux server, install dependencies untuk Chrome:

```bash
sudo apt-get install -y \
    gconf-service \
    libasound2 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgcc1 \
    libgconf-2-4 \
    libgdk-pixbuf2.0-0 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    ca-certificates \
    fonts-liberation \
    libappindicator1 \
    libnss3 \
    lsb-release \
    xdg-utils \
    wget
```

## ⚙️ Configuration

### Environment Variables

Tambahkan ke `.env`:

```env
# Browsershot Configuration
BROWSERSHOT_NODE_BINARY=/usr/bin/node
BROWSERSHOT_NPM_BINARY=/usr/bin/npm
BROWSERSHOT_TIMEOUT=120

# Optional: Custom Chrome path (jika perlu)
# BROWSERSHOT_CHROME_PATH=/usr/bin/chromium-browser
```

### Verify Binary Paths

```bash
# Check Node path
which node
# Output: /usr/bin/node

# Check NPM path
which npm
# Output: /usr/bin/npm

# Check Chrome/Chromium path (optional)
which chromium-browser
# atau
which google-chrome
```

Update `.env` sesuai dengan path yang ditemukan.

## 🚀 Usage

### Di SummaryDiagram Page

1. **Print Laporan** (Browser Print Dialog)
   - Klik tombol "Print Laporan" (ikon printer, warna biru)
   - Membuka preview di tab baru
   - User bisa review dan print manual

2. **Export PDF** (Browsershot - Otomatis)
   - Klik tombol "Export PDF" (ikon download, warna hijau)
   - Muncul modal konfirmasi
   - Klik "Export"
   - File PDF akan di-generate dan otomatis download
   - File tersimpan sementara di `storage/app/public/exports/`

### Fitur Export PDF

- ✅ Generate dari HTML dengan ApexCharts
- ✅ Support background colors & graphics
- ✅ Format A4, Portrait
- ✅ Margin 10mm
- ✅ Filename: `Laporan_{slug-indikator}_{timestamp}.pdf`
- ✅ Auto-delete setelah download
- ✅ Timeout 120 detik
- ✅ Wait until network idle (untuk load chart)

## 🐛 Troubleshooting

### Error: "Could not find Chrome"

**Solusi:**

1. Install Chromium:
   ```bash
   sudo apt-get install chromium-browser
   ```

2. Set path di `.env`:
   ```env
   BROWSERSHOT_CHROME_PATH=/usr/bin/chromium-browser
   ```

3. Update SummaryDiagram.php untuk use custom chrome:
   ```php
   Browsershot::html($html)
       ->setChromePath(config('browsershot.chrome_path'))
       // ... other options
   ```

### Error: "Error: ENOENT: no such file or directory, stat 'node_modules/puppeteer'"

**Solusi:**

```bash
cd /home/juni/skripsi-ahmad-ilyas/application/SI-IMUT
npm install puppeteer
```

### Error: Permission denied

**Solusi:**

```bash
# Berikan permission ke storage
chmod -R 775 storage/app/public/
chown -R www-data:www-data storage/app/public/

# Atau sesuaikan dengan user server Anda
```

### Chart tidak muncul di PDF

**Solusi:**

Browsershot sudah di-set dengan:
- `waitUntilNetworkIdle()` - Tunggu semua request selesai
- `showBackground()` - Show background graphics
- `emulateMedia('print')` - Emulate print media

Jika masih tidak muncul, tambahkan delay:

```php
Browsershot::html($html)
    ->waitUntilNetworkIdle()
    ->delay(2000) // Wait 2 seconds
    // ... other options
```

### Timeout Error

**Solusi:**

Increase timeout di `.env`:

```env
BROWSERSHOT_TIMEOUT=300
```

Dan di code:

```php
->timeout(300)
```

## 📦 File Structure

```
app/
  Filament/
    Resources/
      ImutDataResource/
        Pages/
          SummaryDiagram.php        # Main action logic
config/
  browsershot.php                   # Browsershot config
resources/
  views/
    filament/
      prints/
        imut-indicator-report.blade.php  # Template with inline CSS
storage/
  app/
    public/
      exports/                      # Temporary PDF storage
```

## 🔒 Security Notes

1. PDF files di-generate di `storage/app/public/exports/`
2. File otomatis dihapus setelah download (`deleteFileAfterSend(true)`)
3. Pastikan folder `exports/` tidak public accessible (gunakan `storage/app/` bukan `public/`)

## 📊 Performance

- **Small PDF** (1-2 pages): ~3-5 seconds
- **Medium PDF** (5-10 pages with charts): ~8-15 seconds
- **Large PDF** (20+ pages): ~20-30 seconds

Memory usage: ~100-200MB per request (headless Chrome)

## 🎨 Customization

### Margin

```php
->margins($top, $right, $bottom, $left)
// Default: 10, 10, 10, 10 (mm)
```

### Paper Size

```php
->format('A4')        // A4 (default)
->format('Letter')    // US Letter
->paperSize(width, height)  // Custom size in mm
```

### Orientation

```php
->landscape()   // Landscape mode
// Default: Portrait
```

### Quality

```php
->scale(2)  // Higher resolution (2x)
```

## ✅ Testing

Test dengan command:

```bash
php artisan tinker
```

```php
use Spatie\Browsershot\Browsershot;

// Test simple HTML to PDF
Browsershot::html('<h1>Hello World</h1>')
    ->save(storage_path('app/test.pdf'));

// Check if file created
file_exists(storage_path('app/test.pdf')); // Should return true
```

Jika berhasil, berarti setup Browsershot sudah OK! 🎉
