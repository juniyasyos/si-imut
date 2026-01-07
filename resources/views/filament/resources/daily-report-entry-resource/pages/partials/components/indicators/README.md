# Indicator Components

## Components

### `desktop-indicator-card.blade.php`
Desktop view untuk indicator cards dengan full information.

**Features:**
- Indicator title and description
- Category badge
- Status indicator
- Action buttons
- Compliance preview

### `indicators-empty-state.blade.php`
Empty state ketika tidak ada indicators tersedia.

**Features:**
- Empty illustration
- Contextual message
- Call-to-action

### `action-buttons.blade.php`
Action buttons untuk indicators based pada status.

**Features:**
- Dynamic button states
- Status-based actions
- Responsive design

### `status-indicator.blade.php`
Status indicator display dengan color coding.

**Features:**
- Status badges
- Color-coded states
- Tooltip information

**Usage:**
```php
@include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.desktop-indicator-card')
```