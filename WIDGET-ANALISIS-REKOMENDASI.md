# 📊 Widget Pengisian Analisis & Rekomendasi - Dokumentasi

## 🎯 Overview

Widget **"Pengisian Analisis & Rekomendasi"** ditampilkan di halaman **ListLaporanImuts** untuk memberikan informasi real-time tentang laporan yang sedang dalam fase pengisian analisis dan rekomendasi.

Widget ini menampilkan:
- ✅ Laporan yang sedang dalam fase analisis (setelah periode penilaian berakhir)
- ✅ Deadline untuk setiap laporan
- ✅ Sisa waktu yang tersisa (countdown)
- ✅ Progress pengisian analisis per unit kerja
- ✅ Status indicator (URGENT, PERHATIAN, BERLANGSUNG)

---

## 📁 File Structure

```
app/Filament/Widgets/
├── RecommendationAnalysisRunningWidget.php    ← Widget Logic

resources/views/filament/widgets/
├── recommendation-analysis-running-widget.blade.php    ← Widget View

app/Filament/Resources/LaporanImutResource/Pages/
├── ListLaporanImuts.php    ← Integration (getHeaderWidgets method)
```

---

## 🔧 Widget Configuration

### Integration Point
File: `app/Filament/Resources/LaporanImutResource/Pages/ListLaporanImuts.php`

```php
protected function getHeaderWidgets(): array
{
    return [
        RecommendationAnalysisRunningWidget::class,
    ];
}
```

Widget ini akan ditampilkan **di atas header halaman ListLaporanImuts**, tepat di atas judul dan action buttons.

---

## 📊 Data & Logic

### Kriteria Laporan yang Ditampilkan

```
✓ Status = 'process' (Laporan sedang berlangsung)
✓ Periode penilaian sudah berakhir (assessment_period_end < hari ini)
✓ Masih dalam deadline analisis (hari ini ≤ analysis_deadline)
  
  Rumus deadline analisis:
  analysis_deadline = assessment_period_end + recommendation_analysis_duration (dalam hari)
```

### Status Indicators & Warna

| Status | Kondisi | Warna | Icon |
|--------|---------|-------|------|
| **URGENT** | < 1 hari sisa | 🔴 Red | ⚠️ Exclamation |
| **PERHATIAN** | 1-2 hari sisa | 🟠 Amber | ⚠️ Warning |
| **BERLANGSUNG** | > 2 hari sisa | 🔵 Blue | 🔄 Arrow |

### Progress Pengisian Analisis

Widget menampilkan progress bar untuk setiap laporan:

```
Pengisian: 5/8 unit kerja (62%)
[████░░░░░░░░░░░░░░] 62%
```

Logika:
- **Total Units**: Jumlah unit kerja yang terlibat dalam laporan
- **Completed Units**: Unit kerja yang sudah mengisi analisis/rekomendasi
- **Percentage**: (Completed / Total) × 100

---

## 🎨 Widget Features

### 1. Most Urgent Report (Summary Card)
Card di paling atas menampilkan laporan dengan deadline paling dekat:
- Status badge
- Nama laporan
- Periode penilaian
- Deadline analisis
- Progress bar pengisian unit kerja

### 2. Reports List
List horizontal dari semua laporan aktif:
- Nama laporan
- Deadline
- Sisa hari
- Progress bar dengan persentase

### 3. Empty State
Jika tidak ada laporan dalam fase analisis:
```
✓ Tidak Ada Laporan Aktif
  Tidak ada laporan yang sedang dalam fase pengisian 
  analisis dan rekomendasi saat ini.
```

### 4. Footer Info
Catatan penting tentang pengisian analisis.

---

## 🔍 Method Details

### `getOngoingAnalysisReports(): array`

**Deskripsi**: Mengambil semua laporan yang sedang dalam fase pengisian analisis dan rekomendasi.

**Return Format**:
```php
[
    [
        'id' => 1,
        'name' => 'Laporan IMUT Januari 2026',
        'slug' => 'laporan-imut-202601-a1b2c3d4',
        'period_end' => Carbon,
        'analysis_deadline' => Carbon,
        'days_remaining' => 5,
        'status' => 'info|warning|urgent',
        'is_overdue' => false,
        'laporan' => LaporanImut instance,
        'completion_stats' => [
            'total_units' => 8,
            'completed_units' => 5,
            'pending_units' => 3,
            'percentage' => 62,
        ]
    ]
]
```

**Sorting**: Diurutkan berdasarkan days remaining (deadline paling dekat duluan).

### `getAnalysisCompletionStats(LaporanImut $laporan): array`

**Deskripsi**: Menghitung statistik pengisian analisis per laporan.

**Return Format**:
```php
[
    'total_units' => 8,           // Total unit kerja
    'completed_units' => 5,       // Sudah mengisi
    'pending_units' => 3,         // Belum mengisi
    'percentage' => 62,           // Persentase
]
```

### `getOngoingAnalysisCount(): int`

**Deskripsi**: Mengembalikan jumlah laporan dalam fase analisis.

### `getMostUrgentReport(): ?array`

**Deskripsi**: Mengambil laporan dengan deadline paling dekat (paling urgent).

---

## 🎯 Use Cases

### 1. Monitoring Deadline
Admin/Tim Mutu bisa melihat laporan mana saja yang deadline-nya sudah dekat, memudahkan follow-up.

### 2. Progress Tracking
Lihat berapa banyak unit kerja yang sudah/belum mengisi analisis, membantu identifikasi bottleneck.

### 3. Quick Action
Klik laporan di widget untuk akses langsung ke halaman ListLaporanImuts.

---

## 🔐 Permission

Widget hanya ditampilkan untuk user yang memiliki permission:
```php
public static function canView(): bool
{
    return Auth::user()?->can('view_any', LaporanImut::class);
}
```

---

## 📱 Responsiveness

Widget fully responsive:
- ✅ Mobile (xs/sm)
- ✅ Tablet (md)
- ✅ Desktop (lg/xl)
- ✅ Dark Mode Support

---

## 🚀 Future Enhancements (Optional)

1. **Live Updates**: Tambahkan Livewire polling untuk auto-refresh setiap 5 menit
2. **Notification Bell**: Tambahkan badge count di top-right ketika ada laporan urgent
3. **Quick Actions**: Tombol untuk direct edit laporan atau kirim reminder ke unit kerja
4. **Export Report**: Button untuk export progress status ke Excel/PDF
5. **Email Notification**: Kirim reminder otomatis 1-2 hari sebelum deadline

Contoh implementasi Livewire polling:

```php
// Di widget class
protected $listeners = ['$refresh'];

#[On('refreshAnalysisWidget')]
public function refresh()
{
    // Auto refresh setiap 5 menit
}

// Di view
<div wire:poll-5000ms="refresh">
    <!-- Widget content -->
</div>
```

---

## 🐛 Troubleshooting

### Widget Tidak Muncul
**Solusi**:
1. Pastikan user memiliki permission `view_any` untuk LaporanImut
2. Jalankan `php artisan view:clear && php artisan view:cache`
3. Refresh browser (Ctrl+Shift+Delete)

### Data Tidak Update
**Solusi**:
1. Check apakah ada laporan dalam fase analisis di database
2. Verify nilai `recommendation_analysis_duration` di setting
3. Jalankan: `php artisan tinker` → `\App\Models\LaporanImut::where('status', 'process')->get()`

### Progress Bar Tidak Akurat
**Solusi**:
1. Cek field `analisis` dan `rekomendasi` di tabel `imut_penilaians`
2. Pastikan field sudah di-update ketika user mengisi analisis

---

## 📞 Support

Untuk pertanyaan atau issue, hubungi tim development atau buat issue di repository.

Last Updated: April 7, 2026
