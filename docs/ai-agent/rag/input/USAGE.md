# Panduan Penggunaan

Panduan ini mencakup cara menjalankan dan menggunakan fitur utama SIIMUT.

---

## Menjalankan Aplikasi

### Mode Development (Full Stack)

```bash
# Server + Queue + Schedule + Logs + Vite
composer run dev
```

Perintah ini menjalankan 5 proses bersamaan:
- `php artisan serve` — HTTP server (port 8000)
- `php artisan queue:listen` — Queue worker
- `php artisan schedule:work` — Scheduler
- `php artisan pail` — Log viewer
- `npm run dev` — Vite dev server (HMR)

### Mode Development (Laravel Only)

```bash
composer run dev-lara
```

### Mode IAM / SSO

```bash
composer run iam
```
Menjalankan server di port **8088** bersama queue dan log.

### Mode Production

```bash
php artisan serve
# atau via Nginx (lihat .docker/nginx/)
```

---

## Perintah Penting

### Artisan Commands

```bash
# Informasi aplikasi
php artisan about

# Database
php artisan migrate                    # Jalankan migrasi
php artisan migrate:fresh              # Reset + migrasi ulang
php artisan db:seed                    # Seeder
php artisan db:show                    # Lihat status database

# Shield (Permission)
php artisan shield:generate --all      # Generate permissions
php artisan shield:super-admin --user=1

# Queue
php artisan queue:listen --tries=1     # Jalankan queue worker
php artisan queue:monitor              # Monitor antrian

# Storage
php artisan storage:link               # Symlink storage

# Cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Debug
php artisan pail                       # Log viewer (Laravel Pail)
```

### Makefile Commands

```bash
make up            # Start Docker containers
make down          # Stop containers
make logs          # Tail container logs
make shell:app     # SSH ke container PHP
make build         # Build images
make ps            # Status container
make restart       # Restart containers
```

---

## Fitur Utama

### 1. Dashboard

Dashboard utama menampilkan visualisasi indikator mutu secara real-time:

- Grafik tren indikator per periode
- Ringkasan unit kerja
- Status capaian mutu
- Notifikasi dan peringatan

### 2. Daily Report

Input laporan harian dengan mekanisme live update:

1. Buka menu **Daily Report** di sidebar Filament.
2. Pilih unit kerja dan tanggal.
3. Isi form indikator (numerator/denominator).
4. Submit — data langsung tampil di dashboard.
5. Sistem otomatis menghitung persentase dan menandai tren.

**Form Template Versioning**:
- Admin bisa membuat/mengelola template form.
- Pilih versi template untuk setiap periode.
- Field types: text, number, radio, checkbox, select, date, time, long text.

### 3. Manajemen Indikator Mutu

Kelola data master indikator:

- **Imut Profile**: Profil indikator mutu.
- **Imut Category**: Kategori indikator.
- **Imut Data**: Data indikator utama.
- **Imut Penilaian**: Penilaian indikator.
- **Imut Data Notes**: Catatan analisis per data indikator.

### 4. Benchmarking

Bandingkan performa indikator antar unit kerja:

- Validasi periode (start date / end date).
- Filter region dan level.
- Cache management untuk performa.
- Lihat di menu **Benchmarking** di sidebar.

### 5. Laporan & Export

Hasilkan laporan dalam berbagai format:

```bash
# Via browser, akses:
/reports/category            # Laporan kategori
/reports/category/{id}/pdf   # Export PDF
/reports/print               # Print-ready report

# Via Livewire report:
/livewire-report             # Filtering interaktif
```

Format export: **PDF**, **Excel**.

### 6. Media Manager

Upload dan kelola file media per unit kerja:

- Upload gambar/dokumen.
- Folder sync otomatis.
- Backup/export media.

### 7. Logs & Audit

- **Activity Log**: Lihat histori perubahan data.
- **Audit Trail**: Setiap operasi CRUD tercatat (created_by, updated_by).

---

## Manajemen Pengguna & Role

### Role & Permission

SIIMUT menggunakan **Spatie Laravel Permission** + **Filament Shield**:

```bash
# Generate permissions (setelah migrasi)
php artisan shield:generate --all --panel=admin

# Set super admin
php artisan shield:super-admin --user=1
```

Role yang tersedia (default):

| Role | Deskripsi |
|---|---|
| Super Admin | Akses penuh ke semua fitur |
| Admin | Manajemen data dan pengguna |
| PIC Unit Kerja | Input data unit kerja |
| Viewer | Lihat laporan dan dashboard |

### User Management

1. Buka menu **Users** di sidebar.
2. Tambah/edit user.
3. Assign role dan unit kerja.
4. User bisa login via email/password atau SSO.

---

## Cron / Scheduled Tasks

Project memiliki scheduler untuk task otomatis:

```bash
# Jalankan scheduler (di background)
php artisan schedule:work
```

Task yang dijadwalkan:
- **TODO**: Generate laporan periodik otomatis.
- **TODO**: Backup data.
- **TODO**: Cache warming.

---

## Contoh Workflow

### Workflow: Input Laporan Harian

1. Login sebagai PIC Unit Kerja.
2. Buka **Daily Report**.
3. Pilih unit kerja + tanggal.
4. Isi numerator & denominator.
5. Pilih template form yang sesuai.
6. Submit.
7. Cek dashboard — data langsung update.
8. (Opsional) Tambahkan catatan analisis.

### Workflow: Export Laporan Triwulan

1. Login sebagai Admin.
2. Buka menu **Laporan**.
3. Pilih periode dan unit kerja.
4. Klik **Export PDF**.
5. File siap diunduh.

---

## Catatan

- Pastikan queue worker berjalan untuk fitur async (export, notifikasi).
- Untuk production, gunakan Nginx (bukan artisan serve).
- Untuk PWA, jalankan `generate-cache-pwa.sh` setelah build.
