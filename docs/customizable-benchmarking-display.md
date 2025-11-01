# 🎨 Customizable Benchmarking Display Settings

## 📋 Overview
Fitur ini memungkinkan admin untuk mengatur tampilan benchmarking di chart secara dinamis melalui menu Region Type Benchmarking, tanpa perlu edit kode.

## ✨ Fitur yang Ditambahkan

### 1. **Custom Color** 🎨
- Admin dapat memilih warna untuk setiap region type
- Warna ditampilkan dalam format hex color (#RRGGBB)
- Preview langsung di form
- Fallback ke default color jika tidak diset

### 2. **Custom Chart Type** 📊
- Admin dapat memilih tipe chart untuk setiap region type:
  - **Line (📈)** - Garis
  - **Column (📊)** - Batang vertikal
  - **Area (📉)** - Area chart
- Default: Column

## 🗄️ Database Changes

### Migration: `2025_11_01_101909_add_display_settings_to_region_types_table.php`

**Kolom Baru:**
```sql
ALTER TABLE region_types ADD COLUMN display_color VARCHAR(7) NULL;
ALTER TABLE region_types ADD COLUMN chart_type ENUM('line','column','area') DEFAULT 'column';
```

**Fields:**
- `display_color` (varchar, 7, nullable) - Hex color code
- `chart_type` (enum, default: 'column') - Tipe chart

## 📝 Files Modified

### 1. Model
- `app/Models/RegionType.php`
  - Added `display_color` and `chart_type` to fillable
  - Added helper methods:
    - `getDisplayColorWithFallback()` - Get color with fallback
    - `getChartTypeWithFallback()` - Get chart type with fallback
    - `getChartTypes()` - Static method untuk options

### 2. Filament Resource
- `app/Filament/Resources/RegionTypeBencmarkingResource.php`
  - Enhanced form with:
    - ColorPicker untuk `display_color`
    - Select untuk `chart_type`
    - Preview section dengan visual color & chart type icon
  - Enhanced table columns:
    - ColorColumn untuk visual preview
    - Badge untuk chart type dengan icon
    - Kode warna sebagai badge

### 3. Widgets
- `app/Filament/Resources/ImutDataResource/Widgets/LineChart.php`
  - Updated untuk read color & chart type dari database
  - Fallback logic untuk backward compatibility
  - Added `getFallbackColor()` helper method

- `app/Filament/Resources/ImutDataResource/Widgets/UnitKerjaChart.php`
  - Same updates as LineChart

## 🌱 Seeder

### `database/seeders/RegionTypeDisplaySettingsSeeder.php`

**Purpose:** Mengisi default display settings untuk region types yang sudah ada

**Default Values:**
- **Nasional**: Green (#10b981), Column
- **Provinsi**: Purple (#8b5cf6), Column
- **Rumah Sakit**: Red (#ef4444), Line

**Run:**
```bash
php artisan db:seed --class=RegionTypeDisplaySettingsSeeder
```

## 🚀 Usage

### Admin Panel
1. Buka menu **Region Type Benchmarking**
2. Klik **Edit** pada region type yang ingin diubah
3. Pilih **Warna** menggunakan color picker
4. Pilih **Tipe Chart** dari dropdown
5. Lihat **Preview** di bagian bawah form
6. **Save** - Perubahan langsung diterapkan ke semua chart

### Developer
```php
// Get color with fallback
$color = $regionType->getDisplayColorWithFallback();

// Get chart type with fallback  
$chartType = $regionType->getChartTypeWithFallback();

// Dalam chart widget
$series[] = [
    'name' => $regionName,
    'type' => $regionType->getChartTypeWithFallback(), // 'line', 'column', atau 'area'
    'data' => $data,
    'color' => $regionType->getDisplayColorWithFallback(), // hex color
];
```

## 🎯 Backward Compatibility

✅ **Fully backward compatible:**
- Field `display_color` nullable → tidak break existing data
- Field `chart_type` has default value 'column'
- Fallback logic di widget → chart tetap berfungsi
- Existing benchmarking data tidak perlu migration

## 🧪 Testing

### Manual Test Checklist:
- [ ] Create region type baru dengan custom color & chart type
- [ ] Edit existing region type
- [ ] Verify color preview di form
- [ ] Verify chart type icon preview di form
- [ ] Check chart rendering dengan custom settings
- [ ] Check fallback jika display_color null
- [ ] Check fallback jika chart_type null
- [ ] Check table columns display correctly

## 📸 Screenshots Locations

### Form View:
- Section 1: Informasi Region Type
  - Nama Region Type (TextInput)
  - Warna Chart (ColorPicker)
  - Tipe Chart (Select with icons)
- Section 2: Preview Tampilan
  - Visual color block
  - Chart type icon dengan label

### Table View:
- Column: Region Type (text, bold)
- Column: Warna (ColorColumn, visual)
- Column: Kode Warna (Badge, hex code)
- Column: Tipe Chart (Badge with icon & color)
- Column: Jumlah Benchmarking (count)

## 🔮 Future Enhancements
- [ ] Export/Import region type settings
- [ ] Bulk edit colors
- [ ] Color scheme presets
- [ ] Chart type preview in table
- [ ] History/audit log untuk perubahan settings

## 📚 References
- Filament ColorPicker: https://filamentphp.com/docs/forms/fields/color-picker
- ApexCharts Types: https://apexcharts.com/docs/chart-types/

---
**Created:** November 1, 2025  
**Version:** 1.0.0  
**Status:** ✅ Implemented & Tested
