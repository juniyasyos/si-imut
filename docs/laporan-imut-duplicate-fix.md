# Fix untuk Duplicate Entry Error LaporanImut

## Problem
Error SQL duplicate entry muncul ketika mencoba membuat LaporanImut dengan kombinasi `report_year` dan `report_month` yang sama:

```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '2025-1' for key 'laporan_imuts.unique_periode_laporan'
```

Error ini menampilkan debug bar yang tidak user-friendly dan membingungkan user.

## Root Cause
1. Database constraint `unique_periode_laporan` pada tabel `laporan_imuts` memaksa kombinasi `[report_year, report_month]` harus unik
2. Tidak ada validation di level aplikasi untuk mencegah duplicate sebelum sampai ke database
3. Error handling tidak menangani QueryException dengan baik untuk memberikan pesan user-friendly

## Solusi yang Diterapkan

### 1. Custom Validation Rule
**File:** `app/Rules/UniqueLaporanPeriode.php`
- Validation rule khusus untuk mengecek periode laporan yang unik
- Mendukung ignore ID untuk edit mode
- Memberikan pesan error yang informatif dengan nama bulan

### 2. Form Validation Enhancement
**File:** `app/Filament/Resources/LaporanImutResource/Schema/LaporanImutSchema.php`
- Menambahkan real-time validation pada field `report_month` dan `report_year`
- Menggunakan `reactive()` dan `live()` untuk immediate feedback
- Custom `validateUniquePeriod()` method untuk notifikasi dinamis

### 3. Enhanced Create Page
**File:** `app/Filament/Resources/LaporanImutResource/Pages/CreateLaporanImut.php`
- Pre-validation di `mutateFormDataBeforeCreate()`
- Custom error handling di `handleRecordCreation()`
- User-friendly notifications dengan action buttons untuk melihat laporan existing

### 4. Enhanced Edit Page
**File:** `app/Filament/Resources/LaporanImutResource/Pages/EditLaporanImut.php`
- Pre-validation di `mutateFormDataBeforeSave()`
- Custom error handling di `handleRecordUpdate()`
- Exclude current record saat validasi update

### 5. Model-Level Validation
**File:** `app/Models/LaporanImut.php`
- Validation di model events (`creating`, `updating`)
- Method `validateUniquePeriod()` untuk consistent validation logic
- ValidationException dengan pesan yang informatif

### 6. Global Exception Handling
**File:** `bootstrap/app.php`
- Custom exception handling untuk QueryException dengan code 23000
- Khusus menangani `unique_periode_laporan` constraint violation
- Mengkonversi QueryException menjadi ValidationException yang user-friendly

## User Experience Improvements

### Sebelum:
- Error SQL mentah ditampilkan
- Debug bar muncul dengan stack trace
- User bingung apa yang harus dilakukan

### Sesudah:
- Modal notification dengan pesan yang jelas
- Action buttons untuk melihat/edit laporan existing
- Form validation real-time mencegah submit invalid data
- Pesan error dalam Bahasa Indonesia dengan nama bulan

## Features

### Real-time Validation
- Validation terjadi saat user mengubah bulan atau tahun
- Notifikasi langsung jika periode sudah ada
- Auto-reset ke nilai yang valid

### Smart Notifications
- Menampilkan nama laporan yang sudah ada
- Action buttons untuk navigasi ke laporan existing
- Persistent notification untuk memastikan user melihat

### Multi-Layer Protection
1. **Form Level**: Real-time validation dengan custom rules
2. **Page Level**: Pre-validation sebelum submit
3. **Model Level**: Validation di model events
4. **Global Level**: Exception handling untuk backup

## Testing
Untuk test functionality:
1. Buat laporan baru untuk periode tertentu (misal: Januari 2025)
2. Coba buat laporan lagi untuk periode yang sama
3. Observasi:
   - Form validation mencegah submit
   - Modal notification muncul
   - Action buttons tersedia
   - Tidak ada debug bar yang muncul

## Hasil
- ✅ No more SQL error debug bar
- ✅ User-friendly modal notifications
- ✅ Real-time form validation
- ✅ Action buttons untuk navigation
- ✅ Consistent error handling across create/edit
- ✅ Bahasa Indonesia error messages