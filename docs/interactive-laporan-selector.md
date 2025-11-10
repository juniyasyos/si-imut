# Interactive Laporan Selector - Implementation Summary

## Overview
Menambahkan fitur dropdown interaktif di footer widget `ImutCapaianWidget` agar user dapat memilih laporan mana yang ingin dilihat statistiknya.

## Problem Statement
Sebelumnya:
- Widget chart menampilkan tren dari 6 laporan (Juni-Oktober 2025)
- Footer statistik hanya menampilkan data dari laporan terbaru (Oktober 2025)
- User tidak bisa melihat statistik detail dari laporan periode lain
- Inkonsistensi antara chart (multi-period) dan footer (single period)

## Solution Implementation

### 1. Widget Backend Changes (`app/Filament/Widgets/ImutCapaianWidget.php`)

#### Added Property
```php
public ?int $selectedLaporanId = null;
```
Property untuk menyimpan ID laporan yang dipilih user.

#### Added Livewire Handler
```php
public function updatedSelectedLaporanId(): void
{
    // Force widget untuk reload dengan data baru
    $this->dispatch('$refresh');
}
```
Method ini otomatis dipanggil Livewire ketika user mengubah pilihan dropdown.

#### Modified `calculateDetailedStatistics()` Method
**Before:**
```php
$latestLaporan = $laporans->sortByDesc('assessment_period_start')->first();
$laporans = collect([$latestLaporan]); // Hard-coded ke latest
```

**After:**
```php
// Default: ambil laporan terbaru jika tidak ada yang dipilih
if (!$this->selectedLaporanId) {
    $latestLaporan = $laporans->sortByDesc('assessment_period_start')->first();
    $this->selectedLaporanId = $latestLaporan?->id;
}

// Ambil laporan yang dipilih
$selectedLaporan = $laporans->firstWhere('id', $this->selectedLaporanId);
```

**Key Changes:**
- Auto-select latest laporan on first load
- Use user's selected laporan if available
- Fallback to latest if selected ID not found
- Added `available_laporans` array to statistics return

#### Return Structure Enhanced
```php
return [
    // ... existing fields ...
    'available_laporans' => [
        [
            'id' => 123,
            'name' => 'Laporan IMUT Oktober 2025',
            'period' => 'October 2025'
        ],
        // ... more laporans
    ],
    'selected_laporan_id' => $this->selectedLaporanId,
];
```

### 2. Frontend Changes (`resources/views/filament/widgets/imut-capaian-footer.blade.php`)

#### Added Dropdown Selector
```blade
<!-- Laporan Selector -->
<div class="mb-6 flex items-center justify-between">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📊 Ringkasan Statistik</h3>
    
    <div class="flex items-center gap-2">
        <label class="text-sm text-gray-700 dark:text-gray-300">Pilih Laporan:</label>
        <select 
            wire:model.live="selectedLaporanId"
            class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
        >
            @foreach ($stats['available_laporans'] as $laporan)
                <option value="{{ $laporan['id'] }}">{{ $laporan['name'] }} - {{ $laporan['period'] }}</option>
            @endforeach
        </select>
    </div>
</div>
```

**Key Features:**
- `wire:model.live` untuk real-time reactivity
- Loop semua available laporans dari backend
- Display nama + periode untuk clarity
- Dark mode support

#### Added Period Badge
```blade
<div class="flex items-center gap-2 mb-4">
    <span class="text-sm text-gray-600 dark:text-gray-400">
        📅 Menampilkan data: <strong class="text-blue-600 dark:text-blue-400">{{ $stats['laporan_used'] ?? 'N/A' }}</strong> 
        ({{ $stats['laporan_period'] ?? 'N/A' }})
    </span>
</div>
```

Menampilkan nama dan periode laporan yang sedang ditampilkan untuk context.

## Data Flow

```
User selects laporan from dropdown
    ↓
wire:model.live updates $selectedLaporanId
    ↓
updatedSelectedLaporanId() triggered
    ↓
dispatch('$refresh') called
    ↓
getData() re-executed
    ↓
calculateDetailedStatistics() uses new $selectedLaporanId
    ↓
New statistics calculated for selected laporan
    ↓
View re-rendered with updated data
```

## Available Laporans (Example Data)

Based on current database:
1. **Laporan IMUT Juni 2025** - Juni 2025 (116 IMUT)
2. **Laporan IMUT Juli 2025** - Juli 2025 (115 IMUT)
3. **Laporan IMUT Agustus 2025** - Agustus 2025 (115 IMUT)
4. **Laporan IMUT September 2025** - September 2025 (115 IMUT)
5. **Laporan IMUT Tes** - Oktober 2025 (3 IMUT)
6. **Laporan IMUT Oktober 2025** - Oktober 2025 (115 IMUT) ← Default

## Benefits

1. **User Control**: User dapat eksplorasi statistik dari berbagai periode
2. **Consistency**: Footer sekarang bisa sync dengan period manapun yang user ingin lihat
3. **No Breaking Changes**: Chart tetap menampilkan tren multi-period
4. **Default Behavior**: Auto-select latest laporan untuk UX yang smooth
5. **Performance**: Tidak ada query tambahan - data sudah di-fetch untuk chart

## Testing Checklist

- [ ] Dropdown muncul dengan semua laporan available
- [ ] Default selection = laporan terbaru (Oktober 2025)
- [ ] Mengubah dropdown → statistik update secara real-time
- [ ] Badge periode menampilkan nama + bulan yang benar
- [ ] Dark mode styling correct
- [ ] Total IMUT indicators berubah sesuai laporan dipilih
- [ ] Category details table update sesuai selection
- [ ] Tidak ada error di browser console
- [ ] Loading state smooth (wire:loading optional enhancement)

## Future Enhancements (Optional)

1. **Loading State**:
```blade
<div wire:loading wire:target="selectedLaporanId">
    <span class="text-xs text-gray-500">Memuat data...</span>
</div>
```

2. **Comparison Mode**: 
Allow user to compare 2 laporans side-by-side

3. **Save Selection**: 
Remember user's last selected laporan using browser storage

4. **Export with Selection**:
When exporting, use the selected laporan only

## Code Quality Notes

- ✅ Type hints maintained
- ✅ Backward compatible
- ✅ No duplicate queries
- ✅ Follows existing coding patterns
- ✅ Minimal changes to existing logic
- ✅ Dark mode support included
- ⚠️ IDE warning on `Auth::user()->can()` is false positive (Laravel dynamic method)

## Files Modified

1. `app/Filament/Widgets/ImutCapaianWidget.php`
   - Added: `$selectedLaporanId` property
   - Added: `updatedSelectedLaporanId()` method
   - Modified: `calculateDetailedStatistics()` to use selected laporan

2. `resources/views/filament/widgets/imut-capaian-footer.blade.php`
   - Added: Laporan selector dropdown
   - Added: Period information badge
   - Removed: Duplicate heading

## Git Commit Message Suggestion

```
feat: Add interactive laporan selector to ImutCapaianWidget footer

- Add dropdown to select which laporan period to view statistics for
- Auto-select latest laporan by default
- Display selected period badge for context
- Implement Livewire reactivity for real-time updates
- Maintain chart multi-period view while allowing footer customization

Fixes inconsistency between chart (6 periods) and footer (1 period)
Enhances UX by giving users control over which data to explore
```

---

**Implementation Date**: 2025
**Status**: ✅ Complete
**Developer**: Ahmad Ilyas (with AI assistance)
