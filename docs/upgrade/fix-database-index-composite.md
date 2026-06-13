# 📄 Fix Database Index: Optimasi Query Aggregasi di Laravel

> **Konteks**: Studi kasus dari project SI-IMUT.  
> Bagian dari rangkaian fix **30 detik → < 2 detik**.  
> Fix #2 dari 3: Menambahkan composite index yang tepat pada tabel `daily_report_responses`.

---

## 🔴 Masalah: Full Table Scan di Setiap Query

Setelah Fix #1 (mengganti `whereHas` dengan JOIN), query kita sudah lebih efisien. Tapi tanpa index yang tepat, MySQL tetap harus **scan seluruh tabel** untuk menemukan data yang cocok — seperti mencari kata di buku tanpa daftar isi.

**Hasil investigasi awal (sebelum fix):**

```sql
SHOW INDEX FROM daily_report_responses;
-- Hanya ada:
-- PRIMARY         → id
-- unit_kerja_id_report_date_index → unit_kerja_id, report_date
-- form_template_id_foreign → form_template_id (hanya FK, bukan composite)
```

Query `getComplianceSummaries()` memfilter berdasarkan `form_template_id` + `report_date` + `GROUP BY` keduanya — tapi tidak ada composite index untuk kombinasi tersebut. Akibatnya: **full table scan** setiap kali query dieksekusi.

**Estimasi dampak:**

```
Tanpa composite index:
  Rows di daily_report_responses (1 bulan)  : ~3.000 rows
  MySQL harus scan                           : SEMUA rows untuk setiap filter
  Waktu per scan                             : ~50ms
  
  Amplified oleh N+1 dari whereHas (Fix #1): 50ms × 1.000 subqueries = 50.000ms
  Bahkan setelah Fix #1 (tanpa index)       : ~5.000ms untuk GROUP BY + JOIN
```

---

## 🔍 Analisis: Index Apa yang Dibutuhkan?

Query utama di `getComplianceSummaries()`:

```sql
SELECT
    form_templates.id as form_template_id,
    DATE(daily_report_responses.report_date) as report_date,
    COUNT(*) as total_count,
    SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count
FROM daily_report_responses
    JOIN form_templates ON daily_report_responses.form_template_id = form_templates.id
    JOIN imut_profil    ON form_templates.imut_profile_id = imut_profil.id
WHERE
    imut_profil.valid_from <= NOW()                              -- ← perlu index di imut_profil
    AND (imut_profil.valid_until IS NULL OR valid_until >= NOW())-- ← perlu index di imut_profil
    AND daily_report_responses.unit_kerja_id IN (1, 2, 3)       -- ← perlu index
    AND daily_report_responses.report_date BETWEEN '...' AND '...' -- ← perlu index
GROUP BY
    form_templates.id,
    DATE(daily_report_responses.report_date)                    -- ← perlu composite index
```

**Mapping filter → index yang dibutuhkan:**

| Filter / JOIN | Kolom | Index yang Dibutuhkan |
|---|---|---|
| JOIN ke form_templates | `form_template_id` | FK index (sudah ada) |
| GROUP BY + filter date | `form_template_id, report_date` | **Composite index (BARU)** |
| WHERE unit_kerja_id | `unit_kerja_id, report_date` | Sudah ada |
| WHERE valid_from | `valid_from, valid_until` | Perlu di imut_profil |
| JOIN ke imut_profil | `imut_profile_id` di form_templates | FK + index (sudah ada) |

---

## ✅ Solusi: Migration Menambah Composite Index

```php
<?php
// database/migrations/2026_06_05_134500_add_index_form_template_report_date_daily_report_responses.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PHASE 2 OPTIMIZATION: Add composite index on (form_template_id, report_date)
     *
     * Purpose: Optimize GROUP BY queries in MatrixDataService::getComplianceSummaries()
     * which joins form_templates and filters/groups by report_date.
     *
     * Impact: Reduces GROUP BY scan from full table scan → indexed range scan
     * Speedup: ~5.000ms → 50-100ms (50-100× faster)
     */
    public function up(): void
    {
        Schema::table('daily_report_responses', function (Blueprint $table) {
            // Guard: hanya tambah jika belum ada (idempotent)
            if (! $this->indexExists('daily_report_responses', 'idx_form_template_report_date')) {
                $table->index(['form_template_id', 'report_date'], 'idx_form_template_report_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_report_responses', function (Blueprint $table) {
            $table->dropIndex('idx_form_template_report_date');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::connection(null)->getConnection();
        $prefix = $connection->getTablePrefix();
        $indexes = $connection->select(
            "SHOW INDEXES FROM `{$prefix}{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return ! empty($indexes);
    }
};
```

---

## 📊 State Index Setelah Semua Fix Dijalankan

Hasil `SHOW INDEX` setelah migration berjalan:

```
=== daily_report_responses ===
PRIMARY                                    [1] → id
submitted_by_foreign                       [1] → submitted_by
unit_kerja_id_report_date_index            [1] → unit_kerja_id
unit_kerja_id_report_date_index            [2] → report_date
form_template_id_foreign                   [1] → form_template_id
validated_by_foreign                       [1] → validated_by
idx_form_template_report_date ✨ (BARU)   [1] → form_template_id
idx_form_template_report_date ✨ (BARU)   [2] → report_date

=== imut_profil ===
PRIMARY                                    [1] → id
imut_profil_imut_data_id_index             [1] → imut_data_id
idx_imut_profil_data_id                    [1] → imut_data_id
idx_imut_profil_data_version               [1] → imut_data_id
idx_imut_profil_data_version               [2] → version
idx_validity_period ✅                    [1] → valid_from
idx_validity_period ✅                    [2] → valid_until
idx_data_validity                          [1] → imut_data_id
idx_data_validity                          [2] → valid_from

=== form_templates ===
PRIMARY                                    [1] → id
unique_profile_version                     [1] → imut_profile_id
unique_profile_version                     [2] → version
idx_form_templates_imut_profile_id ✅     [1] → imut_profile_id
idx_profile_active                         [1] → imut_profile_id
idx_profile_active                         [2] → is_active
```

---

## ⚡ Kenapa Composite Index `(form_template_id, report_date)` Penting?

**Tanpa composite index:**
```
GROUP BY form_templates.id, DATE(report_date)
  → MySQL harus:
    1. Scan semua 3.000 baris
    2. Sort berdasarkan form_template_id
    3. Kemudian sort lagi berdasarkan report_date
    4. Baru bisa GROUP BY
  → Waktu: ~5.000ms untuk data besar
```

**Dengan composite index:**
```
GROUP BY form_templates.id, DATE(report_date)
  → MySQL bisa:
    1. Langsung menggunakan index untuk lookup form_template_id
    2. report_date sudah terurut di dalam index (pre-sorted!)
    3. GROUP BY langsung bisa pakai index scan, bukan filesort
  → Waktu: ~50-100ms (50-100× lebih cepat)
```

**Prinsip kuncinya:**

> Urutan kolom dalam composite index harus **mengikuti urutan** filter `WHERE` dan `GROUP BY` yang paling sering dipakai.

```
index(form_template_id, report_date) optimal untuk:
  WHERE form_template_id = ?          ✅ (leftmost prefix)
  WHERE form_template_id = ? AND report_date BETWEEN ? ✅ (full index)
  GROUP BY form_template_id, report_date ✅ (pre-sorted, no filesort)
  
  WHERE report_date = ? (tanpa form_template_id) ❌ (tidak bisa pakai index ini)
```

---

## 🧪 Cara Verifikasi Index Digunakan

**1. Cek via EXPLAIN:**

```sql
EXPLAIN FORMAT=TRADITIONAL
SELECT
    form_templates.id as form_template_id,
    DATE(daily_report_responses.report_date) as report_date,
    COUNT(*) as total_count
FROM daily_report_responses
    JOIN form_templates ON daily_report_responses.form_template_id = form_templates.id
    JOIN imut_profil    ON form_templates.imut_profile_id = imut_profil.id
WHERE imut_profil.valid_from <= NOW()
  AND daily_report_responses.unit_kerja_id IN (1, 2)
  AND daily_report_responses.report_date BETWEEN '2026-06-01' AND '2026-06-30'
GROUP BY form_templates.id, DATE(daily_report_responses.report_date);
```

**Output yang diharapkan (setelah index):**

```
table                  | type  | key                           | Extra
daily_report_responses | range | idx_form_template_report_date | Using index condition
form_templates         | eq_ref| PRIMARY                       |
imut_profil            | eq_ref| PRIMARY                       | Using index condition
```

> **Yang perlu diperhatikan:**
> - `type`: `range` atau `ref` (bukan `ALL` = full scan)
> - `key`: nama index yang dipakai (bukan `NULL`)
> - `Extra`: tidak ada `Using filesort` untuk GROUP BY

**2. Cek via Laravel Tinker:**

```php
DB::enableQueryLog();
app(\App\Services\DailyReport\MatrixDataService::class)
    ->loadMatrixCompletely('2026-06');
$log = DB::getQueryLog();
echo count($log); // harusnya: 3 (bukan 1000+)
```

---

## 💡 Panduan: Kapan Perlu Composite Index?

**✅ Tambahkan composite index ketika:**
- Ada query dengan `WHERE col1 = ? AND col2 BETWEEN ?`
- Ada `GROUP BY col1, col2` di query yang sering dieksekusi
- Ada `ORDER BY col1, col2` di halaman yang diakses banyak user
- Ada JOIN + filter di tabel yang datanya terus bertambah

**❌ Jangan sembarangan tambah index karena:**
- Setiap index memperlambat `INSERT`, `UPDATE`, `DELETE`
- Index memakan storage tambahan
- Terlalu banyak index bisa membingungkan query optimizer

**Rule of thumb:**
> Tambah index berdasarkan **query nyata yang lambat** (diukur dengan EXPLAIN atau slow query log), bukan berdasarkan prediksi.

---

## 📊 Ringkasan Dampak Fix #2

| Metrik | Sebelum | Sesudah | Improvement |
|--------|---------|---------|-------------|
| **Scan type** | Full table scan | Range/Index scan | Index dipakai |
| **GROUP BY** | Filesort (lambat) | Index scan (cepat) | No filesort |
| **Waktu GROUP BY** | ~5.000ms | ~50–100ms | **50–100× lebih cepat** |
| **Storage overhead** | - | ~kecil | Dapat diabaikan |

---

## 🔗 Hubungan dengan Fix #1 dan Fix #3

```
Fix #1 (whereHas → JOIN)    : 15.000ms → 150ms  [Query structure]
Fix #2 (Database Index)     :  5.000ms → 100ms  [Index coverage]  ← KITA DI SINI
Fix #3 (Pagination/Virtual) :  3.000ms → 200ms  [Data transfer]   [Coming next]
────────────────────────────────────────────────────────────────
Total combined improvement  : 30.000ms → ~500ms  [~60× lebih cepat]
```

---

## 📝 Draft LinkedIn Post

```
Database indexes itu seperti daftar isi buku.

Tanpa daftar isi: buka halaman satu per satu sampai ketemu.
Dengan daftar isi: langsung lompat ke halaman yang tepat.

---

Saat optimasi dashboard yang loading 30 detik, saya menemukan
query GROUP BY yang tidak punya index untuk kombinasi kolom
yang difilter.

MySQL terpaksa melakukan full table scan → sort → group.
Untuk 3.000 baris: ~5.000ms hanya untuk operasi ini.

Solusinya: tambah 1 composite index.

❌ Sebelum (index tidak ada):
SHOW INDEX FROM daily_report_responses;
→ Hanya ada: (unit_kerja_id, report_date)
→ Tidak ada: (form_template_id, report_date)  ← yang dipakai GROUP BY!

✅ Sesudah (tambah composite index):
$table->index(['form_template_id', 'report_date'], 'idx_form_template_report_date');

---

Hasilnya:
• Full table scan → Range/Index scan
• GROUP BY filesort dihilangkan (pre-sorted dari index)
• 5.000ms → 50–100ms (50-100× lebih cepat)

---

3 hal penting tentang database index:

1. Urutan kolom dalam composite index SANGAT penting
   → index(a, b) optimal untuk WHERE a=? AND b=?
   → tapi tidak untuk WHERE b=? saja (tanpa a)

2. Selalu verify dengan EXPLAIN sebelum dan sesudah
   → pastikan type bukan "ALL" (full scan)
   → pastikan Extra tidak ada "Using filesort"

3. Jangan asal tambah index
   → index memperlambat INSERT/UPDATE/DELETE
   → tambah hanya berdasarkan query nyata yang lambat

Ini adalah bagian dari rangkaian fix yang mengubah loading
30 detik menjadi < 2 detik. Next: pagination dan virtual rendering.

#Laravel #MySQL #DatabaseOptimization #PerformanceOptimization
#BackendDevelopment #PHP #WebDev #Programming
```

---

*Studi kasus dari project SI-IMUT | Fix #2 dari 3 | Juni 2026*  
*Migration: `database/migrations/2026_06_05_134500_add_index_form_template_report_date_daily_report_responses.php`*
