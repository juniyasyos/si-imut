# 📄 Panduan Print Report SI-IMUT

## 🎯 Jenis Laporan

### 1. **Laporan IMUT Per Laporan** (Semua Indikator)
Menampilkan semua indikator mutu dalam satu laporan, dikelompokkan berdasarkan kategori.

**Fitur:**
- Summary total IMUT data, unit kerja, dan pencapaian
- Data dikelompokkan per kategori IMUT
- Tabel lengkap dengan status capaian
- Signature section
- Print ready

**Preview dengan Dummy Data:**
```
http://localhost/print/preview/imut-data-report
```

**Print dengan Data Real:**
```
http://localhost/print/imut-data-report?laporan_id={id}
```

---

### 2. **Laporan Per Indikator Mutu** (Detail Satu Indikator)
Menampilkan detail lengkap untuk satu indikator mutu tertentu.

**Fitur:**
- Header informasi indikator dengan kategori dan target
- Grafik tren pencapaian (ApexCharts) - hanya tampil di preview web
- Tabel perbandingan triwulan
- Data per unit kerja
- Analisis dan rekomendasi
- Tabel analisis per unit kerja (jika ada)
- Definisi operasional
- Print ready

**Preview dengan Dummy Data:**
```
http://localhost/print/preview/imut-indicator-report
```

**Print dengan Data Real:**
```
http://localhost/print/imut-indicator-report?laporan_id={id}&imut_data_id={id}
```

---

## 🎨 Fitur Tampilan

### Laporan Per Indikator
1. **Header Gradient** dengan informasi kategori, standar, dan pencapaian
2. **Chart Interaktif** (hanya tampil di web, otomatis hidden saat print):
   - Line chart perbandingan pencapaian vs standar
   - Marker untuk setiap unit kerja
   - Tooltip interaktif
   - Annotation garis standar
3. **Tabel Perbandingan** seperti contoh gambar:
   - Perbandingan 3 bulan terakhir
   - Kolom standar, provinsi, dan tahun depan
   - Styling dengan warna berbeda per kolom
4. **Analysis Box** dengan:
   - Analisis capaian
   - Tren pencapaian (naik/turun/stabil)
   - Rekomendasi otomatis berdasarkan status
5. **Tabel Detail Per Unit Kerja**
6. **Tabel Analisis** (jika ada data analisis)

---

## 🖨️ Cara Print

### Dari Web Browser:
1. Akses URL preview atau print
2. Klik tombol **"🖨️ Cetak"** di kanan bawah
3. Atau tekan `Ctrl + P` (Windows/Linux) atau `Cmd + P` (Mac)
4. Pilih printer atau "Save as PDF"

### Catatan Print:
- Chart ApexCharts otomatis hidden saat print (karena class `.no-print`)
- Preview controls (tombol Kembali & Cetak) otomatis hidden
- Format A4 dengan margin optimal
- Header tabel berulang di setiap halaman

---

## 📊 Data yang Digunakan

### Laporan Per Indikator menggunakan Query Builder:
```php
// Dari LaporanUnitKerja model
LaporanUnitKerja::getReportByImutDataDetails($laporanId, $imutDataId)
```

Query ini mengambil data dari:
- `ImutDataDetailReportQueryBuilder`
- Join dengan tabel: laporan_unit_kerjas, imut_penilaians, imut_profil, imut_data, imut_kategori, unit_kerja
- Data yang dikembalikan: unit_kerja, numerator, denominator, percentage, standard, profil, analysis, recommendations

---

## 🎯 TODO: Implementasi Data Real

Untuk menggunakan data real, update method di `PrintReportController.php`:

```php
public function printImutIndicatorReport(Request $request)
{
    $laporanId = $request->get('laporan_id');
    $imutDataId = $request->get('imut_data_id');
    
    // Get real data
    $laporan = LaporanImut::with('createdBy')->findOrFail($laporanId);
    $imutData = ImutData::with('kategori')->findOrFail($imutDataId);
    
    // Get unit kerja data using query builder
    $unitKerjaData = LaporanUnitKerja::getReportByImutDataDetails($laporanId, $imutDataId)->get();
    
    // Calculate summary
    $summary = [
        'total_unit_kerja' => $unitKerjaData->count(),
        'total_numerator' => $unitKerjaData->sum('numerator_value'),
        'total_denominator' => $unitKerjaData->sum('denominator_value'),
        'average_percentage' => $unitKerjaData->avg('percentage'),
    ];
    
    return view('filament.prints.imut-indicator-report', 
        compact('laporan', 'imutData', 'unitKerjaData', 'summary'));
}
```

---

## 📁 File Structure

```
public/
├── css/
│   └── print-report.css          # Styling untuk print (termasuk chart styles)
└── js/
    └── print-report.js            # JavaScript untuk print & back button

resources/views/filament/prints/
├── imut-data-report.blade.php     # Laporan semua indikator
└── imut-indicator-report.blade.php # Laporan per indikator

app/Http/Controllers/
└── PrintReportController.php      # Controller untuk kedua jenis laporan

routes/
└── web.php                         # Routes: print.* dan print.preview.*
```

---

## 🔧 Dependencies

- **ApexCharts**: `https://cdn.jsdelivr.net/npm/apexcharts` (sudah included via CDN)
- **Laravel Carbon**: Untuk format tanggal
- **Blade Templates**: Engine template Laravel

---

## 💡 Tips

1. **Chart tidak muncul?** 
   - Pastikan koneksi internet aktif (ApexCharts dari CDN)
   - Cek console browser untuk error

2. **Styling tidak sesuai?**
   - Clear browser cache
   - Pastikan file CSS ter-load: `{{ asset('css/print-report.css') }}`

3. **Print landscape vs portrait?**
   - Default: Portrait (A4)
   - Untuk landscape, tambahkan di CSS: `@page { size: A4 landscape; }`

4. **Menyembunyikan elemen saat print:**
   - Tambahkan class `.no-print` pada elemen
   - Sudah diterapkan pada: chart container, preview controls

---

## 📞 Support

Untuk pertanyaan atau masalah, hubungi tim development SI-IMUT.

**Last Updated:** November 12, 2025
