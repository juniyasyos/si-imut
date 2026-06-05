# Dashboard Refactoring Guide

## Struktur Baru

Setelah refaktoring, dashboard menggunakan struktur modular dengan pemisahan concerns yang jelas:

### Alpine Stores (dalam `stores/`)

1. **dashboard-state.blade.php**
   - State management utama untuk seluruh dashboard
   - Mengatur: date, filters, indicators, monitoring data
   - Methods: date selection, monitoring control, formatting
   - Properties computed: filteredIndicators, filteredMonitoringData

2. **indicators-loader.blade.php**
   - Menangani lazy loading report counts
   - Batch loading optimization
   - Loading states dan caching

3. **content-syncer.blade.php**
   - Sinkronisasi selectedDate antara Livewire dan Alpine.js
   - Validasi nilai date
   - Fallback ke current date

### Utilities

**public/js/dashboard-utils.js**
- Fungsi formatting (date, number, version)
- Date navigation helpers
- Utility functions (debounce, throttle, merge)

### Component Structure

```
partials/components/
├── stores/
│   ├── dashboard-state.blade.php      ← Main Alpine store
│   ├── indicators-loader.blade.php    ← Report count loading
│   └── content-syncer.blade.php       ← Livewire sync
├── header/
│   ├── header-section.blade.php
│   └── filters-section.blade.php
├── navigation/
│   ├── date-navigation.blade.php
│   ├── date-header.blade.php
│   ├── month-navigation.blade.php
│   └── legend.blade.php
├── indicators/
│   ├── desktop-indicator-card.blade.php
│   ├── action-buttons.blade.php
│   ├── status-indicator.blade.php
│   ├── share-button.blade.php
│   └── indicators-empty-state.blade.php
├── modal/
│   └── slide-over.blade.php
├── monitoring/
│   └── monitoring-view.blade.php
└── scripts/
    └── scripts-styles.blade.php
```

## Cara Menggunakan

### Main Page Template

```blade
<x-filament-panels::page>
    <div 
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.stores.dashboard-state', [
            'selectedDate' => $selectedDate,
            'selectedMonth' => $selectedMonth,
            'indicators' => $indicators,
            'matrixData' => $matrixData,
            'categoryColors' => $categoryColors,
            'monitoringTemplates' => $monitoringTemplates,
        ])
        x-cloak
    >
        <!-- Main content -->
    </div>
</x-filament-panels::page>
```

### Mengakses State

```blade
<!-- Formatting -->
<span x-text="formatDate(selectedDate)"></span>

<!-- Filtering -->
<template x-for="indicator in filteredIndicators">
    <!-- content -->
</template>

<!-- Method calls -->
<button @click="openSlideOverFast(indicatorId, selectedDate)"></button>

<!-- Computed properties -->
<span x-show="isMobile"></span>
```

## Benefits Refactoring

✅ **Modular**: Setiap concern terpisah dalam component sendiri  
✅ **Readable**: Code lebih mudah dibaca dan dimengerti  
✅ **Maintainable**: Lebih mudah menemukan dan memperbaiki bugs  
✅ **Reusable**: Stores bisa di-reuse di halaman lain  
✅ **Testable**: Logika terpisah lebih mudah untuk di-test  
✅ **Performance**: Lazy loading dan batch processing built-in  

## Migration Steps

1. Copy `stores/` directory ke `partials/components/`
2. Copy `public/js/dashboard-utils.js` ke project
3. Include dashboard-state di page utama
4. Update existing page menggunakan new template
5. Test semua functionality

## Debugging Tips

### Check Alpine State
```javascript
// Di console browser
document.querySelector('[x-data*="selectedDate"]).__x.getUnobservedData()
```

### Monitor Loading States
```html
<div x-show="isDateLoading">Loading...</div>
<div x-show="slideOverLoading">Opening...</div>
```

### Batch Loading Debug
- Check `reportCountsLoading` untuk lihat progress
- Batch size configurable di indicators-loader.blade.php
- Default batch size: 5

## Future Improvements

- [ ] Extract more utilities ke helper functions
- [ ] Add comprehensive error handling
- [ ] Create reusable modal components
- [ ] Add unit tests untuk utilities
- [ ] Consider moving ke Alpine plugin system
- [ ] Add loading state progress bar
