# Benchmarking Default Region Name - Implementation

**Date:** October 30, 2025  
**Feature:** Auto-fill region_name untuk region types dengan default value  
**Status:** ✅ Implemented & Tested

## 📋 Overview

Fitur ini menambahkan kemampuan auto-fill `region_name` untuk region types tertentu (Nasional dan Provinsi) dengan nilai default yang sudah ditentukan.

## 🎯 Requirements

1. **Nasional** → auto-fill dengan "Indonesia"
2. **Provinsi** → auto-fill dengan "Jawa Timur"
3. **Region lain** (Rumah Sakit, dll) → user input manual
4. Field `region_name` menjadi **read-only** jika ada default value
5. Helper text menunjukkan bahwa nilai terisi otomatis

## ✨ Implementation

### 1. Model: RegionType

**File:** `app/Models/RegionType.php`

```php
/**
 * Get default region name for this region type.
 * Returns null if no default (user must input manually).
 */
public function getDefaultRegionName(): ?string
{
    $type = strtolower(trim($this->type));
    
    // Remove emoji and extra spaces
    $type = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $type);
    $type = trim($type);
    
    return match (true) {
        str_contains($type, 'nasional') || str_contains($type, 'national') => 'Indonesia',
        str_contains($type, 'provinsi') || str_contains($type, 'province') => 'Jawa Timur',
        default => null,
    };
}

/**
 * Check if this region type has a default region name.
 */
public function hasDefaultRegionName(): bool
{
    return $this->getDefaultRegionName() !== null;
}
```

**Features:**
- ✅ Menangani emoji dalam nama region type
- ✅ Case-insensitive matching
- ✅ Partial string matching (flexible)
- ✅ Return null jika tidak ada default

### 2. Observer: ImutBenchmarkingObserver

**File:** `app/Observers/ImutBenchmarkingObserver.php`

**Added to `creating()` method:**

```php
// Auto-fill region_name if empty and region type has default
if (empty($benchmarking->region_name) && $benchmarking->regionType) {
    $defaultName = $benchmarking->regionType->getDefaultRegionName();
    if ($defaultName) {
        $benchmarking->region_name = $defaultName;
        Log::info("Auto-filled region_name: {$defaultName} for region_type: {$benchmarking->regionType->type}");
    }
}
```

**Features:**
- ✅ Auto-fill saat creating (sebelum save ke DB)
- ✅ Hanya jika `region_name` kosong
- ✅ Log untuk tracking
- ✅ Tidak override jika user sudah isi manual

### 3. Form Schema

**File:** `app/Filament/Resources/ImutDataResource/Schema/ImutDataSchema.php`

**Updated field:**

```php
TextInput::make('region_name')
    ->label(false)
    ->placeholder($regionType->hasDefaultRegionName() 
        ? $regionType->getDefaultRegionName() 
        : ($regionType->type === 'provinsi' ? 'Jawa Barat' : 'RS Harapan'))
    ->default($regionType->getDefaultRegionName())
    ->disabled($regionType->hasDefaultRegionName())
    ->dehydrated()
    ->helperText($regionType->hasDefaultRegionName() 
        ? '✓ Otomatis: ' . $regionType->getDefaultRegionName() 
        : 'Masukkan nama ' . $regionType->type)
    ->required(),
```

**Features:**
- ✅ Auto-fill default value
- ✅ Disabled (read-only) jika ada default
- ✅ Helper text informatif
- ✅ `dehydrated()` ensures value is saved
- ✅ Dynamic placeholder

## 🧪 Testing

### Test Results

```
Region Types dengan Default Values:
============================================================
[✓] 🌐 Nasional        -> Indonesia
[✓] 🏛️ Provinsi       -> Jawa Timur
[✗] 🏥 Rumah Sakit     -> (user input)

Testing Auto-fill region_name:
======================================================================

Test 1: Region Type = {🌐 Nasional}
Expected: Indonesia
Result: Indonesia
✓ PASS

Test 2: Region Type = {🏛️ Provinsi}
Expected: Jawa Timur
Result: Jawa Timur
✓ PASS

Test 3: Region Type = {🏥 Rumah Sakit}
Expected: NULL (no default)
Result: NULL
✓ PASS
```

**All tests passed! ✅**

## 📊 Behavior Matrix

| Region Type | Has Default? | Default Value | Field State | User Can Edit? |
|-------------|--------------|---------------|-------------|----------------|
| 🌐 Nasional | ✅ Yes | Indonesia | Disabled | ❌ No |
| 🏛️ Provinsi | ✅ Yes | Jawa Timur | Disabled | ❌ No |
| 🏥 Rumah Sakit | ❌ No | - | Enabled | ✅ Yes |
| Other | ❌ No | - | Enabled | ✅ Yes |

## 🔄 Workflow

### Creating Benchmarking (Nasional)

1. User pilih tab "🌐 Nasional"
2. Klik add row
3. Field `region_name` otomatis terisi "Indonesia"
4. Field menjadi **disabled** (tidak bisa diubah)
5. Helper text: "✓ Otomatis: Indonesia"
6. User isi field lain (benchmark_value, period, dll)
7. Save → Observer memastikan region_name = "Indonesia"

### Creating Benchmarking (Rumah Sakit)

1. User pilih tab "🏥 Rumah Sakit"
2. Klik add row
3. Field `region_name` **kosong** dan **enabled**
4. Helper text: "Masukkan nama 🏥 Rumah Sakit"
5. User **wajib isi** nama rumah sakit (e.g., "RS Harapan")
6. User isi field lain
7. Save

## 🎨 UI Preview

### Nasional Tab
```
┌─────────────────────────────────────────────────────────┐
│ Region Name: [Indonesia] 🔒                             │
│ ℹ️ ✓ Otomatis: Indonesia                                │
├─────────────────────────────────────────────────────────┤
│ Nilai (%): [85.5]                                       │
│ Berlaku Dari: [2025-01-01]                             │
│ Sampai: [2025-12-31]                                   │
└─────────────────────────────────────────────────────────┘
```

### Rumah Sakit Tab
```
┌─────────────────────────────────────────────────────────┐
│ Region Name: [____________]                             │
│ ℹ️ Masukkan nama 🏥 Rumah Sakit                         │
├─────────────────────────────────────────────────────────┤
│ Nilai (%): [75.0]                                       │
│ Berlaku Dari: [2025-01-01]                             │
│ Sampai: [2025-12-31]                                   │
└─────────────────────────────────────────────────────────┘
```

## 💡 Benefits

1. **Konsistensi Data**: Nama region nasional & provinsi selalu sama
2. **User-Friendly**: User tidak perlu ketik manual untuk region standar
3. **Error Prevention**: Typo tidak mungkin terjadi untuk region default
4. **Flexible**: Masih bisa add region type baru yang butuh manual input
5. **Scalable**: Mudah tambah region type baru dengan default value

## 🔧 How to Add New Default Region

Edit method `getDefaultRegionName()` di `RegionType.php`:

```php
return match (true) {
    str_contains($type, 'nasional') => 'Indonesia',
    str_contains($type, 'provinsi') => 'Jawa Timur',
    str_contains($type, 'kabupaten') => 'Surabaya',  // NEW!
    default => null,
};
```

## 📝 Notes

- Default value bisa diubah di satu tempat (`RegionType::getDefaultRegionName()`)
- Observer akan auto-apply untuk semua create operations
- Form akan auto-disable field jika ada default
- Backward compatible: existing data tidak terpengaruh

## ✅ Checklist

- [x] Model method `getDefaultRegionName()`
- [x] Model method `hasDefaultRegionName()`
- [x] Observer auto-fill logic
- [x] Form schema update (default + disabled)
- [x] Helper text di form
- [x] Testing semua region types
- [x] Documentation

## 🐛 Known Issues

None.

## 📚 Related Documentation

- [Benchmarking System Implementation](./benchmarking-system-implementation.md)
- [Benchmarking Quick Start](./benchmarking-quick-start.md)

---

**Status:** ✅ Production Ready  
**Last Updated:** October 30, 2025
