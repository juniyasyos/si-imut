# Catatan Teknis (NOTES)

Catatan bebas untuk hal-hal yang belum final, keputusan sementara, dan hal yang perlu dicek ulang.

---

## Catatan Arsitektur

### Duplikasi Kode di `app/Kernel/`

Folder `app/Kernel/` berisi trait/support yang isinya mirip dengan `app/Traits/` dan `app/Support/`.

- **Lokasi**: `app/Kernel/Support/`, `app/Kernel/Traits/`, `app/Kernel/Providers/`
- **Status**: `Needs Review`
- **Kemungkinan**: Duplikasi yang tidak disengaja saat refactoring.
- **Saran**: Verifikasi apakah file-file di `app/Kernel/` masih dipakai atau bisa dialihkan ke `app/Traits/` dan `app/Support/`.

### `app/Domain/` Underutilized

Folder `app/Domain/` hanya berisi satu file: `DailyReport/TableViewDomain.php`.

- **Status**: `Needs Review`
- **Saran**: Jika tidak ada rencana pengembangan Domain layer lebih lanjut, pertimbangkan untuk memindahkan file tersebut ke lokasi yang lebih sesuai.

### Routes `test.php`

File `routes/test.php` berisi route khusus testing.

- **Status**: `Needs Verification`
- **Saran**: Pastikan route ini tidak aktif di environment production. Tambahkan pengecekan `if (app()->environment('local'))` jika perlu.

---

## Catatan Konfigurasi

### Session Driver Harus Database

Untuk kompatibilitas dengan IAM client plugin, session driver **harus** menggunakan `database`.

- File: `.env` → `SESSION_DRIVER=database`
- Jangan diganti ke `file` atau `cookie` selama IAM client digunakan.

### Queue Connection Default Database

Queue menggunakan database driver (bukan Redis) sebagai default.

- File: `.env` → `QUEUE_CONNECTION=database`
- Redis tersedia sebagai alternatif untuk production skala besar.

---

## Catatan Dependency

### Package Filament Kustom

Project menggunakan beberapa package Filament kustom milik `juniyasyos`:

| Package | Versi | Catatan |
|---|---|---|
| `juniyasyos/dash-stack-theme-juniyasyos` | ^1.3 | Theme Filament kustom |
| `juniyasyos/filament-backup` | v3.0.1 | Backup management |
| `juniyasyos/filament-media-manager` | 3.0 | Media manager |
| `juniyasyos/filament-pwa-kaido` | ^2.1 | PWA support |
| `juniyasyos/filament-settings-hub-kaido` | ^4.0 | Settings hub |
| `juniyasyos/nexaid-client` | v1.2.11 | IAM/SSO client |
| `juniyasyos/table-repeater` | v1.0.1 | Table repeater field |

Beberapa package mungkin belum dipublikasikan ke Packagist — sourced dari local atau private repository.

---

## Catatan Testing

### Benchmarking Tests

- 59 tests, 153 assertions
- Semua passing (100%)
- Dokumentasi: `docs/benchmarking-*`

### PWA Test

- Script: `generate-cache-pwa.sh`
- Workflow: `PWA-PRODUCTION-TESTING.md`

---

## TODOs Global

- [ ] Verifikasi duplikasi `app/Kernel/` vs `app/Traits/` dan `app/Support/`.
- [ ] Verifikasi `routes/test.php` aman di production.
- [ ] Dokumentasikan konfigurasi Socialite provider.
- [ ] Dokumentasikan konfigurasi Filament PWA.
- [ ] Setup CI/CD pipeline.
- [ ] Dokumentasi API dengan Scramble.
- [ ] Panduan instalasi untuk server bare-metal production.
- [ ] Backup & restore procedure documentation.
