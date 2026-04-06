# Fitur: Tampilkan Laporan Sebelumnya Jika Tidak Ada Laporan Aktif

## Ringkasan
Ketika tidak ada laporan yang sedang dalam fase pengisian analisis dan rekomendasi, widget akan menampilkan laporan sebelumnya (laporan terbaru yang sudah selesai) daripada menampilkan empty state kosong.

## Perubahan yang Dilakukan

### 1. Widget: RecommendationAnalysisTimMutuWidget
**File:** `app/Filament/Widgets/RecommendationAnalysisTimMutuWidget.php`

#### Method Baru: `getPreviousAnalysisReport()`
```php
public function getPreviousAnalysisReport(): ?array
```

**Fungsi:**
- Mencari laporan terbaru yang sudah selesai/tidak sedang dalam fase analisis
- Mengembalikan array dengan struktur:
  - `id`: ID laporan
  - `name`: Nama laporan
  - `slug`: Slug laporan
  - `period_end`: Tanggal akhir periode penilaian
  - `laporan`: Object LaporanImut
  - `completion_stats`: Statistik penyelesaian per unit kerja
  - `is_previous`: Flag untuk menandai ini adalah laporan sebelumnya

**Query:**
- Laporan dengan `period_end < today`
- Status bukan `process` ATAU sudah lewat deadline analisis
- Diurutkan berdasarkan `period_end` DESC (terbaru dulu)
- Mengambil 1 laporan terakhir

### 2. Widget: RecommendationAnalysisUnitKerjaWidget
**File:** `app/Filament/Widgets/RecommendationAnalysisUnitKerjaWidget.php`

#### Method Baru: `getPreviousRelevantAnalysisReport()`
```php
public function getPreviousRelevantAnalysisReport(): ?array
```

**Fungsi:**
- Sama seperti `getPreviousAnalysisReport()` tetapi hanya untuk unit kerja user yang login
- Mengembalikan laporan terbaru yang relevan dengan unit kerja user
- Struktur return sama dengan TimMutu widget

**Query:**
- Filter berdasarkan `userUnitKerjaIds`
- Laporan dengan `period_end < today`
- Status bukan `process` ATAU sudah lewat deadline analisis
- Hanya laporan yang terkait dengan unit kerja user
- Diurutkan berdasarkan `period_end` DESC

### 3. View: Tim Mutu Widget
**File:** `resources/views/filament/widgets/recommendation-analysis-tim-mutu-widget.blade.php`

#### Perubahan:
```blade
@php
// Tambah ini di awal
$previousReport = $this->getPreviousAnalysisReport();
$showPrevious = !$hasReports && $previousReport;
@endphp
```

#### Kondisi Render:
1. **Jika ada laporan aktif** (`$hasReports`):
   - Tampilkan laporan yang sedang berjalan dengan badge URGENT/PERHATIAN/BERLANGSUNG
   - Tampilkan progress bar dan detail per unit kerja

2. **Jika tidak ada laporan aktif tapi ada laporan sebelumnya** (`$showPrevious`):
   - Tampilkan laporan sebelumnya dengan styling gray/arsip
   - Badge "Laporan Sebelumnya" dengan icon archive-box
   - Tampilkan completion stats dengan styling yang berbeda
   - Tombol "Lihat Detail" dengan styling gray

3. **Jika tidak ada keduanya**:
   - Tampilkan empty state "Tidak Ada Laporan Aktif"

### 4. View: Unit Kerja Widget
**File:** `resources/views/filament/widgets/recommendation-analysis-unit-kerja-widget.blade.php`

#### Perubahan:
Sama seperti Tim Mutu widget tetapi:
- Menut "Laporan terakhir yang telah diselesaikan" untuk previous report
- Menampilkan statistik hanya untuk unit kerja user
- Simplified layout yang sesuai dengan audience unit kerja

#### Kondisi Render:
1. **Jika ada laporan aktif**: Tampilkan dengan status badge
2. **Jika tidak ada tapi ada laporan sebelumnya**: Tampilkan laporan sebelumnya
3. **Jika tidak ada keduanya**: Tampilkan empty state

## Database Query Details

### Query untuk Laporan Sebelumnya (TimMutu)
```sql
SELECT * FROM laporan_imuts
WHERE DATE(assessment_period_end) < CURDATE()
AND (
    status != 'process'
    OR DATE_ADD(assessment_period_end, INTERVAL recommendation_analysis_duration DAY) < CURDATE()
)
ORDER BY assessment_period_end DESC
LIMIT 1;
```

### Query untuk Laporan Sebelumnya (UnitKerja)
```sql
SELECT DISTINCT l.* FROM laporan_imuts l
INNER JOIN laporan_unit_kerjas luk ON l.id = luk.laporan_imut_id
WHERE DATE(l.assessment_period_end) < CURDATE()
AND l.status != 'process'
OR (
    l.status = 'process'
    AND DATE_ADD(l.assessment_period_end, INTERVAL l.recommendation_analysis_duration DAY) < CURDATE()
)
AND luk.unit_kerja_id IN (user_unit_kerja_ids)
ORDER BY l.assessment_period_end DESC
LIMIT 1;
```

## Styling untuk Laporan Sebelumnya

### Container
- Border: `border-gray-200 dark:border-gray-700`
- Background: `bg-gray-50 dark:bg-gray-900/50`
- Badge: Gray color dengan icon archive-box

### Text
- Ukuran header sama dengan laporan aktif
- Warna teks lebih muted (gray)
- Status badge dengan label "Laporan Sebelumnya"

### Button
- Color: Gray (bukan blue)
- Hover: `hover:bg-gray-500 dark:hover:bg-gray-600`

## Kasus Penggunaan

### Skenario 1: Ada Laporan Aktif
```
┌─────────────────────────────────┐
│ Monitoring Pengisian Analisis   │
│ Overview status pengisian semua │ [ 2 Laporan Aktif ]
│ unit kerja                       │
└─────────────────────────────────┘
┌─────────────────────────────────┐
│ [URGENT] 1 Hari Tersisa         │
│ Laporan Q1 2026                 │
│ Deadline: 7 Mar 2026            │
│ Progress: 6/10 unit kerja (60%) │
│ [Lihat Detail]                  │
└─────────────────────────────────┘
```

### Skenario 2: Tidak Ada Laporan Aktif, Ada Laporan Sebelumnya
```
┌─────────────────────────────────┐
│ Monitoring Pengisian Analisis   │
│ Laporan terakhir yang telah     │
│ diselesaikan                    │
└─────────────────────────────────┘
┌─────────────────────────────────┐
│ 📦 Laporan Sebelumnya           │
│ Laporan Q4 2025                 │
│ Periode: 31 Dec 2025            │
│ Progress: 10/10 unit (100%)     │
│ [Lihat Detail]                  │
└─────────────────────────────────┘
```

### Skenario 3: Tidak Ada Laporan Sama Sekali
```
┌─────────────────────────────────┐
│ Monitoring Pengisian Analisis   │
│ Riwayat pengisian analisis dan  │
│ rekomendasi                     │
└─────────────────────────────────┘
┌─────────────────────────────────┐
│      ✓ Tidak Ada Laporan Aktif  │
│                                 │
│ Tidak ada laporan yang sedang   │
│ dalam fase pengisian analisis   │
│ dan rekomendasi saat ini.       │
└─────────────────────────────────┘
```

## Testing

### Test 1: Widget Tim Mutu dengan Laporan Sebelumnya
```bash
# Login sebagai tim mutu/admin
# Pastikan tidak ada laporan dengan status 'process' dan masih dalam deadline
# Verifikasi widget menampilkan "Laporan Sebelumnya"
# Verifikasi data stats ditampilkan dengan benar
```

### Test 2: Widget Unit Kerja dengan Laporan Sebelumnya
```bash
# Login sebagai user unit kerja
# Pastikan tidak ada laporan aktif yang relevan dengan unit kerja user
# Verifikasi widget menampilkan "Laporan Sebelumnya"
# Verifikasi hanya stats unit kerja user yang ditampilkan
```

### Test 3: Laporan Sebelumnya Tidak Tersedia
```bash
# Login dengan akun yang tidak memiliki laporan sebelumnya
# Verifikasi empty state ditampilkan dengan benar
```

## Cache dan Performance

### Cache Clearing Diperlukan
Setelah deployment, jalankan:
```bash
php artisan view:clear
php artisan cache:clear
```

### SQL Optimization
- Query menggunakan `whereDate()` untuk efisiensi
- Hanya mengambil 1 laporan (LIMIT 1)
- Filter kondisi di database level

## Maintenance Notes

1. **Relasi Database**: Pastikan `LaporanImut` dan `LaporanUnitKerja` relasi intact
2. **Duration Field**: `recommendation_analysis_duration` harus selalu ter-set (default 0)
3. **Status Updates**: Status laporan harus di-update saat selesai analisis
4. **User Permissions**: Pastikan `user->unitKerjas()` relasi working correctly

## Rollback (Jika Diperlukan)

Jika ingin kembali ke display empty state:

1. Edit views dan comment out section `$showPrevious`
2. Hapus method `getPreviousAnalysisReport()` dan `getPreviousRelevantAnalysisReport()`
3. Clear view cache: `php artisan view:clear`

Atau gunakan git to revert perubahan:
```bash
git revert <commit-hash>
```

## Future Enhancements

1. **Multiple Previous Reports**: Tampilkan lebih dari 1 laporan sebelumnya
2. **Archive List**: Button untuk melihat semua laporan sebelumnya
3. **Timeline View**: Visual timeline dari semua laporan
4. **Notifications**: Alert ketika laporan baru aktif
5. **Statistics**: Trend analysis dari completion rates

## Files Modified

- ✅ `app/Filament/Widgets/RecommendationAnalysisTimMutuWidget.php` - Tambah method
- ✅ `app/Filament/Widgets/RecommendationAnalysisUnitKerjaWidget.php` - Tambah method
- ✅ `resources/views/filament/widgets/recommendation-analysis-tim-mutu-widget.blade.php` - Update view
- ✅ `resources/views/filament/widgets/recommendation-analysis-unit-kerja-widget.blade.php` - Update view

## Status

✅ **IMPLEMENTASI SELESAI**

Fitur sudah fully implemented dan tested. Siap untuk deployment.
