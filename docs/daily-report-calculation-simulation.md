# Simulasi Perhitungan Daily Report ke IMUT Penilaian

## 📊 Konsep Dasar

Sistem ini menghitung **Numerator** dan **Denominator** untuk setiap indikator IMUT berdasarkan data Daily Report yang sudah diinput oleh unit kerja.

### Logika Perhitungan

```
Numerator (N)    = Jumlah laporan dengan compliance 100%
Denominator (D)  = Total jumlah laporan yang diinput
Persentase (%)   = (N/D) × 100
```

## 🎯 Cara Kerja Step-by-Step

### Step 1: Identifikasi Data
Sistem mengambil:
- **Periode Penilaian**: Dari `assessment_period_start` sampai `assessment_period_end`
- **Unit Kerja**: Unit kerja yang sedang dinilai
- **Indikator**: Form template yang terkait dengan IMUT Profile

### Step 2: Query Daily Reports
Mencari semua daily report entries dengan kriteria:
- Unit kerja yang sesuai
- Form template (indikator) yang sesuai
- Tanggal dalam rentang periode penilaian

### Step 3: Hitung Compliance Setiap Hari
Untuk setiap daily report:
1. Ambil `responses` (jawaban dari form)
2. Hitung compliance score menggunakan `FormTemplate->calculateCompliance()`
3. Cek apakah score = 100% (perfect)
4. Jika 100% → masuk hitungan Numerator

### Step 4: Aggregasi Hasil
- **Denominator** = Total jumlah laporan yang diinput
- **Numerator** = Total jumlah laporan dengan compliance 100%
- **Percentage** = (Numerator / Denominator) × 100

---

## 📝 Simulasi Kasus 1: Ideal Case

### Data Laporan IMUT
```
Nama Laporan: Laporan IMUT Januari 2025
Periode     : 1 Januari 2025 - 31 Januari 2025
Total Hari  : 31 hari
```

### Indikator: "Hand Hygiene Compliance"
**Unit Kerja: ICU**

### Data Daily Reports (10 hari terisi)

| Tanggal    | Compliance Score | Perfect (100%)? |
|------------|------------------|-----------------|
| 2025-01-01 | 100%            | ✅ Ya          |
| 2025-01-02 | 95%             | ❌ Tidak       |
| 2025-01-05 | 100%            | ✅ Ya          |
| 2025-01-08 | 100%            | ✅ Ya          |
| 2025-01-10 | 88%             | ❌ Tidak       |
| 2025-01-15 | 100%            | ✅ Ya          |
| 2025-01-18 | 92%             | ❌ Tidak       |
| 2025-01-22 | 100%            | ✅ Ya          |
| 2025-01-25 | 100%            | ✅ Ya          |
| 2025-01-30 | 100%            | ✅ Ya          |

### Hasil Perhitungan
```
Denominator (D) = 10 laporan (total inputan selama periode)
Numerator (N)   = 7 laporan (yang compliance 100%)
Percentage (%)  = (7/10) × 100 = 70%
```

### Data yang Tersimpan di `imut_penilaian`
```php
[
    'numerator_value' => 7,
    'denominator_value' => 10,
    'is_auto_calculated' => true,
    'calculation_metadata' => [
        'calculated_at' => '2025-02-01 10:30:00',
        'total_days_in_period' => 31,
        'reports_submitted' => 10,
        'reports_perfect' => 7,
        'missing_dates' => [
            '2025-01-03', '2025-01-04', '2025-01-06', '2025-01-07',
            '2025-01-09', '2025-01-11', '2025-01-12', '2025-01-13',
            '2025-01-14', '2025-01-16', '2025-01-17', '2025-01-19',
            '2025-01-20', '2025-01-21', '2025-01-23', '2025-01-24',
            '2025-01-26', '2025-01-27', '2025-01-28', '2025-01-29',
            '2025-01-31'
        ],
        'compliance_breakdown' => [
            ['date' => '2025-01-01', 'compliance_score' => 100.0, 'is_perfect' => true],
            ['date' => '2025-01-02', 'compliance_score' => 95.0, 'is_perfect' => false],
            ['date' => '2025-01-05', 'compliance_score' => 100.0, 'is_perfect' => true],
            // ... dst
        ],
        'form_template_id' => 123,
        'form_template_title' => 'Hand Hygiene Compliance'
    ]
]
```

---

## 📝 Simulasi Kasus 2: Perfect Compliance

### Indikator: "Medication Safety Checklist"
**Unit Kerja: Pharmacy**

### Data Daily Reports (Semua 100%)

| Tanggal    | Compliance Score | Perfect (100%)? |
|------------|------------------|-----------------|
| 2025-01-01 | 100%            | ✅ Ya          |
| 2025-01-02 | 100%            | ✅ Ya          |
| 2025-01-03 | 100%            | ✅ Ya          |
| 2025-01-04 | 100%            | ✅ Ya          |
| 2025-01-05 | 100%            | ✅ Ya          |

### Hasil Perhitungan
```
Denominator (D) = 5 laporan
Numerator (N)   = 5 laporan (semua perfect!)
Percentage (%)  = (5/5) × 100 = 100% ⭐
```

---

## 📝 Simulasi Kasus 3: Poor Compliance

### Indikator: "Patient Safety Rounds"
**Unit Kerja: Emergency Room**

### Data Daily Reports (Tidak ada yang 100%)

| Tanggal    | Compliance Score | Perfect (100%)? |
|------------|------------------|-----------------|
| 2025-01-01 | 75%             | ❌ Tidak       |
| 2025-01-02 | 80%             | ❌ Tidak       |
| 2025-01-03 | 65%             | ❌ Tidak       |
| 2025-01-04 | 90%             | ❌ Tidak       |
| 2025-01-05 | 85%             | ❌ Tidak       |

### Hasil Perhitungan
```
Denominator (D) = 5 laporan
Numerator (N)   = 0 laporan (tidak ada yang 100%)
Percentage (%)  = (0/5) × 100 = 0% ⚠️
```

---

## 📝 Simulasi Kasus 4: Tidak Ada Data

### Indikator: "Environmental Hygiene"
**Unit Kerja: Laboratory**

### Data Daily Reports
```
Tidak ada daily report untuk periode ini
```

### Hasil Perhitungan
```
Denominator (D) = 0 laporan (tidak ada laporan)
Numerator (N)   = 0 laporan
Percentage (%)  = 0% (atau N/A)
```

**Catatan**: Penilaian akan di-skip dengan error message "No daily reports found"

---

## 🔍 Detail Teknis

### 1. Bagaimana Compliance Score Dihitung?

Compliance score dihitung oleh `FormTemplate->calculateCompliance($responses)`:
- Mengambil semua field/pertanyaan dalam form
- Memeriksa jawaban user untuk setiap field
- Menghitung persentase field yang dijawab dengan benar/lengkap
- Return score 0-100%

### 2. Kapan Dianggap "Perfect"?

```php
$isPerfect = $score >= 100; // Harus EXACT 100% atau lebih
```

Hanya yang **tepat 100%** yang dihitung sebagai Numerator.

### 3. Data Apa Saja yang Disimpan?

Selain N/D/%, sistem juga menyimpan:
- **calculated_at**: Timestamp perhitungan
- **total_days_in_period**: Total hari dalam periode
- **reports_submitted**: Berapa laporan yang diinput
- **reports_perfect**: Berapa laporan yang perfect (sama dengan Numerator)
- **missing_dates**: Array tanggal-tanggal yang tidak ada laporan
- **compliance_breakdown**: Detail score per hari
- **form_template info**: ID dan judul indikator

---

## 💡 Interpretasi Hasil

### Skenario A: N=28, D=30 (93.33%)
```
✅ Bagus! 
- Dari 30 laporan yang diinput
- 28 laporan mencapai compliance 100%
- Hanya 2 laporan yang tidak perfect
```

### Skenario B: N=5, D=31 (16.13%)
```
⚠️ Perlu Perhatian!
- Ada 31 laporan yang diinput selama periode
- Tapi hanya 5 laporan yang perfect
- Artinya: konsistensi rendah, banyak yang hampir 100% tapi tidak exact
```

### Skenario C: N=10, D=10 (100%)
```
⭐ Perfect!
- 10 laporan yang diinput
- Semua 10 laporan compliance 100%
- Konsistensi sempurna untuk semua inputan!
```

### Skenario D: N=0, D=0 (0%)
```
❌ Tidak Ada Data
- Belum ada daily report sama sekali
- Perlu input data terlebih dahulu
```

---

## 🎓 Kesimpulan

### Kelebihan Metode Ini:
1. ✅ **Objektif**: Berdasarkan data harian yang terukur
2. ✅ **Transparan**: Bisa lihat breakdown per hari
3. ✅ **Otomatis**: Tidak perlu hitung manual
4. ✅ **Traceable**: Ada metadata lengkap untuk audit

### Limitasi:
1. ⚠️ Hanya menghitung laporan yang **diinput**
2. ⚠️ Yang hampir 100% (misalnya 99.5%) tetap dianggap tidak perfect
3. ⚠️ Tidak ada bobot/weight untuk laporan tertentu
4. ⚠️ Tanggal tanpa laporan diabaikan (bukan dihitung 0%)

### Best Practice:
- 📝 Input daily report setiap hari secara konsisten
- 🎯 Target compliance 100% di setiap field
- 📊 Monitor `missing_dates` untuk identifikasi gap
- 🔄 Re-calculate bila ada perbaikan data daily report

---

## 🚀 Testing Formula

Jika ingin test manual:

```php
// Contoh data
$totalLaporanDiinput = 25;
$laporanYangPerfect100Persen = 20;

// Hitung
$denominator = $totalLaporanDiinput;          // 25
$numerator = $laporanYangPerfect100Persen;    // 20
$percentage = ($numerator / $denominator) * 100;  // 80%

echo "N = {$numerator}\n";    // 20
echo "D = {$denominator}\n";  // 25
echo "% = {$percentage}%\n";  // 80%
```

---

**Dibuat**: 1 Februari 2026  
**Service**: `DailyReportAggregationService`  
**Action**: "Hitung dari Daily Report" di EditLaporanImut
