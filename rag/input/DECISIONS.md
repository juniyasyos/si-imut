# Keputusan Teknis (Decisions)

Daftar keputusan teknis penting yang pernah diambil dalam pengembangan project SIIMUT,
lengkap dengan konteks, alasan, dan dampak.

---

## DEC-001: Migrasi ke Modular Monolith Architecture

- **ID**: DEC-001
- **Tanggal**: 2026-06-09
- **Status**: Implemented (v1.4.0)
- **Context**: Project SIIMUT awalnya adalah Laravel monolith dengan technical
  slicing — semua model di `App\Models`, semua service di `App\Services`, tanpa
  bounded context. Cross-module dependency tanpa batas, tidak ada interface contract,
  komunikasi antar fitur via method call langsung.
- **Decision**: Migrasi ke Modular Monolith architecture menggunakan package
  `nwidart/laravel-modules`. Aplikasi dipecah menjadi 7 modul independen:
  Authorization, Benchmarking, DailyReport, FormEngine, ImutMaster, Laporan,
  Reporting.
- **Reason**:
  - **Bounded Context** — setiap modul punya ruang lingkup jelas
  - **Encapsulation** — internal implementation disembunyikan; hanya interface publik
  - **Explicit Contracts** — komunikasi antar modul lewat interface/events
  - **Independent Deployability** — satu modul bisa diubah tanpa efek samping ke modul lain
  - **Tanpa overhead microservices** — masih satu deployment, satu database
- **Impact**:
  - ✅ Struktur kode lebih terorganisir per domain bisnis
  - ✅ Mudah bagi developer baru untuk memahami scope modul
  - ✅ Interface contracts memudahkan testing dan mocking
  - ⚠️ Migration effort signifikan (629 commits sejak v1.3.4)
  - ⚠️ Perlu disiplin tim untuk tidak melanggar batas modul

---

## DEC-002: Ganti `whereHas` dengan JOIN untuk Query Aggregasi

- **ID**: DEC-002
- **Tanggal**: 2026-06-10
- **Status**: Implemented (v1.4.0)
- **Context**: Dashboard Daily Report loading 30 detik. Profiling menemukan
  `whereHas()` di dalam query aggregasi `getComplianceSummaries()` menghasilkan
  correlated subquery — MySQL eksekusi query tambahan untuk setiap baris.
- **Decision**: Ganti `whereHas()` dengan `LEFT JOIN` langsung.
- **Reason**: `whereHas()` di Laravel menggunakan correlated subquery yang sangat
  lambat untuk dataset besar. JOIN langsung memberikan MySQL fleksibilitas optimizer
  dan menghilangkan N+1 di level database.
- **Impact**:
  - ✅ Query time turun drastis (15.000ms → ~500ms)
  - ✅ Dashboard bisa dipakai navigasi real-time
  - ⚠️ Perlu memastikan JOIN kondisi sesuai dengan logic subquery
  - ⚠️ Perlu composite index pendukung (lihat DEC-003)

---

## DEC-003: Composite Index pada `(form_template_id, report_date)`

- **ID**: DEC-003
- **Tanggal**: 2026-06-10
- **Status**: Implemented (v1.4.0)
- **Context**: Setelah fix `whereHas` → JOIN, query masih lambat karena MySQL
  melakukan full table scan. Tidak ada index untuk kombinasi `form_template_id`
  + `report_date` yang digunakan di GROUP BY dan WHERE.
- **Decision**: Tambah composite index pada kolom `(form_template_id, report_date)`
  di tabel `daily_report_responses`.
- **Reason**: Composite index memungkinkan MySQL mengoptimasi WHERE + GROUP BY
  dalam satu index scan. Tanpa index, MySQL harus scan semua baris.
- **Impact**:
  - ✅ Query time turun dari ~5.000ms menjadi < 50ms
  - ✅ Dashboard loading total < 2 detik (dengan DEC-002 + DEC-004)
  - ⚠️ Sedikit overhead write (index maintenance), tidak signifikan untuk workload harian

---

## DEC-004: Hapus Serialisasi Payload Matrix Data

- **ID**: DEC-004
- **Tanggal**: 2026-06-10
- **Status**: Implemented (v1.4.0)
- **Context**: Setelah fix query dan index, masih ada bottleneck — payload
  response dari endpoint matrix data sangat besar karena serialisasi data yang
  tidak perlu.
- **Decision**: Hapus serialisasi matrixData payload. Data dikirim dalam format
  mentah yang lebih ringan, serialisasi dilakukan di frontend.
- **Reason**: Data matrix sudah dalam bentuk array — serialisasi double tidak
  diperlukan dan membuang bandwidth. Frontend bisa handle format mentah lebih cepat.
- **Impact**:
  - ✅ Response size turun drastis
  - ✅ Frontend render lebih cepat
  - ✅ Loading dashboard dari 30 detik → < 2 detik (dengan DEC-002 + DEC-003)

---

## DEC-005: Gunakan `database` sebagai Session Driver

- **ID**: DEC-005
- **Tanggal**: 2026-06-01
- **Status**: Active
- **Context**: IAM/SSO client (`juniyasyos/nexaid-client`) membutuhkan session
  persistence untuk menyimpan state SSO. Driver session `file` atau `cookie`
  tidak mendukung fitur yang dibutuhkan client.
- **Decision**: Gunakan `SESSION_DRIVER=database` sebagai default.
  Redis sebagai alternatif untuk production skala besar (masih kompatibel).
- **Reason**: IAM client membutuhkan akses ke session data yang hanya bisa
  dijamin dengan database atau Redis. File session tidak reliable untuk SSO.
- **Impact**:
  - ✅ SSO login/logout berfungsi dengan baik
  - ✅ Session persistence untuk aplikasi multi-server (dengan database shared)
  - ⚠️ Performa sedikit lebih rendah dibanding file/cookie (tapi diabaikan untuk skala saat ini)
  - ⚠️ Jangan diganti ke `file` atau `cookie` selama IAM aktif

---

## DEC-006: Integrasi IAM/SSO dengan nexaid-client

- **ID**: DEC-006
- **Tanggal**: 2026-06-10
- **Status**: Implemented (v1.4.0)
- **Context**: Project membutuhkan Single Sign-On (SSO) untuk integrasi dengan
  sistem IAM rumah sakit. Sebelumnya menggunakan `auth-bridge-client` yang sudah
  tidak di-maintain.
- **Decision**: Migrasi dari `auth-bridge-client` ke `juniyasyos/nexaid-client`
  untuk integrasi IAM/SSO.
- **Reason**: `auth-bridge-client` deprecated dan tidak mendapat update keamanan.
  `nexaid-client` lebih aktif di-maintain, mendukung fitur SSO yang lebih lengkap
  (logout SSO, application switcher, session handling).
- **Impact**:
  - ✅ SSO lebih stabil dengan session expiration handling
  - ✅ Application Switcher Component untuk navigasi antar aplikasi
  - ✅ LogoutController menangani SSO dan local logout
  - ⚠️ Breaking change — semua komponen harus migrasi ke nexaid-client
  - ⚠️ Update `.env` dengan konfigurasi baru (IAM_BASE_URL, IAM_CLIENT_ID, IAM_CLIENT_SECRET)

---

## DEC-007: Auto-generation Laporan dari Monthly ke Daily

- **ID**: DEC-007
- **Tanggal**: 2026-06-10
- **Status**: Implemented (v1.4.0)
- **Context**: Laporan di-generate bulanan — data yang ditampilkan bisa basi
  (stale) karena user harus menunggu akhir bulan untuk melihat akumulasi.
- **Decision**: Ubah auto-generation schedule dari monthly ke daily.
- **Reason**: Kebutuhan user untuk melihat data harian yang up-to-date.
  Monthly generation terlalu lambat untuk monitoring mutu real-time.
- **Impact**:
  - ✅ Data lebih real-time — laporan selalu up-to-date
  - ⚠️ Beban server meningkat (perlu optimasi query — lihat DEC-002, DEC-003)

---

## DEC-008: Format Dokumentasi Bilingual

- **ID**: DEC-008
- **Tanggal**: 2026-06-13
- **Status**: Active
- **Context**: Dokumentasi project perlu ditulis dalam bahasa yang mudah
  dipahami oleh tim mutu rumah sakit (Indonesia) dan developer (bisa campuran).
- **Decision**: Dokumentasi teknis ditulis dalam Bahasa Indonesia. Kata teknis
  (service, database, deployment) boleh Inggris. File tertentu tetap Inggris
  (SBOM, LICENSE, technical audit docs).
- **Reason**: Mayoritas pengguna dan developer adalah penutur Bahasa Indonesia.
  Konsistensi bahasa dalam satu file lebih penting daripada memaksakan satu bahasa
  untuk semua file.
- **Impact**:
  - ✅ Dokumentasi lebih mudah dipahami tim non-teknis
  - ✅ Developer tetap bisa menggunakan istilah teknis standar
  - ⚠️ Perlu konsistensi — jangan campur ID/EN dalam paragraf yang sama

---

## DEC-009: GraphRAG Ringan Berbasis Python

- **ID**: DEC-009
- **Tanggal**: 2026-06-13
- **Status**: Implemented (v0.3.0)
- **Context**: Butuh sistem knowledge base untuk query cepat dari dokumentasi
  tanpa harus membaca puluhan file manual. Target: developer dan AI agents.
- **Decision**: Bangun POC GraphRAG ringan berbasis Python dengan dependency
  minimal (rich, python-dotenv, anthropic). Pattern-based graph extraction,
  keyword scoring, LLM opsional.
- **Reason**:
  - **Ringan** — 3 dependencies Python, tanpa Docker/Neo4j
  - **File-based** — output JSON, mudah dibaca tools lain
  - **LLM opsional** — tetap jalan tanpa API key
  - **Fokus docs** — tidak scan source code, aman untuk POC
- **Impact**:
  - ✅ Query dokumentasi jadi cepat — cukup `python3 rag/scripts/query.py`
  - ✅ Bisa diintegrasikan dengan AI agents untuk context retrieval
  - ⚠️ Pattern-based extraction — terbatas pada kata kunci eksplisit
  - ⚠️ Source code tidak diindeks (belum)

---

## RAG Metadata

## DEC-001 - Migrasi ke Modular Monolith Architecture

Type: Decision
Status: Implemented
Area: Arsitektur
Related Modules:
- MOD-001
- MOD-002
- MOD-003
- MOD-004
- MOD-005
Related Services:
- Needs Verification
Related Issues:
- Needs Verification
Source:
- commit: 57291cf

Summary:
Keputusan arsitektur besar untuk bermigrasi dari Laravel Monolith konvensional menuju Modular Monolith dengan memecah fitur menjadi modul terpisah seperti Authorization, DailyReport, Laporan, FormEngine, dll.
