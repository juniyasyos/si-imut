# Change Summary (Sejak 2026-01-01)

Dokumen ini merangkum perubahan produk skala besar dan fitur penting yang diimplementasikan sejak awal tahun 2026.

## 1. Pengembangan Fitur Daily Report & Kepatuhan
- **Deskripsi**: Penambahan dan perbaikan ekstensif pada fitur Daily Report (Laporan Harian), mencakup entry laporan, pengoptimalan pemuatan data matriks, server-side pagination, dan simulasi data (contoh: Kepatuhan Kebersihan Tangan).
- **Commit Contoh**: `8dece0a` (Server-side pagination & search), `24e3b1a` (Fix 30-second bottleneck), `12c8eec` (Back data entry duration)

## 2. Peningkatan Form Engine & Templating
- **Deskripsi**: Implementasi generasi form dinamis yang lebih canggih, FormSchemaBuilder, pembuatan versi template (versioning), serta penentuan batas waktu validitas form template. Ini memungkinkan fleksibilitas yang sangat tinggi dalam pengumpulan data.
- **Commit Contoh**: `4bb5bd7` (SyncFormTemplateDates command & versioning), `1efd151` (Dynamic form field generation), `f879acb` (Form template versioning system)

## 3. Sistem Pelaporan (Reporting) & Monitoring Lanjutan
- **Deskripsi**: Penambahan laporan tabel dinamis, laporan berbasis kategori, laporan unit kerja, integrasi reporting berbasis JSON, serta integrasi toggle indikator benchmarking. Mendukung juga ekspor ke format PDF. Peningkatan `ImutCapaianWidget` untuk menampilkan tren pencapaian per "Triwulan" (Quarter) menggunakan grafik garis mulus melintasi bulan.
- **Commit Contoh**: `5aa68cb` (Unit Kerja Laporan), `6f86712` (Category-based indicator report), `3a5d5e9` (Monthly monitoring feature)

## 4. Otorisasi, Integrasi IAM, & Keamanan (SSO)
- **Deskripsi**: Integrasi dan pembaruan sistem SSO (Single Sign-On), penyesuaian pengaturan JWT, transisi penggunaan dependency dari `auth-bridge-client` ke `nexaid-client`, serta pembuatan perintah-perintah audit untuk otorisasi dan penghapusan duplikasi pengguna.
- **Commit Contoh**: `1062390` (Replace auth-bridge-client with nexaid-client), `f295801` (SSO login redirection), `1fc0e1f` (RolePermissionManager command)

## 5. UI/UX & Pembaruan Filament ke Versi 4
- **Deskripsi**: Peningkatan visual antarmuka pengguna yang sangat signifikan dengan upgrade Filament dari versi 3 ke versi 4. Termasuk refactor topbar, optimasi desain komponen, penerapan custom scrollbar, hingga asset tema CSS baru.
- **Commit Contoh**: `84565d6` (Upgrade Filament from v3 to v4), `0091805` (Theme styles & topbar functionality), `ed6c57d` (Custom scrollbar styles)

## 6. Kinerja & Refactor Arsitektur
- **Deskripsi**: Pembersihan dan pengoptimalan kode secara mendalam (refactor) dengan mengadopsi Repository Pattern untuk Daily Report Services, penggunaan `CalculationService` yang menggantikan fungsi raw SQL, hingga peningkatan sistem pengujian dengan dukungan pipeline CI (MySQL/SQLite).
- **Commit Contoh**: `ee586a5` (Repository Pattern untuk Daily Report), `1be0c3f` (Replace SqlExpressionBuilder), `8a34fd1` (Livewire payload serialization fix)
