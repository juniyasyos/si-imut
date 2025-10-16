# Implementasi Profile Selection dengan Periode Validitas

## 📋 **Overview**

Implementasi ini menambahkan sistem **pemilihan profil berdasarkan periode validitas** untuk mengatasi masalah temporal dalam pembuatan laporan IMUT. Sekarang sistem dapat membuat laporan untuk periode lama (misalnya Desember 2023) dengan menggunakan profil yang tepat sesuai periode tersebut.

## 🔧 **Perubahan yang Dilakukan**

### **1. Database Schema**

#### **a. Tabel `imut_profil` - Tambah Periode Validitas**
```sql
ALTER TABLE imut_profil ADD COLUMN:
- valid_from DATE NULL COMMENT 'Tanggal mulai berlaku profil'
- valid_until DATE NULL COMMENT 'Tanggal berakhir berlaku (NULL = selamanya)'
```

#### **b. Tabel Baru `laporan_imut_profiles` - Tracking Profil**
```sql
CREATE TABLE laporan_imut_profiles:
- laporan_imut_id (FK ke laporan_imut)
- imut_data_id (FK ke imut_data)
- imut_profil_id (FK ke imut_profil yang dipilih)
- selected_at (timestamp pemilihan)
- selection_metadata (JSON metadata pemilihan)
```

### **2. Model Enhancements**

#### **a. ImutProfile - Method Validitas**
- `isValidOnDate($date)` - Cek valid pada tanggal tertentu
- `isValidForPeriod($start, $end)` - Cek valid pada periode
- `scopeValidOnDate()` - Query scope untuk tanggal
- `scopeValidForPeriod()` - Query scope untuk periode

#### **b. ImutData - Profile Selection**
- `profileValidOnDate($date)` - Profil valid pada tanggal
- `profileValidForPeriod($start, $end)` - Profil valid pada periode  
- `profileForLaporan($laporan)` - Profil tepat untuk laporan

#### **c. LaporanImut - Profile Tracking**
- `selectedProfiles()` - Relasi ke profil terpilih
- `getSelectedProfileFor($imutDataId)` - Profil untuk data tertentu

### **3. Job ProsesPenilaianImut - Logika Baru**

#### **Alur Pemilihan Profil:**
1. **Prioritas 1:** Cek apakah sudah ada profil yang dipilih khusus untuk laporan
2. **Prioritas 2:** Cari profil yang valid untuk periode laporan
3. **Auto-tracking:** Simpan record profil yang digunakan

#### **Fitur Baru:**
- Metadata pemilihan profil (auto/manual, version, periode)
- Logging transparansi profil terpilih
- Notifikasi yang lebih informatif dengan periode laporan

## 🚀 **Cara Penggunaan**

### **1. Migration**
```bash
php artisan migrate
```

### **2. Existing Data**
- Profil existing akan mendapat `valid_from` = `created_at`
- `valid_until` tetap NULL (berlaku selamanya)

### **3. Pembuatan Laporan**
```php
// Laporan untuk periode lama akan otomatis menggunakan profil yang tepat
$laporan = LaporanImut::create([
    'name' => 'Laporan Desember 2023',
    'assessment_period_start' => '2023-12-01',
    'assessment_period_end' => '2023-12-31',
    // ...
]);

// Job akan otomatis memilih profil yang valid untuk periode 2023-12
ProsesPenilaianImut::dispatch($laporan->id);
```

### **4. Manual Profile Selection (Future Enhancement)**
```php
// Untuk pemilihan manual profil khusus
LaporanImutProfile::create([
    'laporan_imut_id' => $laporan->id,
    'imut_data_id' => $imutData->id,
    'imut_profil_id' => $specificProfile->id,
    'selection_metadata' => [
        'selection_method' => 'manual_override',
        'selected_by' => auth()->id(),
        'reason' => 'Profil khusus untuk kasus X'
    ]
]);
```

## 📊 **Keuntungan Implementasi**

### **1. Temporal Accuracy**
- ✅ Laporan periode lama menggunakan profil yang tepat
- ✅ Tidak lagi tergantung pada `latestProfile`
- ✅ Historical reporting yang akurat

### **2. Transparency & Traceability**
- ✅ Tracking profil mana yang digunakan untuk setiap laporan
- ✅ Metadata pemilihan (auto/manual, alasan, dll)
- ✅ Logging untuk audit trail

### **3. Flexibility**
- ✅ Support manual override jika diperlukan
- ✅ Periode validitas yang fleksibel
- ✅ Backward compatibility dengan data existing

### **4. Performance**
- ✅ Index optimized untuk query periode
- ✅ Efficient profile selection algorithm
- ✅ Cached results via relationship

## 🛡️ **Production Safety**

### **1. Migration Safety**
- ✅ Kolom baru NULLABLE (tidak break existing data)
- ✅ Auto-populate existing data dengan safe values
- ✅ Rollback mechanism tersedia

### **2. Backward Compatibility**
- ✅ Existing code tetap berfungsi
- ✅ `latestProfile` masih tersedia untuk kebutuhan lain
- ✅ Gradual adoption possible

### **3. Error Handling**
- ✅ Fallback ke profil terbaru jika tidak ada yang valid
- ✅ Comprehensive logging
- ✅ User-friendly notifications

## 📝 **Example Scenarios**

### **Scenario 1: Laporan Periode Lama**
```
Bulan: Oktober 2025
Buat laporan: Desember 2023

Sebelum:
❌ Menggunakan profil versi 2025 (salah)

Sesudah:  
✅ Menggunakan profil versi 2023 yang valid pada Desember 2023
```

### **Scenario 2: Multiple Profile Versions**
```
ImutData X memiliki:
- Profile v1: valid_from=2023-01-01, valid_until=2023-06-30
- Profile v2: valid_from=2023-07-01, valid_until=NULL

Laporan Mei 2023: akan pakai Profile v1 ✅
Laporan Agustus 2023: akan pakai Profile v2 ✅
```

### **Scenario 3: Manual Override**
```
Admin bisa memilih profil khusus untuk laporan tertentu
dengan alasan spesifik, dan sistem akan mencatat
metadata pemilihan tersebut.
```

## 🔄 **Migration Commands**

```bash
# 1. Tambah kolom periode validitas
php artisan migrate --path=database/migrations/2025_10_16_000001_add_validity_period_to_imut_profil_table.php

# 2. Buat tabel tracking profil  
php artisan migrate --path=database/migrations/2025_10_16_000002_create_laporan_imut_profiles_table.php

# 3. Populate data existing
php artisan migrate --path=database/migrations/2025_10_16_000003_populate_validity_period_for_existing_profiles.php
```

---

*Generated on: 2025-10-16*  
*SI-IMUT Profile Validity Implementation*
