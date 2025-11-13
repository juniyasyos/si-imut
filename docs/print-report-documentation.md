# Print Report IMUT - Dokumentasi

Dokumentasi untuk fitur print report sistem IMUT.

## 📋 Daftar Laporan

### 1. **Laporan Semua Indikator (Summary Report)**
Laporan yang menampilkan ringkasan semua indikator mutu dalam satu periode laporan, dikelompokkan berdasarkan kategori.

**URL:**
- Preview (Dummy Data): `/print/preview/imut-data-report`
- Print Real Data: `/print/imut-data-report?laporan_id={id}`

**Fitur:**
- Summary statistik keseluruhan
- Data dikelompokkan per kategori IMUT
- Menampilkan N, D, Persentase, dan Status Capaian
- Status badge untuk setiap indikator
- Total 25 indikator sample

### 2. **Laporan Per Indikator (Detail Report)**
Laporan detail untuk satu indikator mutu tertentu, menampilkan data per unit kerja.

**URL:**
- Preview (Dummy Data): `/print/preview/imut-indicator-report`
- Print Real Data: `/print/imut-indicator-report?laporan_id={id}&imut_data_id={id}`

**Fitur:**
- Info lengkap indikator (kategori, standar, definisi)
- Visual badge pencapaian
- Data per unit kerja
- Analisis dan rekomendasi per unit kerja
- Summary total dan rata-rata

## 🎨 Assets

### CSS
**File:** `/public/css/print-report.css`
- Styling umum untuk laporan
- Responsive print layout
- Badge dan status styling
- Print-specific media queries

### JavaScript
**File:** `/public/js/print-report.js`
- Print button handler
- Back button handler
- Keyboard shortcuts (Ctrl+P, ESC)

## 🚀 Cara Mengakses

### Preview dengan Dummy Data

1. **Laporan Semua Indikator:**
```bash
# Browser
http://localhost:8000/print/preview/imut-data-report

# atau dengan serve
php artisan serve
# kemudian buka: http://127.0.0.1:8000/print/preview/imut-data-report
```

2. **Laporan Per Indikator:**
```bash
# Browser
http://localhost:8000/print/preview/imut-indicator-report

# atau dengan serve
php artisan serve
# kemudian buka: http://127.0.0.1:8000/print/preview/imut-indicator-report
```

### Print dengan Data Real (TODO)

```php
// Contoh penggunaan di controller
Route::get('/laporan/{id}/print-summary', function($id) {
    return redirect()->route('print.imut-data-report', [
        'laporan_id' => $id
    ]);
});

Route::get('/laporan/{id}/print-indicator/{imut_data_id}', function($id, $imutDataId) {
    return redirect()->route('print.imut-indicator-report', [
        'laporan_id' => $id,
        'imut_data_id' => $imutDataId
    ]);
});
```

## 📊 Data Structure

### Laporan Semua Indikator
```php
$laporan = (object) [
    'id' => 1,
    'name' => 'Laporan IMUT Bulan Oktober 2024',
    'status' => 'complete', // complete, process, coming_soon
    'assessment_period_start' => '2024-10-01',
    'assessment_period_end' => '2024-10-31',
    'createdBy' => (object) ['name' => 'Dr. Ahmad']
];

$summary = [
    'total_imut_data' => 25,
    'total_unit_kerja' => 8,
    'average_percentage' => 87.45,
    'filled_count' => 192,
    'total_count' => 200,
];

$dataByCategory = collect([
    'Kategori A' => collect([
        (object) [
            'imut_data_title' => 'Nama Indikator',
            'total_numerator' => 485,
            'total_denominator' => 500,
            'percentage' => 97.00,
            'imut_standard' => 100,
        ]
    ])
]);
```

### Laporan Per Indikator
```php
$imutData = (object) [
    'title' => 'Identifikasi Pasien dengan Benar',
    'kategori' => 'Sasaran Keselamatan Pasien',
    'standard' => 100,
    'definition' => 'Definisi operasional...',
    'numerator_description' => 'Deskripsi N',
    'denominator_description' => 'Deskripsi D',
];

$unitKerjaData = collect([
    (object) [
        'unit_kerja' => 'IGD',
        'numerator_value' => 95,
        'denominator_value' => 100,
        'percentage' => 95.00,
        'imut_standard' => 100,
        'imut_profil' => 'Versi 1.2',
        'analysis' => 'Analisis...',
        'recommendations' => 'Rekomendasi...',
    ]
]);

$summary = [
    'total_unit_kerja' => 8,
    'total_numerator' => 1060,
    'total_denominator' => 1100,
    'average_percentage' => 96.36,
];
```

## 🎯 Controller Implementation

Untuk implementasi dengan data real, tambahkan method di controller:

```php
// app/Http/Controllers/PrintReportController.php

use App\Models\LaporanImut;
use App\Models\ImutData;
use App\Models\LaporanUnitKerja;

public function printImutDataReport(Request $request)
{
    $laporanId = $request->get('laporan_id');
    
    $laporan = LaporanImut::with('createdBy')->findOrFail($laporanId);
    
    // Ambil data grouped by category
    $dataByCategory = LaporanUnitKerja::getReportByCategory($laporanId);
    
    // Hitung summary
    $summary = [
        'total_imut_data' => $dataByCategory->flatten()->unique('id')->count(),
        'total_unit_kerja' => $laporan->unitKerjas()->count(),
        // ... dst
    ];
    
    return view('filament.prints.imut-data-report', 
        compact('laporan', 'summary', 'dataByCategory'));
}

public function printImutIndicatorReport(Request $request)
{
    $laporanId = $request->get('laporan_id');
    $imutDataId = $request->get('imut_data_id');
    
    $laporan = LaporanImut::with('createdBy')->findOrFail($laporanId);
    $imutData = ImutData::with('category')->findOrFail($imutDataId);
    
    // Ambil data per unit kerja untuk indikator ini
    $unitKerjaData = LaporanUnitKerja::getReportByImutDataDetails($laporanId, $imutDataId);
    
    // Hitung summary
    $summary = [
        'total_unit_kerja' => $unitKerjaData->count(),
        'total_numerator' => $unitKerjaData->sum('numerator_value'),
        // ... dst
    ];
    
    return view('filament.prints.imut-indicator-report',
        compact('laporan', 'imutData', 'unitKerjaData', 'summary'));
}
```

## 🖨️ Print Features

### Tombol Kontrol (tidak muncul saat print)
- **Cetak**: Membuka dialog print browser
- **Kembali**: Kembali ke halaman sebelumnya

### Keyboard Shortcuts
- `Ctrl/Cmd + P`: Print
- `ESC`: Kembali

### Print Settings
- **Ukuran**: A4
- **Orientasi**: Portrait
- **Margin**: 15mm
- **Background Graphics**: Enabled (untuk warna badge)

## 📁 File Structure

```
application/SI-IMUT/
├── app/
│   └── Http/
│       └── Controllers/
│           └── PrintReportController.php
├── public/
│   ├── css/
│   │   └── print-report.css
│   └── js/
│       └── print-report.js
├── resources/
│   └── views/
│       └── filament/
│           └── prints/
│               ├── imut-data-report.blade.php       # Laporan semua indikator
│               └── imut-indicator-report.blade.php  # Laporan per indikator
└── routes/
    └── web.php
```

## 🔧 Customization

### Mengubah Warna
Edit `/public/css/print-report.css`:
```css
/* Primary color */
.header { border-bottom: 3px solid #2563eb; }

/* Status badges */
.status-complete { background: #d1fae5; color: #065f46; }
```

### Menambah Field
Edit view blade yang sesuai dan tambahkan kolom:
```blade
<th>Field Baru</th>
<!-- ... -->
<td>{{ $item->field_baru }}</td>
```

## 📝 Notes

- Preview menggunakan dummy data untuk testing UI
- Real data implementation memerlukan query ke database
- Print layout optimized untuk A4 paper
- Compatible dengan Chrome, Firefox, Edge
- Gunakan "Background Graphics" untuk print dengan warna

## 🐛 Troubleshooting

### CSS tidak muncul
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear

# Pastikan asset di-publish
php artisan storage:link
```

### Print tidak sesuai
- Aktifkan "Background Graphics" di print dialog
- Set margin ke "Default" atau "Minimal"
- Pastikan paper size A4

### Route tidak ditemukan
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verifikasi route
php artisan route:list --name=print
```

## 📞 Contact

Untuk pertanyaan atau issue, silakan hubungi tim developer.

---
**Last Updated:** November 12, 2025
**Version:** 1.0.0
