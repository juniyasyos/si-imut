# Daily Reports Feature

## Overview
Fitur Daily Reports telah berhasil dibuat untuk menampilkan daftar laporan harian yang telah diinputkan dalam sistem SIIMUT. 

## Komponen yang Dibuat

### 1. Database Seeder
**File:** `database/seeders/DailyReportSeeder.php`

Seeder ini membuat data sample untuk demonstrasi fitur daily reports:
- Membuat form template "Checklist Kebersihan Tangan Harian" jika belum ada
- Membuat 5 form fields dengan berbagai tipe (radio, select, toggle, checkbox)
- Generate laporan harian untuk 30 hari terakhir (1-3 laporan per hari)
- Menghitung compliance score berdasarkan weighted average
- Total 66 laporan sample yang dibuat

### 2. List Daily Reports Page
**File:** `app/Filament/Resources/ImutProfileResource/Pages/ListDailyReports.php`

Page Filament untuk menampilkan daftar laporan harian dengan fitur:
- **Tabel interaktif** dengan kolom:
  - Tanggal Laporan
  - Unit Kerja  
  - Diinput Oleh
  - Total Skor (dengan color coding)
  - Status Kepatuhan (Compliant/Non-Compliant)
  - Auto Calculation indicator
  - Waktu Input

- **Filter tersedia:**
  - Unit Kerja (multiple select)
  - Status Kepatuhan
  - Range tanggal (dari-sampai)
  - Range skor (Excellent/Good/Fair/Poor)

- **Actions:**
  - View Detail (dengan modal lengkap)
  - Export PDF (placeholder)

### 3. View Templates
**Files:** 
- `resources/views/filament/resources/imut-profile-resource/pages/list-daily-reports.blade.php`
- `resources/views/filament/modals/daily-report-detail.blade.php`

Templates untuk rendering tabel dan modal detail laporan.

### 4. Route Integration
**File:** `app/Filament/Resources/ImutProfileResource.php`

Ditambahkan route baru:
```php
'list-daily-reports' => Pages\ListDailyReports::route('/{record:slug}/daily-reports')
```

### 5. Navigation Integration
**File:** `app/Filament/Resources/ImutProfileResource/Pages/EditImutProfile.php`

Ditambahkan action button "Lihat Laporan Harian" di header actions untuk navigasi mudah.

## Struktur Database

### Tabel Utama:
- `daily_report_responses` - Menyimpan laporan harian
- `field_responses` - Menyimpan jawaban per field (JSON format)
- `enhanced_form_fields` - Field-field form
- `form_field_options` - Opsi jawaban untuk field

### Relasi:
- DailyReportResponse belongsTo FormTemplate, UnitKerja, User
- FieldResponse belongsTo DailyReportResponse, EnhancedFormField
- Auto-calculation compliance score berdasarkan weighted average

## Cara Menggunakan

1. **Akses Daily Reports:**
   - Buka ImutProfile edit page
   - Klik tombol "Lihat Laporan Harian" di header
   - Atau akses URL: `/imut-profiles/{slug}/daily-reports`

2. **Run Seeder:**
   ```bash
   php artisan db:seed --class=DailyReportSeeder
   ```

3. **View Reports:**
   - Filter berdasarkan kriteria yang diinginkan
   - Klik "Lihat Detail" untuk melihat response lengkap
   - Export ke PDF (coming soon)

## Features Highlights

- **Real-time Compliance Scoring:** Otomatis menghitung skor compliance berdasarkan weighted average
- **Critical Field Detection:** Deteksi field kritis yang gagal untuk auto-fail
- **Flexible Field Types:** Support radio, select, toggle, checkbox dengan multiple options
- **Rich Filtering:** Multiple filter options untuk analisis data
- **Interactive UI:** Responsive table dengan color-coded status
- **Detailed View:** Modal dengan breakdown response per field
- **Sample Data:** 66 realistic sample reports untuk demonstrasi

## Status
✅ **Completed:**
- Database seeder dengan 66 sample reports
- Interactive table with filtering
- Detailed view modal
- Navigation integration
- Compliance scoring calculation

🔄 **In Progress:**
- PDF Export functionality
- Create new report functionality  
- Edit report functionality

## Sample Data Generated
- **Total Reports:** 66 laporan harian
- **Date Range:** 30 hari terakhir
- **Form Template:** "Checklist Kebersihan Tangan Harian"
- **Fields:** 5 field dengan berbagai tipe validasi
- **Compliance Rate:** ~70% (bias towards correct answers)
- **Unit Kerjas:** Multiple unit kerja
- **Users:** Multiple users sebagai pelapor