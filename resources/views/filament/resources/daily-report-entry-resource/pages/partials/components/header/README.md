# Header Components

## Components

### `header-section.blade.php`
Main page header dengan judul dan navigasi tab antara Input dan Monitoring view.

**Features:**
- Page title display
- Tab navigation (Input/Monitoring)
- Responsive design

### `filters-section.blade.php` 
Filters dan search functionality untuk indicators.

**Features:**
- Search input field
- Status filter dropdown
- Category filters
- Real-time filtering

**Usage:**
```php
@include('filament.resources.daily-report-entry-resource.pages.partials.components.header.header-section')
```