# Project Timeline (Sejak 2026-01-01)

Dokumen ini merangkum rentang waktu (timeline) dari iterasi pengembangan proyek SI-IMUT berdasarkan fase dan bulan pengerjaan sejak awal tahun 2026.

## Juni 2026
- **Fokus Utama**: Optimasi Kinerja, Pembaruan Dependensi & Audit Lisensi.
- **Ringkasan**: 
  - Mengatasi masalah kinerja pada dasbor Daily Report (seperti bottleneck load matriks 30 detik).
  - Implementasi server-side pagination & live search pada Livewire.
  - Peralihan dependensi penting ke `nexaid-client`.
  - Penambahan alur kerja CI untuk audit lisensi SBOM (Software Bill of Materials).

## Mei 2026
- **Fokus Utama**: Peningkatan Framework UI (Filament v4), Form Templates, & Refactoring Arsitektur.
- **Ringkasan**:
  - Upgrade antarmuka secara besar-besaran dengan transisi Filament dari v3 ke v4.
  - Penyelesaian fungsionalitas validity window untuk template form.
  - Refactoring pola Repository pada pengelolaan Unit Kerja dan Layanan Pelaporan (Reporting).
  - Integrasi opsi backup basis data yang lebih canggih (`filament-backup` terbaru).

## April 2026
- **Fokus Utama**: Integrasi Sistem SSO, Sinkronisasi Unit Kerja, & Analisis Pelaporan.
- **Ringkasan**:
  - Pembaruan konfigurasi IAM dan optimalisasi pengalihan (redirect) login SSO.
  - Penambahan widget analisis rekomendasi untuk memperkuat wawasan pada laporan evaluasi kinerja.
  - Integrasi `manage-unit-kerja` versi mutakhir.

## Maret 2026
- **Fokus Utama**: Versioning Template Form, Manajemen Skema DB, & Fungsionalitas Ekspor.
- **Ringkasan**:
  - Peluncuran sistem versioning (pembuatan versi) untuk template form guna mendukung form yang dinamis.
  - Menambahkan fitur auto-complete (history suggestions) untuk form masukan.
  - Fitur sinkronisasi folder otomatis serta rilis skrip reset permission dan manajemen basis data (`wipe-db`).

## Februari 2026
- **Fokus Utama**: Analisis Pelaporan, PDF Export, & Tanda Tangan Digital (TTD).
- **Ringkasan**:
  - Rilis laporan unit kerja, laporan berbasis indikator/kategori, dan kapabilitas ekspor ke format PDF via Browsershot.
  - Implementasi komponen Footer Pelaporan yang memfasilitasi integrasi Tanda Tangan Digital (TTD) secara dinamis.
  - Penambahan job untuk komputasi harian.

## Januari 2026
- **Fokus Utama**: Inisialisasi Modul Laporan Harian (Daily Report) & Engine Form.
- **Ringkasan**:
  - Peluncuran versi awal halaman Daily Report Entry dan fitur validasi form secara dinamis.
  - Pembangunan dataset seed (simulasi laporan kepatuhan kebersihan tangan) dan integrasi awal reporting berbasis JSON.
