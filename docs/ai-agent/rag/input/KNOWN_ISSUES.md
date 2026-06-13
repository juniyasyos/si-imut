# Known Issues

Daftar masalah yang diketahui dan pernah terjadi di project SIIMUT, lengkap
dengan analisis, dampak, dan solusi atau workaround.

---

## KI-001: Loading Dashboard 30 Detik

- **Status**: Resolved (v1.4.0)
- **Severity**: Critical
- **Area**: Performance
- **Gejala**: Navigasi bulan di halaman Daily Report membutuhkan waktu 25-30 detik
  untuk merender.
- **Penyebab**: Penggunaan `whereHas()` di dalam query aggregasi menghasilkan
  correlated subquery — MySQL menjalankan query tambahan untuk setiap baris hasil
  query utama (N+1 query di level database).
- **Dampak**: Dashboard hampir tidak bisa dipakai untuk navigasi harian/bulanan.
  Pengguna menunggu 30 detik setiap kali ganti bulan.
- **Solusi**: Ganti `whereHas()` dengan `JOIN` langsung + composite index.
  - Fix #1: [N+1 Query Fix](upgrade/fix-n1-query-wherehas-vs-join.md) — `whereHas` → `JOIN`
  - Fix #2: [Composite Index](upgrade/fix-database-index-composite.md) — tambah index `(form_template_id, report_date)`
  - Fix #3: [Payload Serialization](upgrade/fix-payload-matrix-cells.md) — hapus serialisasi data matrix
  - **Hasil**: 30 detik → < 2 detik

---

## KI-002: Duplikasi Kode di `app/Kernel/`

- **ID**: KI-002
- **Status**: Needs Review
- **Severity**: Medium
- **Area**: Arsitektur
- **Gejala**: Folder `app/Kernel/` berisi trait/support yang isinya mirip dengan
  `app/Traits/` dan `app/Support/`.
- **Penyebab**: Kemungkinan duplikasi yang tidak disengaja saat refactoring.
  Belum diverifikasi apakah file-file di `app/Kernel/` masih dipakai atau sudah
  bisa dialihkan.
- **Dampak**: Kebingungan developer — file mana yang seharusnya dipakai?
  Potensi bug jika satu file diubah tapi duplikasinya tidak.
- **Solusi / Workaround**:
  - Verifikasi apakah setiap file di `app/Kernel/Support/`, `app/Kernel/Traits/`,
    dan `app/Kernel/Providers/` masih memiliki `use` statement dari file lain.
  - Jika tidak dipakai, pindahkan atau hapus.
  - Jika dipakai, aliaskan ke `app/Traits/` dan `app/Support/` yang sudah ada.

---

## KI-003: `app/Domain/` Underutilized

- **ID**: KI-003
- **Status**: Needs Review
- **Severity**: Low
- **Area**: Arsitektur
- **Gejala**: Folder `app/Domain/` hanya berisi satu file:
  `DailyReport/TableViewDomain.php`. Folder ini terlihat tidak selesai
  implementasinya.
- **Penyebab**: Domain layer dicanangkan tapi tidak dilanjutkan. Mungkin karena
  perubahan arah ke modular monolith.
- **Dampak**: Minor — satu file di folder yang tidak konsisten.
- **Solusi / Workaround**:
  - Jika tidak ada rencana Domain layer, pindahkan file ke lokasi yang lebih
    sesuai (misal `app/Modules/DailyReport/Domain/`).
  - Atau hapus jika file tersebut sudah tidak dipakai.

---

## KI-004: Routes `test.php` Mungkin Aktif di Production

- **ID**: KI-004
- **Status**: Needs Verification
- **Severity**: High
- **Area**: Security
- **Gejala**: File `routes/test.php` berisi route khusus testing.
  Belum ada pengecekan environment — route ini bisa diakses di production.
- **Penyebab**: Route testing diletakkan di `routes/test.php` tanpa conditional
  `if (app()->environment('local'))`.
- **Dampak**: Route testing bisa diakses publik di production. Jika route tersebut
  mengekspos data sensitif atau menjalankan operasi berbahaya, ini risiko keamanan.
- **Solusi / Workaround**:
  - Tambahkan pengecekan environment: `if (app()->environment('local'))`
  - Atau pindahkan route ke file yang hanya di-load di environment local.
  - Verifikasi isi `routes/test.php` — pastikan tidak ada operasi destruktif.

---

## KI-005: Session Driver Wajib Database untuk IAM

- **ID**: KI-005
- **Status**: Resolved (documented)
- **Severity**: High
- **Area**: Konfigurasi
- **Gejala**: Jika `SESSION_DRIVER` diubah dari `database` ke `file` atau `cookie`,
  IAM client plugin tidak berfungsi — SSO gagal, user tidak bisa login.
- **Penyebab**: IAM client (`juniyasyos/nexaid-client`) bergantung pada session
  database untuk menyimpan state SSO.
- **Dampak**: SSO/login IAM tidak berfungsi.
- **Solusi / Workaround**:
  - Pastikan `SESSION_DRIVER=database` di `.env`.
  - Jangan diganti ke `file` atau `cookie` selama IAM client aktif.
  - Jika butuh session performa tinggi, gunakan Redis via `SESSION_DRIVER=redis`
    (bukan file/cookie).

---

## KI-006: Nested Alpine x-data ReferenceError

- **ID**: KI-006
- **Status**: Resolved (v1.4.0)
- **Severity**: Medium
- **Area**: UI / Livewire
- **Gejala**: Error `Uncaught ReferenceError` di konsol browser saat membuka
  halaman Daily Report dengan komponen nested Livewire + Alpine.
- **Penyebab**: Alpine x-data bersarang (nested) menyebabkan konflik scope
  JavaScript. Komponen Livewire di dalam komponen Livewire lain memicu error.
- **Dampak**: Beberapa fitur interaktif di Daily Report tidak berjalan,
  terutama date navigation dan filter.
- **Solusi**: Restruktur komponen Livewire agar tidak nested. Pisahkan state
  loading date navigation dan main content.

---

## KI-007: Package Filament Kustom dari Private Repository

- **ID**: KI-007
- **Status**: Needs Verification
- **Severity**: Medium
- **Area**: Dependency
- **Gejala**: Beberapa package Filament kustom milik `juniyasyos` mungkin belum
  dipublikasikan ke Packagist — sourced dari local atau private repository.
  Developer baru mungkin tidak bisa `composer install` tanpa akses.
- **Penyebab**: Package kustom yang belum publish ke Packagist.
- **Dampak**: Developer baru tidak bisa menjalankan `composer install` tanpa
  akses ke repository private.
- **Solusi / Workaround**:
  - Minta akses ke repository private yang relevan.
  - Atau konfigurasi `repositories` di `composer.json` untuk path local / VCS.
  - Dokumentasi lebih lanjut: lihat [NOTES.md](NOTES.md) untuk daftar package.

---

## KI-008: Auto-generation Schedule Berubah dari Monthly ke Daily

- **ID**: KI-008
- **Status**: Resolved (v1.4.0)
- **Severity**: Low
- **Area**: Performance
- **Gejala**: Setelah perubahan auto-generation schedule dari monthly ke daily,
  beban server meningkat.
- **Penyebab**: Perubahan frekuensi tanpa optimasi query yang sesuai.
- **Dampak**: Peningkatan beban server di jam sibuk, terutama saat banyak unit
  kerja submit laporan bersamaan.
- **Solusi**: Implementasi composite index + query optimization (lihat KI-001).
  Monitoring berkala untuk beban scheduler.

---

## RAG Metadata

## KI-001 - Loading Dashboard 30 Detik

Type: KnownIssue
Status: Resolved
Area: Performance
Related Modules:
- MOD-001
Related Services:
- Needs Verification
Related Commands:
- Needs Verification
Related Decisions:
- DEC-002
- DEC-003
- DEC-004
Source:
- commit: 8dece0a

Summary:
Masalah performa loading navigasi bulan di halaman Daily Report yang mencapai 30 detik. Disebabkan oleh correlated subquery dari penggunaan whereHas(). Diperbaiki dengan transisi ke direct JOIN dan pembuatan composite index.

