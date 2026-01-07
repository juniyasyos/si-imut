# Daily Report Entry Components

Struktur folder yang terorganisir untuk komponen-komponen Daily Report Entry.

## 📁 Struktur Folder

### `/header/`
- `header-section.blade.php` - Main page header dengan judul dan navigasi
- `filters-section.blade.php` - Filters dan search functionality

### `/navigation/`  
- `date-navigation.blade.php` - Calendar picker dan month navigation
- `month-navigation.blade.php` - Month selector navigation
- `date-header.blade.php` - Selected date header display

### `/indicators/`
- `desktop-indicator-card.blade.php` - Desktop view untuk indicator cards
- `indicators-empty-state.blade.php` - Empty state ketika tidak ada indicators
- `action-buttons.blade.php` - Action buttons untuk indicators
- `status-indicator.blade.php` - Status indicator display

### `/monitoring/`
- `monitoring-view.blade.php` - Main monitoring matrix view
- `alpine-matrix.blade.php` - Alpine.js matrix implementation
- `matrix-cell.blade.php` - Individual matrix cell component
- `table-header.blade.php` - Table header untuk monitoring
- `legend.blade.php` - Legend untuk status colors
- `empty-state.blade.php` - Empty state untuk monitoring

### `/modal/`
- `slide-over.blade.php` - Slide-over modal untuk form input

### `/mobile/`
- `mobile-indicator-card.blade.php` - Mobile view untuk indicator cards
- `mobile-action-buttons.blade.php` - Mobile action buttons
- `mobile-card.blade.php` - Mobile card layout
- `mobile-status-cards.blade.php` - Mobile status cards

### `/scripts/`
- `scripts-styles.blade.php` - JavaScript dan CSS styles

## 🎯 Tujuan Organisasi

1. **Maintainability** - Mudah untuk maintain dan debug
2. **Readability** - Struktur yang jelas dan mudah dipahami  
3. **Reusability** - Komponen bisa digunakan ulang
4. **Modularity** - Setiap komponen punya tanggung jawab yang jelas
5. **Scalability** - Mudah untuk menambah komponen baru

## 📝 Usage

Semua komponen diinclude dari file utama `list-daily-report-entries-original.blade.php` menggunakan path yang terstruktur.

Example:
```php
@include('filament.resources.daily-report-entry-resource.pages.partials.components.header.header-section')
```