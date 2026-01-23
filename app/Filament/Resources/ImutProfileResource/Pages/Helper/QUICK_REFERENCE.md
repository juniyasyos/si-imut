# Quick Reference - FormFields Modular System

## 🎯 Kapan Menggunakan Module Mana?

### 1. Membuat Field Secara Manual
```php
// ✅ GUNAKAN: Direct Builder
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TextFieldBuilder;

$field = TextFieldBuilder::create(
    'nama', 
    'Nama Lengkap', 
    'Masukkan nama lengkap Anda',
    255,
    true
);
```

### 2. Membuat Field dari Database Config
```php
// ✅ GUNAKAN: FormFields::createFormComponent()
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;

$field = FormFields::createFormComponent($dynamicField, 'prefix_');
```

### 3. Operasi Waktu
```php
// ✅ GUNAKAN: TimeUtility
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;

$minutes = TimeUtility::convertTimeToMinutes('08:30:00');
$isValid = TimeUtility::checkDurationValidity('08:00', '17:00', '08:00');
```

### 4. Validasi Time Duration
```php
// ✅ GUNAKAN: TimeDurationFieldBuilder
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeDurationFieldBuilder;

// Di dalam closure afterStateUpdated
TimeDurationFieldBuilder::validateDurationAndSetIndicator($get, $set, 'jam_kerja');
```

### 5. Conditional Logic
```php
// ✅ GUNAKAN: ConditionalLogicHandler
use App\Filament\Resources\ImutProfileResource\Pages\Helper\ConditionalLogicHandler;

$visibleCondition = ConditionalLogicHandler::getVisibilityCondition(
    $field->conditional_logic, 
    'prefix_'
);
```

## 📋 Field Types Mapping

| Field Type | Builder Class | Method |
|-----------|---------------|--------|
| Text | `TextFieldBuilder` | `create()` |
| Number | `NumberFieldBuilder` | `create()` |
| Single Select | `SelectFieldBuilder` | `createSingleSelect()` |
| Multi Select | `SelectFieldBuilder` | `createMultiSelect()` |
| Boolean | `BooleanFieldBuilder` | `create()` |
| Time Duration | `TimeDurationFieldBuilder` | `create()` |
| Time Range | `TimeRangeFieldBuilder` | `create()` |

## 🔨 Common Use Cases

### Use Case 1: Buat Text Field dengan Conditional
```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TextFieldBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\ConditionalLogicHandler;

$visibleWhen = fn($get) => $get('status') === 'active';

$field = TextFieldBuilder::create(
    fieldKey: 'keterangan',
    label: 'Keterangan',
    helperText: 'Isi jika status aktif',
    maxLength: 500,
    required: false,
    visibleCondition: $visibleWhen
);
```

### Use Case 2: Time Duration dengan Custom Threshold
```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeDurationFieldBuilder;

$timeDuration = TimeDurationFieldBuilder::create(
    fieldKey: 'jam_lembur',
    required: true,
    visibleCondition: true,
    defaultThreshold: '04:00:00' // 4 jam maksimal
);
```

### Use Case 3: Dynamic Select dari Database
```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\SelectFieldBuilder;

// Extract options dari database model
$fieldOptions = Field::find($fieldId)->options;
$options = SelectFieldBuilder::extractOptions($fieldOptions);

$select = SelectFieldBuilder::createSingleSelect(
    fieldKey: 'jabatan',
    label: 'Pilih Jabatan',
    helperText: null,
    options: $options,
    required: true,
    visibleCondition: true
);
```

### Use Case 4: Calculate Work Duration
```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;

$startTime = '08:00:00';
$endTime = '17:00:00';

// Hitung durasi dalam menit
$duration = TimeUtility::calculateDurationInMinutes($startTime, $endTime); // 540 menit = 9 jam

// Convert ke format waktu
$formattedDuration = TimeUtility::convertMinutesToTime($duration); // '09:00:00'

// Validasi apakah dalam threshold
$isValid = TimeUtility::checkDurationValidity($startTime, $endTime, '08:00:00'); // false (lebih dari 8 jam)
```

### Use Case 5: Form dengan Multiple Field Types
```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\{
    TextFieldBuilder,
    NumberFieldBuilder,
    SelectFieldBuilder,
    TimeDurationFieldBuilder
};

return $form->schema([
    TextFieldBuilder::create('nama', 'Nama', null, 255, true),
    
    NumberFieldBuilder::create('umur', 'Umur', 'Usia dalam tahun', 17, 65, true),
    
    SelectFieldBuilder::createSingleSelect(
        'jenis_kelamin',
        'Jenis Kelamin',
        null,
        ['L' => 'Laki-laki', 'P' => 'Perempuan'],
        true
    ),
    
    TimeDurationFieldBuilder::create('jam_kerja', true, true, '08:00:00'),
]);
```

## 🚫 What NOT to Do

```php
// ❌ JANGAN: Panggil method private langsung
FormFields::createStartTimePicker('key', true); // Error! Private method

// ✅ LAKUKAN: Gunakan builder class
TimeDurationFieldBuilder::createStartTimePicker('key', true);

// ❌ JANGAN: Mix old and new approach
$minutes = FormFields::convertTimeToMinutes('08:00'); // Deprecated

// ✅ LAKUKAN: Gunakan utility class
$minutes = TimeUtility::convertTimeToMinutes('08:00');

// ❌ JANGAN: Manual conditional logic
$visible = function($get) use ($logic) {
    return $get($logic['field']) === $logic['value'];
};

// ✅ LAKUKAN: Gunakan handler
$visible = ConditionalLogicHandler::getVisibilityCondition($conditionalLogic);
```

## 📊 Performance Tips

1. **Reuse builders** - Cache builder instances jika membuat banyak field sejenis
2. **Batch operations** - Gunakan `extractOptions()` sekali untuk multiple selects
3. **Lazy loading** - Gunakan `visibleCondition` untuk field yang jarang digunakan
4. **Time operations** - Cache hasil `TimeUtility` calculations jika digunakan berulang

## 🔧 Troubleshooting

### Problem: Field tidak muncul
**Solution:** Cek `visibleCondition` - mungkin conditional logic salah

### Problem: Validation tidak jalan
**Solution:** Pastikan `live()` ada dan `afterStateUpdated` terpanggil

### Problem: Time duration tidak update indicator
**Solution:** Cek apakah `validateDurationAndSetIndicator` dipanggil di semua time pickers

### Problem: Options tidak muncul di select
**Solution:** Gunakan `SelectFieldBuilder::extractOptions()` untuk convert database options

## 📚 Related Files

- `FormFields.php` - Main orchestrator
- `TimeUtility.php` - Time operations
- `ConditionalLogicHandler.php` - Visibility logic
- `FieldBuilders/` - Individual field builders
- `README.md` - Full documentation

## 💡 Pro Tips

1. Always use **named parameters** untuk clarity
2. Import builder classes dengan **group use statement**
3. Create **custom builders** untuk complex field combinations
4. Use **PHPDoc** untuk better IDE autocomplete
5. Test builders **independently** dengan unit tests
