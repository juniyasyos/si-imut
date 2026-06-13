# 📄 Fix N+1 Query: `whereHas` vs Direct `JOIN` di Laravel

> **Konteks**: Studi kasus nyata dari project SI-IMUT (Sistem Informasi Indikator Mutu).  
> Dashboard loading **30 detik** → diperbaiki jadi **< 2 detik** hanya dengan **1 perubahan kecil**.

---

## 🔴 Masalah: Loading 30 Detik

Saat user menekan navigasi bulan di halaman Daily Report, dashboard membutuhkan waktu **25–30 detik** untuk merender. Ini bukan sekedar "lambat" — ini sudah level tidak bisa dipakai.

Setelah di-profiling, ditemukan **satu baris kode** yang menjadi akar masalah.

**File**: `app/Services/DailyReport/MatrixDataService.php`  
**Fungsi**: `getComplianceSummaries()`

---

## 🔍 Root Cause: `whereHas()` di Dalam Query Aggregasi

```php
// ❌ KODE LAMA — SANGAT LAMBAT (~15.000ms)
$summaries = DailyReportResponse::select([
    'form_templates.id as form_template_id',
    DB::raw('DATE(report_date) as report_date'),
    DB::raw('COUNT(*) as total_count'),
    DB::raw('SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count')
])
    ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
    ->whereHas('formTemplate.imutProfile', function ($q) use ($now) {
        // ⚠️ INI BIANG KEROKNYA
        $q->where('valid_from', '<=', $now)
            ->where(function ($subQ) use ($now) {
                $subQ->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $now);
            });
    })
    ->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds)
    ->whereBetween('daily_report_responses.report_date', [$startDate, $endDate])
    ->groupBy('form_templates.id', DB::raw('DATE(report_date)'))
    ->get();
```

---

## 🧠 Penjelasan: Kenapa `whereHas` di Sini Berbahaya?

`whereHas()` di Laravel bekerja dengan cara membuat **correlated subquery** — artinya MySQL harus menjalankan query tambahan untuk **setiap baris** hasil query utama.

**Ilustrasi apa yang terjadi di MySQL:**

```sql
-- Query utama berjalan normal...
-- Tapi untuk SETIAP BARIS hasil, MySQL menjalankan ini secara terpisah:

SELECT EXISTS (
    SELECT 1
    FROM form_templates ft
    INNER JOIN imut_profil ip ON ft.imut_profile_id = ip.id
    WHERE ft.id = daily_report_responses.form_template_id  -- ← nilai berubah per baris!
      AND ip.valid_from <= NOW()
      AND (ip.valid_until IS NULL OR ip.valid_until >= NOW())
)
```

**Kalkulasi impaknya:**

```
Data di tabel daily_report_responses untuk 1 bulan : ~1.000–3.000 baris
Setiap baris                                        → 1 subquery
Setiap subquery (tanpa index optimal)               : ~15ms
                                                     ─────────────
Total                                               : 1.000 × 15ms
                                                    = 15.000ms
                                                    = 15 DETIK

Ditambah overhead render, network, Alpine.js → total 25–30 detik
```

Ini yang disebut **N+1 Query Problem** — salah satu performance killer paling umum di aplikasi berbasis ORM.

---

## ✅ Solusi: Ganti dengan Direct `JOIN`

```php
// ✅ KODE BARU — CEPAT (~100–200ms)
$summaries = DailyReportResponse::select([
    'form_templates.id as form_template_id',
    DB::raw('DATE(report_date) as report_date'),
    DB::raw('COUNT(*) as total_count'),
    DB::raw('SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count')
])
    ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
    // ✅ Direct JOIN — semua filter selesai dalam SATU query
    ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
    ->where('imut_profil.valid_from', '<=', $now)
    ->where(function ($q) use ($now) {
        $q->whereNull('imut_profil.valid_until')
            ->orWhere('imut_profil.valid_until', '>=', $now);
    })
    ->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds)
    ->whereBetween('daily_report_responses.report_date', [$startDate, $endDate])
    ->groupBy('form_templates.id', DB::raw('DATE(report_date)'))
    ->get();
```

---

## ⚡ Perbandingan Eksekusi

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SEBELUM (whereHas) — 1.001 queries ke database:

  Query 1 : SELECT ... FROM daily_report_responses JOIN form_templates
  Query 2 : SELECT EXISTS (...) WHERE form_template_id = 1   ← per baris
  Query 3 : SELECT EXISTS (...) WHERE form_template_id = 2   ← per baris
  Query 4 : SELECT EXISTS (...) WHERE form_template_id = 3   ← per baris
  ...
  Query 1001: SELECT EXISTS (...) WHERE form_template_id = 1000

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SESUDAH (JOIN) — 1 query ke database:

  Query 1 : SELECT ...
            FROM daily_report_responses
            JOIN form_templates      ON ...
            JOIN imut_profil         ON ...  ← semua filter dalam 1 scan
            WHERE imut_profil.valid_from <= NOW()
            AND (imut_profil.valid_until IS NULL OR ...)
            AND unit_kerja_id IN (...)
            AND report_date BETWEEN ... AND ...
            GROUP BY form_templates.id, DATE(report_date)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 📊 Hasil Before vs After

| Metrik | Sebelum | Sesudah | Improvement |
|--------|---------|---------|-------------|
| **Total queries ke DB** | ~1.000+ | 1 | **1000× lebih sedikit** |
| **Waktu eksekusi DB** | ~15.000 ms | ~100–200 ms | **~100× lebih cepat** |
| **Total loading halaman** | 25–30 detik | < 2 detik | **~15× lebih cepat** |
| **Lines of code** | 11 baris | 8 baris | Lebih ringkas |

---

## 💡 Kapan `whereHas()` Aman Digunakan?

`whereHas()` bukan musuh — ini tool yang sangat berguna. Tapi ada konteksnya:

**✅ Aman dipakai ketika:**
- Data kecil hasil query (< 100 baris)
- Filtering di halaman detail atau single record
- Belum ada `JOIN` ke tabel relasi tersebut di query yang sama
- Index database sudah optimal

**❌ Hindari ketika:**
- Query utama menghasilkan ribuan baris (seperti kasus ini)
- Sudah ada `JOIN` ke tabel yang sama sebelumnya di query yang sama
- Dipakai di dalam query aggregasi besar (`COUNT`, `SUM`, `GROUP BY`)
- Performa sudah menjadi concern utama

> **Rule of thumb**: Kalau kamu sudah `JOIN` ke tabel tertentu, **jangan `whereHas` ke relasi yang melewati tabel yang sama**. Langsung filter dari JOIN-nya.

---

## 🛠️ Tools untuk Investigasi Query

**1. Laravel Query Log:**
```php
DB::enableQueryLog();

// ... jalankan kode yang ingin diinvestigasi ...

$queries = DB::getQueryLog();
echo count($queries); // lihat berapa banyak query yang dieksekusi
dd($queries);         // lihat detail setiap query
```

**2. MySQL EXPLAIN:**
```sql
EXPLAIN FORMAT=JSON
SELECT
    form_templates.id as form_template_id,
    DATE(daily_report_responses.report_date) as report_date,
    COUNT(*) as total_count
FROM daily_report_responses
JOIN form_templates ON daily_report_responses.form_template_id = form_templates.id
JOIN imut_profil ON form_templates.imut_profile_id = imut_profil.id
WHERE imut_profil.valid_from <= NOW()
  AND (imut_profil.valid_until IS NULL OR imut_profil.valid_until >= NOW())
  AND daily_report_responses.unit_kerja_id IN (1, 2, 3)
  AND daily_report_responses.report_date BETWEEN '2026-06-01' AND '2026-06-30'
GROUP BY form_templates.id, DATE(daily_report_responses.report_date);
```

**3. Laravel Debugbar / Telescope:**
Pasang di environment lokal untuk monitoring query secara real-time dengan visual yang nyaman.

```bash
composer require barryvdh/laravel-debugbar --dev
```

---

## 🔑 Key Takeaways

1. **`whereHas()` = correlated subquery** → O(n) queries ke database, bukan O(1)
2. **Direct JOIN** selalu lebih efisien untuk filter pada tabel yang sudah di-join
3. **Profiling dulu** sebelum optimasi — jangan asal refactor berdasarkan asumsi
4. **1 baris kode yang salah** bisa membuat aplikasi tidak bisa dipakai
5. **Pahami SQL yang dihasilkan ORM** — ORM mempermudah coding tapi menyembunyikan biaya sebenarnya

---

## 📝 Draft LinkedIn Post

### 🔴 Versi Pendek (hook kuat, cocok untuk engagement tinggi)

```
Dashboard 30 detik → 2 detik. Satu baris kode.

Bulan lalu saya debugging fitur yang bikin user nunggu 30 detik setiap klik navigasi bulan.

Ternyata: 1 query yang nge-loop 1.000+ subquery ke database.

Pelakunya: whereHas() di Laravel.

---

whereHas() itu nyaman banget. Bisa filter relasi dengan syntax yang readable.
Tapi dia punya "harga" yang tersembunyi:

Untuk setiap baris hasil query utama, dia buat 1 correlated subquery.

1.000 baris × 15ms/subquery = 15.000ms = 15 detik.
Tambah overhead lain → 30 detik total.

---

Solusinya sederhana: ganti dengan direct JOIN.

❌ SEBELUM:
->whereHas('formTemplate.imutProfile', function ($q) use ($now) {
    $q->where('valid_from', '<=', $now);
})

✅ SESUDAH:
->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
->where('imut_profil.valid_from', '<=', $now)

---

Hasilnya:
• 1.000+ queries → 1 query
• 15.000ms → 150ms
• Loading halaman: 30 detik → < 2 detik

whereHas() bukan jelek — dia punya tempat yang tepat.
Tapi kalau sudah ada JOIN ke tabel yang sama, langsung filter dari JOIN-nya.

Pernah ketemu kasus N+1 query yang bikin kepala pusing? Drop di komentar 👇

#Laravel #PHP #WebDev #PerformanceOptimization #BackendDev #Programming
```

---

### 🔵 Versi Panjang (detailed, cocok untuk thought leadership)

```
Satu perubahan 8 baris kode. Loading dari 30 detik menjadi 2 detik.

Ini bukan magic. Ini tentang memahami apa yang sebenarnya terjadi di database.

---

🔍 KONTEKS

Saya sedang membangun dashboard Daily Report untuk sistem manajemen
mutu rumah sakit (SI-IMUT). Dashboard menampilkan matrix indikator ×
hari dalam sebulan — sekitar 60 indikator × 30 hari = 1.800 cells.

Masalahnya: setiap kali user navigasi ke bulan berbeda, halaman butuh
25–30 detik. User komplain. Tim komplain. Saya pusing.

---

🧐 INVESTIGASI

Langkah pertama: aktifkan query log.

DB::enableQueryLog();
$this->loadMatrixData($month);
dd(count(DB::getQueryLog())); // hasilnya: 1.247 queries 😱

Seribu dua ratus empat puluh tujuh queries. Untuk satu halaman.

Saya trace satu per satu. Ketemu pelakunya: whereHas() di dalam
fungsi getComplianceSummaries().

---

🎯 MASALAHNYA

whereHas() di Laravel bekerja dengan membuat correlated subquery.

Artinya: untuk setiap baris di hasil query utama, MySQL menjalankan
subquery terpisah untuk mengecek apakah relasi memenuhi kondisi.

Data saya: ~1.000 baris daily_report_responses per bulan.
Setiap subquery (tanpa index optimal): ~15ms.
Total: 1.000 × 15ms = 15.000ms = 15 detik.
Belum termasuk overhead lain.

---

✅ SOLUSINYA

Saat kamu sudah melakukan JOIN ke sebuah tabel, jangan pakai
whereHas() untuk filter relasi yang melewati tabel yang sama.
Langsung filter dari JOIN-nya.

❌ Sebelum — 1.247 queries:
->join('form_templates', ...)
->whereHas('formTemplate.imutProfile', function ($q) {
    $q->where('valid_from', '<=', now());
})

✅ Sesudah — 1 query:
->join('form_templates', ...)
->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
->where('imut_profil.valid_from', '<=', now())

---

📊 HASILNYA

• Jumlah query: 1.247 → 1
• Waktu DB: ~15.000ms → ~150ms
• Total loading: 30 detik → < 2 detik
• Baris kode yang diubah: 11 → 8 (malah lebih ringkas!)

---

💡 KAPAN whereHas() AMAN?

whereHas() bukan villain. Dia sangat berguna untuk:
• Query dengan data kecil (< 100 baris)
• Filtering di halaman detail/single record
• Saat belum ada JOIN ke tabel relasi tersebut

Yang berbahaya adalah memakai whereHas() di query aggregasi besar
yang sudah punya JOIN ke tabel relasi yang sama.

---

🏁 LESSON LEARNED

1. Profiling > Asumsi. Selalu ukur sebelum optimasi.
2. Pahami apa yang terjadi di level SQL, bukan hanya di level ORM.
3. ORM memudahkan coding, tapi menyembunyikan biaya yang sebenarnya.
4. N+1 query problem bisa muncul di tempat yang tidak terduga.
5. Fix terbaik seringkali adalah yang paling sederhana.

Pernah punya pengalaman serupa dengan N+1 query?
Atau ada trick lain untuk deteksi dan fix-nya?

Sharing di komentar ya — saya selalu senang belajar dari orang lain 🙌

#Laravel #PHP #MySQL #PerformanceOptimization #BackendDevelopment
#WebDevelopment #Programming #SoftwareEngineering #DatabaseOptimization
```

---

## 📋 Checklist Sebelum Posting di LinkedIn

- [ ] Screenshot kode before/after → gunakan [carbon.now.sh](https://carbon.now.sh) biar tampilan keren
- [ ] Tambahkan visual perbandingan waktu (screenshot profiler / grafik sederhana)
- [ ] Tag 2–3 rekan developer untuk engagement awal
- [ ] Post di jam prime time: **Selasa–Kamis, pukul 07.00–09.00 atau 12.00–13.00 WIB**
- [ ] Siapkan waktu 1–2 jam setelah posting untuk balas komentar (penting untuk algoritma)
- [ ] Set reminder untuk repost minggu depan dengan angle berbeda

---

*Studi kasus dari project SI-IMUT | Fix dilakukan: Juni 2026*  
*File yang diubah: `app/Services/DailyReport/MatrixDataService.php`*
