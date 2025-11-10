# Fitur Catatan Data IMUT (IMUT Data Notes)

## Deskripsi
Fitur ini menambahkan kemampuan untuk membuat dan mengelola catatan (notes) pada data IMUT. Catatan ini berguna untuk mendokumentasikan analisis, rekomendasi, dan informasi penting lainnya terkait data IMUT tertentu.

## Komponen yang Dibuat

### 1. Database Migration
**File**: `database/migrations/2025_11_10_233330_create_imut_data_notes_table.php`

Struktur tabel `imut_data_notes`:
- `id`: Primary key
- `imut_data_id`: Foreign key ke tabel imut_data
- `note_name`: Nama catatan (varchar 255)
- `period_start`: Tanggal mulai periode (nullable)
- `period_end`: Tanggal akhir periode (nullable)
- `related_laporan_ids`: JSON array berisi ID laporan terkait (nullable)
- `recommendation`: Teks rekomendasi (text, nullable)
- `analysis`: Teks analisis (text, nullable)
- `additional_notes`: Catatan tambahan (text, nullable)
- `priority`: Enum (low, medium, high) default medium
- `is_active`: Boolean status aktif/tidak aktif
- `created_by`: Foreign key ke tabel users
- `timestamps`: created_at, updated_at
- `softDeletes`: deleted_at

### 2. Model Eloquent
**File**: `app/Models/ImutDataNote.php`

Fitur Model:
- Menggunakan `SoftDeletes` untuk soft delete
- Menggunakan `LogsActivity` untuk audit trail
- Cast otomatis untuk `related_laporan_ids` (array), `period_start` dan `period_end` (date)
- Relasi:
  - `imutData()`: BelongsTo ke ImutData
  - `creator()`: BelongsTo ke User
- Accessor:
  - `getLaporanNamesAttribute()`: Mendapatkan nama-nama laporan dari related_laporan_ids
- Scopes:
  - `forImutData($imutDataId)`: Filter berdasarkan IMUT Data
  - `active()`: Filter hanya note aktif
  - `byPriority($priority)`: Filter berdasarkan prioritas

### 3. Update Model ImutData
**File**: `app/Models/ImutData.php`

Ditambahkan relasi:
```php
public function notes(): HasMany
{
    return $this->hasMany(ImutDataNote::class);
}
```

### 4. Widget Table untuk Menampilkan Notes
**File**: `app/Filament/Resources/ImutDataResource/Widgets/ImutDataNotesReport.php`

Fitur Widget:
- Tampilan tabel dengan kolom:
  - Nama Catatan
  - Periode
  - Laporan Terkait
  - Prioritas (badge berwarna)
  - Status Aktif/Tidak Aktif
  - Dibuat Oleh
  - Tanggal Dibuat
- Filter berdasarkan:
  - Prioritas
  - Status Aktif
- Actions:
  - **Create**: Tambah catatan baru dengan form lengkap
  - **View**: Lihat detail catatan dalam modal
  - **Edit**: Edit catatan yang ada
  - **Delete**: Hapus catatan (soft delete)
- Bulk Actions:
  - Delete multiple records

### 5. View Detail Note
**File**: `resources/views/filament/resources/imut-data-resource/widgets/note-detail.blade.php`

Tampilan detail catatan dengan styling yang rapi:
- Informasi periode
- Laporan terkait
- Rekomendasi (dengan background biru)
- Analisis (dengan background hijau)
- Catatan tambahan (dengan background abu-abu)
- Informasi pembuat dan tanggal

### 6. Update Halaman Summary Diagram
**File**: `app/Filament/Resources/ImutDataResource/Pages/SummaryDiagram.php`

Perubahan:
- Menambahkan method `getFooterWidgets()` yang mengembalikan widget ImutDataNotesReport
- Widget akan otomatis mendapatkan `imutDataId` dari context

### 7. Update View dengan Tabs
**File**: `resources/views/filament/resources/imut-data-resource/pages/summary-imut-data-diagram.blade.php`

Fitur Tabs:
- **Tab Summary Data**: Menampilkan tabel summary data IMUT (existing)
- **Tab Catatan**: Menampilkan tabel catatan dengan CRUD lengkap
- Menggunakan Alpine.js untuk interaksi tabs
- Transisi smooth antar tabs
- Icon untuk setiap tab

### 8. Seeder untuk Testing
**File**: `database/seeders/ImutDataNoteSeeder.php`

Membuat sample data:
- 2-3 catatan untuk setiap data IMUT
- Dengan periode, laporan terkait, rekomendasi, dan analisis
- Priority random (low/medium/high)

## Cara Menggunakan

### 1. Melihat Catatan
1. Buka halaman **Daftar Data IMUT**
2. Pilih salah satu data IMUT dan klik tombol **Ikhtisar** atau **Summary**
3. Klik tab **Catatan** untuk melihat daftar catatan

### 2. Menambah Catatan Baru
1. Di tab Catatan, klik tombol **Tambah Catatan**
2. Isi form:
   - **Nama Catatan**: Judul/nama catatan
   - **Tanggal Mulai/Akhir Periode**: Periode yang relevan (opsional)
   - **Laporan Terkait**: Pilih satu atau lebih laporan (opsional)
   - **Rekomendasi**: Tulis rekomendasi (opsional)
   - **Analisis**: Tulis analisis (opsional)
   - **Catatan Tambahan**: Informasi tambahan lainnya (opsional)
   - **Prioritas**: Pilih tingkat prioritas (Rendah/Sedang/Tinggi)
   - **Status Aktif**: Toggle untuk mengaktifkan/nonaktifkan
3. Klik **Create** untuk menyimpan

### 3. Melihat Detail Catatan
1. Klik icon **mata** (View) pada baris catatan
2. Modal akan muncul menampilkan detail lengkap

### 4. Mengedit Catatan
1. Klik icon **pensil** (Edit) pada baris catatan
2. Form akan muncul dengan data yang sudah terisi
3. Ubah data yang diperlukan
4. Klik **Save changes**

### 5. Menghapus Catatan
1. Klik icon **sampah** (Delete) pada baris catatan
2. Konfirmasi penghapusan
3. Data akan dihapus (soft delete)

## Permissions
Untuk menambahkan permissions khusus untuk catatan, Anda dapat membuat permissions baru seperti:
- `view_imut_data_note`
- `create_imut_data_note`
- `update_imut_data_note`
- `delete_imut_data_note`

Dan update Policy di `app/Policies/ImutDataNotePolicy.php` sesuai kebutuhan.

## API Endpoints (Opsional)
Jika ingin menambahkan API endpoints untuk catatan:

```php
// routes/api.php
Route::get('/imut-data/{imutDataId}/notes', [ImutDataNoteController::class, 'index']);
Route::post('/imut-data/{imutDataId}/notes', [ImutDataNoteController::class, 'store']);
Route::get('/notes/{noteId}', [ImutDataNoteController::class, 'show']);
Route::put('/notes/{noteId}', [ImutDataNoteController::class, 'update']);
Route::delete('/notes/{noteId}', [ImutDataNoteController::class, 'destroy']);
```

## Keuntungan Fitur Ini
1. **Dokumentasi Lengkap**: Semua analisis dan rekomendasi tercatat dengan baik
2. **Tracking Periode**: Dapat melacak catatan berdasarkan periode tertentu
3. **Relasi dengan Laporan**: Menghubungkan catatan dengan laporan-laporan spesifik
4. **Prioritas**: Membantu mengidentifikasi catatan yang lebih penting
5. **Audit Trail**: Semua perubahan tercatat berkat LogsActivity
6. **Soft Delete**: Data tidak hilang permanen, bisa di-restore jika diperlukan
7. **UI yang User-Friendly**: Dengan tabs yang rapi dan form yang lengkap

## Future Enhancements
Beberapa peningkatan yang bisa ditambahkan:
1. Attachment files untuk catatan
2. Notifikasi untuk catatan prioritas tinggi
3. Export catatan ke PDF/Excel
4. Comment/reply system untuk kolaborasi
5. Tags untuk kategorisasi catatan
6. Search dan advanced filtering
7. Timeline view untuk melihat history catatan
8. Integration dengan sistem reminder/calendar
