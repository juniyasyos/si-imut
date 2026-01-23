# FormFields Modular Architecture

Dokumentasi lengkap untuk sistem form field yang sudah dimodularisasi.

## 📁 Struktur File

```
Helper/
├── FormFields.php                          # Main orchestrator (entry point)
├── ConditionalLogicHandler.php            # Handle visibility conditions
├── TimeUtility.php                        # Time conversion utilities
└── FieldBuilders/                         # Field builder modules
    ├── TextFieldBuilder.php               # Text input fields
    ├── NumberFieldBuilder.php             # Number input fields
    ├── SelectFieldBuilder.php             # Single & multi select fields
    ├── BooleanFieldBuilder.php            # Boolean/radio fields
    ├── TimeDurationFieldBuilder.php       # Time duration with validation
    └── TimeRangeFieldBuilder.php          # Simple time range
```

## 🎯 Keuntungan Modular

1. **Separation of Concerns** - Setiap class fokus pada satu tanggung jawab
2. **Reusability** - Module bisa digunakan di mana saja
3. **Testability** - Mudah di-unit test
4. **Maintainability** - Perubahan terisolasi per module
5. **Scalability** - Mudah menambah field type baru

## 📚 Penggunaan

### 1. FormFields (Main Entry Point)

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;

// Cara lama (masih didukung untuk backward compatibility)
$field = FormFields::createFormComponent($fieldConfig, 'prefix_');

// Cek visibility
$isVisible = FormFields::isFieldVisible($field, $formData);
```

### 2. TextFieldBuilder

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TextFieldBuilder;

$textField = TextFieldBuilder::create(
    fieldKey: 'nama_pegawai',
    label: 'Nama Pegawai',
    helperText: 'Masukkan nama lengkap',
    maxLength: 255,
    required: true,
    visibleCondition: true
);
```

### 3. NumberFieldBuilder

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\NumberFieldBuilder;

$numberField = NumberFieldBuilder::create(
    fieldKey: 'umur',
    label: 'Umur',
    helperText: 'Masukkan umur dalam tahun',
    min: 18,
    max: 65,
    required: true,
    visibleCondition: true
);
```

### 4. SelectFieldBuilder

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\SelectFieldBuilder;

// Single Select
$singleSelect = SelectFieldBuilder::createSingleSelect(
    fieldKey: 'jenis_kelamin',
    label: 'Jenis Kelamin',
    helperText: 'Pilih jenis kelamin',
    options: [
        'L' => 'Laki-laki',
        'P' => 'Perempuan'
    ],
    required: true,
    visibleCondition: true
);

// Multi Select
$multiSelect = SelectFieldBuilder::createMultiSelect(
    fieldKey: 'hobi',
    label: 'Hobi',
    helperText: 'Pilih satu atau lebih hobi',
    options: [
        'olahraga' => 'Olahraga',
        'membaca' => 'Membaca',
        'musik' => 'Musik'
    ],
    required: false,
    visibleCondition: true
);

// Extract options dari database
$options = SelectFieldBuilder::extractOptions($fieldOptions);
```

### 5. BooleanFieldBuilder

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\BooleanFieldBuilder;

$booleanField = BooleanFieldBuilder::create(
    fieldKey: 'aktif',
    label: 'Status Aktif',
    helperText: 'Apakah pegawai masih aktif?',
    options: [
        '1' => 'Ya',
        '0' => 'Tidak'
    ],
    required: true,
    visibleCondition: true
);
```

### 6. TimeDurationFieldBuilder

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeDurationFieldBuilder;

// Complete field with validation
$timeDuration = TimeDurationFieldBuilder::create(
    fieldKey: 'jam_kerja',
    required: true,
    visibleCondition: true,
    defaultThreshold: '08:00:00'
);

// Individual components
$startPicker = TimeDurationFieldBuilder::createStartTimePicker('jam_kerja', true);
$endPicker = TimeDurationFieldBuilder::createEndTimePicker('jam_kerja', true);
$thresholdPicker = TimeDurationFieldBuilder::createThresholdPicker('jam_kerja', '08:00:00');
$indicator = TimeDurationFieldBuilder::createValidationIndicator('jam_kerja');

// Validation
TimeDurationFieldBuilder::validateDurationAndSetIndicator($get, $set, 'jam_kerja');
$isValid = TimeDurationFieldBuilder::isDurationValid($get, 'jam_kerja');
```

### 7. TimeRangeFieldBuilder

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeRangeFieldBuilder;

$timeRange = TimeRangeFieldBuilder::create(
    fieldKey: 'jam_operasional',
    required: true,
    visibleCondition: true
);
```

### 8. TimeUtility

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;

// Convert time to minutes
$minutes = TimeUtility::convertTimeToMinutes('08:30:00'); // Returns 510

// Convert minutes to time
$time = TimeUtility::convertMinutesToTime(510); // Returns '08:30:00'

// Calculate duration
$duration = TimeUtility::calculateDurationInMinutes('08:00:00', '17:00:00'); // Returns 540

// Check validity
$isValid = TimeUtility::checkDurationValidity('08:00:00', '16:00:00', '08:00:00'); // Returns true

// Format time
$formatted = TimeUtility::formatTime('8:30'); // Returns '08:30:00'

// Validate time
$isValidTime = TimeUtility::isValidTime('08:30:00'); // Returns true
```

### 9. ConditionalLogicHandler

```php
use App\Filament\Resources\ImutProfileResource\Pages\Helper\ConditionalLogicHandler;

// Get visibility condition
$condition = ConditionalLogicHandler::getVisibilityCondition(
    $field->conditional_logic,
    'prefix_'
);

// Check if field visible
$isVisible = ConditionalLogicHandler::isFieldVisible($field, $formData);

// Check specific condition
$isMet = ConditionalLogicHandler::isConditionMet(
    ['condition_type' => 'equals', 'expected_value' => 'yes'],
    'yes'
);
```

## 🔧 Menambah Field Type Baru

Untuk menambahkan field type baru, buat builder class baru:

```php
<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\DatePicker;

class DateFieldBuilder
{
    public static function create(
        string $fieldKey,
        string $label,
        ?string $helperText = null,
        bool $required = false,
        $visibleCondition = true
    ): DatePicker {
        return DatePicker::make($fieldKey)
            ->label($label)
            ->helperText($helperText)
            ->required($required)
            ->visible($visibleCondition);
    }
}
```

Lalu tambahkan case baru di `FormFields::createFormComponent()`:

```php
case 'date':
    return DateFieldBuilder::create(
        $fieldKey,
        $label,
        $helperText,
        $required,
        $visibleCondition
    );
```

## 🧪 Testing

Setiap module dapat di-test secara terpisah:

```php
// Test TimeUtility
$this->assertEquals(510, TimeUtility::convertTimeToMinutes('08:30:00'));

// Test ConditionalLogicHandler
$this->assertTrue(ConditionalLogicHandler::isConditionMet([
    'condition_type' => 'equals',
    'expected_value' => 'yes'
], 'yes'));

// Test Field Builders
$field = TextFieldBuilder::create('test', 'Test Label');
$this->assertInstanceOf(TextInput::class, $field);
```

## 📝 Backward Compatibility

Semua method lama masih didukung melalui FormFields dengan annotation `@deprecated`:

```php
// Legacy (still works but deprecated)
FormFields::validateDurationAndSetIndicator($get, $set, 'field_key');
FormFields::convertTimeToMinutes('08:00:00');

// New recommended way
TimeDurationFieldBuilder::validateDurationAndSetIndicator($get, $set, 'field_key');
TimeUtility::convertTimeToMinutes('08:00:00');
```

## 🚀 Best Practices

1. **Gunakan builder classes secara langsung** untuk kode baru
2. **FormFields::createFormComponent()** hanya untuk dynamic field generation
3. **TimeUtility** untuk semua operasi waktu
4. **ConditionalLogicHandler** untuk semua conditional logic
5. **Buat builder baru** untuk field type yang kompleks
6. **Test setiap module** secara terpisah
7. **Dokumentasi** setiap method dengan PHPDoc

## 📊 Diagram Arsitektur

```
┌─────────────────────────────────────────┐
│         FormFields (Main)               │
│     (Entry Point & Orchestrator)        │
└────────────┬───────────────────────────┘
             │
             ├─────────────────────────────┐
             │                             │
    ┌────────▼────────┐         ┌─────────▼────────┐
    │ ConditionalLogic│         │   Field Builders  │
    │    Handler      │         │   (Individual)    │
    └─────────────────┘         └──────┬────────────┘
                                       │
                    ┌──────────────────┼──────────────────┐
                    │                  │                  │
            ┌───────▼──────┐  ┌────────▼───────┐  ┌──────▼──────┐
            │ TextBuilder  │  │ SelectBuilder  │  │ TimeBuilder │
            └──────────────┘  └────────────────┘  └──────┬──────┘
                                                          │
                                                  ┌───────▼────────┐
                                                  │  TimeUtility   │
                                                  └────────────────┘
```

## 💡 Tips

- Gunakan named parameters untuk clarity
- Builder methods selalu return Filament Component
- Utility methods selalu return primitive types
- Handler methods return bool atau callable
- Semua class bersifat static (no instantiation needed)

## 🔄 Migration Guide

Jika Anda memiliki kode lama, ini cara migrationnya:

```php
// OLD
FormFields::validateDurationAndSetIndicator($get, $set, 'jam_kerja');

// NEW
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeDurationFieldBuilder;
TimeDurationFieldBuilder::validateDurationAndSetIndicator($get, $set, 'jam_kerja');

// OLD
$minutes = FormFields::convertTimeToMinutes('08:00:00');

// NEW
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
$minutes = TimeUtility::convertTimeToMinutes('08:00:00');
```

## 📞 Support

Untuk pertanyaan atau issue, silakan hubungi tim development atau buat issue di repository project.
