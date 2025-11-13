# Print Troubleshooting Guide - SI-IMUT

## Masalah: Chart dan Elemen Tidak Muncul Saat Print

### Penyebab
Browser modern secara default tidak mencetak background colors dan graphics untuk menghemat tinta. Hal ini menyebabkan:
- ApexCharts tidak tercetak
- Gradient backgrounds hilang
- Warna tabel tidak muncul
- Badge dan box berwarna jadi putih

### Solusi yang Telah Diterapkan

#### 1. CSS Print Enhancements
File: `/public/css/print-report.css`

**Properti `print-color-adjust: exact`** ditambahkan untuk memaksa browser mencetak warna:
```css
@media print {
    /* Chart */
    .chart-container {
        display: block !important;
        page-break-inside: avoid;
    }
    
    #trendChart {
        display: block !important;
        min-height: 350px;
        max-height: 400px;
        width: 100% !important;
    }
    
    /* Gradient backgrounds */
    .indicator-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Table colors */
    .comparison-table th,
    .comparison-table td {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
```

#### 2. JavaScript Print Handlers
File: `/public/js/print-report.js`

Event handlers untuk memastikan chart ter-render sempurna:
```javascript
window.addEventListener('beforeprint', function() {
    if (window.ApexCharts && window.chartInstance) {
        window.chartInstance.render();
    }
});
```

#### 3. Chart Instance Storage
File: `resources/views/filament/prints/imut-indicator-report.blade.php`

Chart instance disimpan di `window.chartInstance` untuk akses dari print handler.

---

## Cara Mencetak dengan Benar

### Google Chrome (Recommended)
1. Buka preview report
2. Tekan `Ctrl+P` (Windows/Linux) atau `Cmd+P` (Mac)
3. **PENTING:** Di dialog print, klik **"More settings"**
4. Centang opsi **"Background graphics"** atau **"Background colors and images"**
5. Pilih orientasi: **Portrait** atau **Landscape** (sesuai kebutuhan)
6. Atur margin: **Default** atau **Minimum**
7. Klik **Print** atau **Save as PDF**

### Mozilla Firefox
1. Buka preview report
2. Tekan `Ctrl+P` (Windows/Linux) atau `Cmd+P` (Mac)
3. Klik **"More Settings"**
4. Centang **"Print backgrounds (colors & images)"**
5. Klik **Print**

### Microsoft Edge
1. Buka preview report
2. Tekan `Ctrl+P` (Windows/Linux) atau `Cmd+P` (Mac)
3. Aktifkan **"Background graphics"** di sidebar
4. Klik **Print**

### Safari (Mac)
1. Buka preview report
2. Tekan `Cmd+P`
3. Klik **"Show Details"**
4. Centang **"Print backgrounds"**
5. Klik **Print**

---

## Checklist Sebelum Print

✅ **Background graphics diaktifkan** di browser settings  
✅ **Orientasi halaman sesuai** (Portrait untuk laporan standar)  
✅ **Margin minimal** untuk memaksimalkan ruang  
✅ **Page size: A4**  
✅ **Scale: 100%** (jangan di-zoom)  

---

## Tips Optimasi Print

### 1. Save as PDF Dulu
Untuk hasil terbaik:
1. Print to PDF dulu (Chrome → Save as PDF)
2. Cek hasil PDF, pastikan semua elemen tampil
3. Baru print PDF tersebut ke printer fisik

### 2. Jika Chart Masih Tidak Muncul
Coba:
- Tunggu beberapa detik setelah halaman load sempurna
- Scroll ke bawah sampai chart terlihat di viewport
- Baru tekan Ctrl+P

### 3. Untuk Multiple Pages
Jika laporan lebih dari 1 halaman:
- Pastikan page-break sudah optimal
- Cek di preview apakah tabel terpotong di tengah
- Gunakan Landscape jika tabel terlalu lebar

---

## Browser Settings (One-time Setup)

### Chrome/Edge - Always Print Backgrounds
1. Buka `chrome://settings/printing`
2. Atau: Settings → Advanced → Printing
3. Di "Print preview", always enable "Background graphics"

### Firefox - Always Print Backgrounds
1. Buka `about:config`
2. Cari `print.print_bgcolor`
3. Set ke `true`
4. Cari `print.print_bgimages`
5. Set ke `true`

---

## Troubleshooting Spesifik

### Chart Tidak Muncul
- **Penyebab**: ApexCharts belum fully rendered
- **Solusi**: Tunggu 2-3 detik setelah page load, pastikan chart terlihat di layar, baru print

### Gradient Hilang
- **Penyebab**: Background graphics disabled
- **Solusi**: Enable "Background graphics" di print dialog

### Warna Tabel Pudar
- **Penyebab**: Browser color optimization
- **Solusi**: Pastikan `print-color-adjust: exact` aktif (sudah ada di CSS)

### Element Terpotong
- **Penyebab**: Page break di tengah element
- **Solusi**: Gunakan `page-break-inside: avoid` (sudah diterapkan)

### Font Terlalu Kecil/Besar
- **Penyebab**: Browser scaling
- **Solusi**: Set scale ke 100% di print dialog

---

## Catatan Teknis

### CSS Properties yang Digunakan
- `print-color-adjust: exact` - Force print colors
- `-webkit-print-color-adjust: exact` - Safari/Chrome
- `page-break-inside: avoid` - Prevent element splitting
- `page-break-after: avoid` - Keep sections together
- `display: block !important` - Ensure visibility

### ApexCharts Print Support
ApexCharts menggunakan SVG untuk rendering. SVG printer-friendly dan akan tercetak dengan baik jika:
1. Container memiliki explicit width/height
2. Background graphics enabled
3. Chart sudah fully rendered sebelum print

### Browser Compatibility
✅ Chrome 90+ (Recommended)  
✅ Firefox 88+  
✅ Edge 90+  
✅ Safari 14+  
⚠️ Internet Explorer: Not supported  

---

## Update Log

### 2025-11-12
- ✅ Added `print-color-adjust: exact` to all colored elements
- ✅ Added beforeprint event handler for chart re-rendering
- ✅ Saved chart instance to window object
- ✅ Enhanced print CSS for chart container
- ✅ Added page-break controls for better pagination

---

## Kontak Support

Jika masih mengalami masalah print:
1. Screenshot error/hasil print yang salah
2. Sebutkan browser dan versi yang digunakan
3. Sebutkan apakah "Background graphics" sudah diaktifkan
4. Hubungi tim IT SI-IMUT
