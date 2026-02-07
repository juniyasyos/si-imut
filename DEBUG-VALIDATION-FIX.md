# Perbaikan Validasi Status Data di Table View

## Masalah
Kolom "Tervalidasi" menampilkan ✗ (invalid) padahal semua field sudah correct dengan ✓ pada kolom "Sesuai".

## Root Cause
Ada dua logika validasi yang bertentangan di [TableViewController.php](app/Http/Controllers/TableViewController.php):

### Logika Lama (Line 480-483)
```php
$validCount = $entry->fieldResponses->where('compliance_score', '>', 0)->count();
$totalCount = $entry->fieldResponses->count();
$row['validation_status'] = ($totalCount > 0 && $validCount === $totalCount) ? 1 : 0;
```

**Masalah:** Mengecek **SEMUA** field responses. Jika ada 1 field yang tidak terisi (fieldValue = null atau compliance_score = 0), maka validation_status = 0 (invalid).

**Contoh:**
- Field "waktu-lapor-tes-kritis" (time_duration) = valid, compliance_score = 100 ✓
- Field "pemeriksaan" (text field) = kosong, compliance_score = 0 ✗
- Hasil: 1 dari 2 field valid → validation_status = 0 (INVALID)

## Solusi

### 1. Filter Field yang Terisi (Lines 475-500)
```php
$filledFieldResponses = $entry->fieldResponses->filter(function ($fr) {
    $fieldValue = $fr->field_value;
    $fieldType = $fr->formField?->field_type;
    
    // Hanya count field yang benar-benar ada/terisi
    if ($fieldType === 'time_duration' && is_array($fieldValue)) {
        return isset($fieldValue['start_time']) && isset($fieldValue['end_time']);
    } elseif ($fieldType === 'time_range' && is_array($fieldValue)) {
        return isset($fieldValue['input_value']);
    } else {
        return $fieldValue !== null;
    }
});
```

**Hasil:** Hanya field yang terisi yang di-check. Field kosong tidak mempengaruhi validation_status.

### 2. Perbaiki Label Kolom (Line 282)
Ubah dari: `'label' => 'Pengumpul Data'` (duplicated)  
Menjadi: `'label' => 'Status Kepatuhan'` (descriptive)

## Struktur Kolom Final

| No | Kolom | Keterangan |
|----|-------|-----------|
| 1 | Tanggal | Tanggal laporan |
| 2-N | Field Responses | Data laporan (time_duration, text, dll) |
| N+1 | Pengumpul Data | Nama user yang submit |
| N+2 | Status Kepatuhan | ✓ atau ✗ (validation_status) |
| N+3 | Tervalidasi | ✓ atau ✗ (entry.validation_status) |
| N+4 | Validator | Nama user yang validate |

## Testing
Untuk verifikasi, lihat log:
```bash
tail -f storage/logs/laravel.log | grep "Entry validation status"
```

Akan menampilkan:
- `filled_field_responses`: Jumlah field yang terisi
- `valid_field_responses`: Jumlah field yang valid (compliance_score > 0)
- `validation_status`: Hasil akhir (1 = valid, 0 = invalid)

