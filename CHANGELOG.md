# Changelog

Semua perubahan penting pada project ini dicatat di file ini.

Format mengikuti pola:
- **Added**: fitur baru
- **Changed**: perubahan fitur
- **Fixed**: perbaikan bug
- **Removed**: fitur yang dihapus
- **Security**: perubahan terkait keamanan
- **Deprecated**: fitur yang akan dihapus di versi mendatang

---

## [1.4.0] — 2026-06-13

### Added
- **Refactor Modular Monolith**: Migrasi arsitektur ke modular monolith dengan 7 modul independen (Authorization, Benchmarking, DailyReport, FormEngine, ImutMaster, Laporan, Reporting) menggunakan `nwidart/laravel-modules`.
- **Form Builder Engine**: Form template versioning, validity window, field config, compliance scoring, SyncFormTemplateDates command.
- **Daily Report Livewire**: Server-side pagination, search filter, matrix snapshot synchronization, polling interval reduction.
- **Repository Pattern**: Refactor Daily Report Services ke Repository Pattern dengan Unified Compliance Service.
- **Backup System**: Integrasi `juniyasyos/filament-backup` v3.0.1, backup configuration table, scheduler columns.
- **IAM/SSO**: Integrasi `nexaid-client`, IAM Application Switcher, LogoutController untuk SSO & local logout, session expiration fix.
- **Export Data**: Export JSON data Unit Kerja, command TestTtdUrlResolver.
- **Licensing**: Proprietary license, SBOM license audit workflow, third-party licenses documentation.
- **Performance Tests**: Phase 4 Benchmark and Consolidation Tests, compliance scoring optimization.
- **Database Index**: Composite index on `(form_template_id, report_date)` untuk `daily_report_responses`.

### Changed
- **Daily Report**: Auto-generation schedule dari monthly ke daily, matrix data loading enhancement, date navigation layout improvement.
- **Service Layer**: ImutReportService dipindah ke Reporting namespace, ImutSqlExpressionBuilder diganti dengan ImutCalculationService.
- **UI Components**: Refactor dashboard components untuk state management, loading indicators, date legend component untuk mobile.
- **Asset Management**: Old theme assets dihapus, manifest diperbarui, compressed CSS files.
- **Permissions**: Dynamic data permissions berdasarkan user authentication context.
- **ToggleIconColumn**: Diganti dengan IconColumn di ImutCategoryResourceTable dan ImutDataRelationManager.

### Fixed
- **Performance**: 30-second loading bottleneck di daily report dashboard (matrixData payload serialization dihapus).
- **Livewire**: Nested Alpine x-data causing Uncaught ReferenceError, reset pagination ke 1 saat selected date berubah.
- **UI**: Separate loading states untuk date nav dan main content, filter action di header list daily report.
- **Validation**: Indicator not found saat menggunakan filter search, disabled state logic di buildAnalysisSchemaForAction.
- **Dependencies**: Auth-bridge-client update ke nexaid-client, ToggleIconColumn dihapus.

### Removed
- FormBuilder dan ManageFormBuilder pages (digantikan FormTemplateVersionsRelationManager).
- ToggleIconColumn package dan referensi terkait.
- Exporter dan form classes yang tidak dipakai.
- Auth-bridge-client (digantikan nexaid-client).
- Cache files dan asset lama.

### Security
- Proprietary license enforcement.
- User authentication context untuk dynamic data permissions.

---

## [0.1.0] — 2026-06-13

### Added
- Inisialisasi dokumentasi project.
- Struktur dokumentasi `docs/` dengan panduan lengkap.
- README utama yang ringkas sebagai pintu masuk dokumentasi.
- CHANGELOG untuk tracking perubahan per versi.
- Panduan instalasi, konfigurasi, penggunaan, development, deployment.
- Dokumentasi struktur folder project.
- Aturan versioning (semantic versioning).
- Template release notes.
- Roadmap pengembangan project.
- Catatan teknis (NOTES.md).

### Changed
- README utama dirapikan — fokus sebagai pintu masuk, detail dipindah ke `docs/`.

### Fixed
- -

### Notes
- Versi awal dokumentasi. Belum ada perubahan logic aplikasi.
- Project masih dalam tahap active development.

---

## [0.0.0] — Pra-rilis

Project sebelum dokumentasi resmi. Semua perubahan sebelum versi 0.1.0 tidak dicatat secara terstruktur.
