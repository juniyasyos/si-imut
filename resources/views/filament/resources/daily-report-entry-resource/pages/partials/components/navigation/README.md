# Navigation Components

## Components

### `date-navigation.blade.php`
Calendar picker dan month navigation sidebar.

**Features:**
- Monthly calendar display
- Date selection
- Today highlighting
- Weekend styling

### `month-navigation.blade.php`
Month selector navigation controls.

**Features:**
- Previous/Next month buttons
- Current month display
- Year navigation

### `date-header.blade.php`
Selected date header display dengan informasi tanggal dan indicator count.

**Features:**
- Selected date display
- Indicator count for date
- Formatted date display (Indonesian)

**Usage:**
```php
@include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-navigation')
```