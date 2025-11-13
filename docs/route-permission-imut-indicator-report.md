# Dokumentasi: Route Permission untuk IMUT Indicator Report

## Ringkasan Perubahan

Route `/print/preview/imut-indicator-report` telah diamankan dengan permission `view_all_data_imut::data`, sehingga hanya user yang memiliki permission tersebut yang dapat mengaksesnya.

## Detail Implementasi

### 1. Route Protection (routes/web.php)

Route telah ditambahkan middleware `auth` dan `can`:

```php
Route::get('/preview/imut-indicator-report', [PrintReportController::class, 'previewImutIndicatorReport'])
    ->name('preview.imut-indicator-report')
    ->middleware(['auth', 'can:view_all_data_imut::data']);
```

**Middleware yang digunakan:**
- `auth`: Memastikan user sudah login
- `can:view_all_data_imut::data`: Memastikan user memiliki permission yang diperlukan

### 2. Controller Authorization Check (PrintReportController.php)

Sebagai lapisan keamanan tambahan, ditambahkan Gate check di method controller:

```php
public function previewImutIndicatorReport(Request $request)
{
    // Authorization check
    if (!Gate::allows('view_all_data_imut::data')) {
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
    
    // ...rest of the code
}
```

## Konsistensi dengan Page Authorization

Permission ini konsisten dengan yang digunakan di `SummaryDiagram.php`:

```php
public static function canAccess(array $parameters = []): bool
{
    $user = Auth::user();

    return Gate::any([
        'view_all_data_imut::data'
    ], $user);
}
```

## Testing

### Test Akses Berhasil
1. Login sebagai user yang memiliki permission `view_all_data_imut::data`
2. Akses URL: `/print/preview/imut-indicator-report?imut_data_id=1&laporan_id=1`
3. Halaman harus dapat diakses dengan normal

### Test Akses Ditolak
1. Login sebagai user yang **tidak** memiliki permission `view_all_data_imut::data`
2. Akses URL yang sama
3. Sistem akan menampilkan error 403 dengan pesan: "Anda tidak memiliki izin untuk mengakses halaman ini."

### Test Tanpa Login
1. Logout atau akses tanpa login
2. Akses URL yang sama
3. Sistem akan redirect ke halaman login

## Keamanan

✅ **Double Protection**: Route dilindungi di level middleware DAN di level controller
✅ **Consistent Permission**: Menggunakan permission yang sama dengan page terkait
✅ **Clear Error Messages**: Pesan error yang jelas untuk user yang tidak memiliki akses

## Tanggal Implementasi

13 November 2025
