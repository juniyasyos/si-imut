# Daftar Module

Dokumen ini mendeskripsikan domain atau modul bisnis utama pada project SI-IMUT. Informasi ini diproses oleh sistem RAG.

## MOD-001 - Daily Report

Type: Module
Status: Active
Area: Daily Report
Related Services:
- SVC-001
- Needs Verification
Related Commands:
- CMD-001
Related Issues:
- KI-001
Related Decisions:
- DEC-001
Source:
- app/Modules/DailyReport
- commit: 24e3b1a

Summary:
Modul ini bertanggung jawab untuk pencatatan entri laporan harian, pemuatan matriks, validasi tenggat waktu (back data entry duration), serta paginasi server-side.

---

## MOD-002 - Form Engine

Type: Module
Status: Active
Area: Form Template
Related Services:
- Needs Verification
Related Commands:
- Needs Verification
Related Issues:
- Needs Verification
Related Decisions:
- Needs Verification
Source:
- app/Modules/FormEngine
- commit: 4bb5bd7

Summary:
Modul inti untuk pembuatan form yang dinamis (FormSchemaBuilder), mencakup pengelolaan versi template (template versioning) dan penentuan jangka waktu (validity window) berlakunya template tersebut.

---

## MOD-003 - Reporting & Analytics

Type: Module
Status: Active
Area: Reporting
Related Services:
- Needs Verification
Related Commands:
- Needs Verification
Related Issues:
- KI-002
Related Decisions:
- Needs Verification
Source:
- app/Modules/Reporting
- commit: 6f86712

Summary:
Sistem pelaporan agregat (Laporan Unit Kerja, Laporan Kategori/Indikator) yang menyertakan fungsionalitas ekspor PDF, filter bulan aktif, kalkulasi skor kepatuhan, dan integrasi benchmarking.

---

## MOD-004 - Authorization & IAM

Type: Module
Status: Active
Area: Authentication
Related Services:
- Needs Verification
Related Commands:
- Needs Verification
Related Issues:
- Needs Verification
Related Decisions:
- Needs Verification
Source:
- app/Modules/Auth
- commit: 1062390

Summary:
Modul manajemen pengguna yang menaungi fungsionalitas Single Sign-On (SSO), RolePermissionManager, sinkronisasi unit kerja (Unit Kerja Sync), serta peralihan klien (nexaid-client).

---

## MOD-005 - Core & UI Framework

Type: Module
Status: Active
Area: Core Application
Related Services:
- Needs Verification
Related Commands:
- Needs Verification
Related Issues:
- Needs Verification
Related Decisions:
- Needs Verification
Source:
- app/Providers
- commit: 84565d6

Summary:
Fondasi antarmuka dan penyedia aplikasi yang melayani pengelolaan tema Filament, custom widget dasbor (Filament v4), manajemen caching aset, hingga layanan integrasi pihak ketiga.
