# Quick Reference: IMUT Data Notes Feature

## Database Schema

### Table: `imut_data_notes`
```sql
CREATE TABLE imut_data_notes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    imut_data_id BIGINT NOT NULL,
    note_name VARCHAR(255) NOT NULL,
    period_start DATE NULL,
    period_end DATE NULL,
    related_laporan_ids JSON NULL,
    recommendation TEXT NULL,
    analysis TEXT NULL,
    additional_notes TEXT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (imut_data_id) REFERENCES imut_data(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_imut_data_id (imut_data_id)
);
```

## Model Usage

### Creating a Note
```php
use App\Models\ImutDataNote;
use Illuminate\Support\Facades\Auth;

ImutDataNote::create([
    'imut_data_id' => 1,
    'note_name' => 'Catatan Penting Bulan November',
    'period_start' => '2025-11-01',
    'period_end' => '2025-11-30',
    'related_laporan_ids' => [1, 2, 3], // Array of laporan IDs
    'recommendation' => 'Tingkatkan kualitas input data',
    'analysis' => 'Data menunjukkan peningkatan 15%',
    'additional_notes' => 'Follow up dengan unit terkait',
    'priority' => 'high',
    'is_active' => true,
    'created_by' => Auth::id(),
]);
```

### Querying Notes
```php
// Get all notes for specific IMUT Data
$notes = ImutDataNote::forImutData($imutDataId)->get();

// Get only active notes
$activeNotes = ImutDataNote::forImutData($imutDataId)->active()->get();

// Get notes by priority
$highPriorityNotes = ImutDataNote::forImutData($imutDataId)
    ->byPriority('high')
    ->get();

// Get notes with creator information
$notes = ImutDataNote::with('creator')->get();

// Get laporan names
$note = ImutDataNote::find(1);
echo $note->laporan_names; // Returns comma-separated laporan names
```

### Relationship Access
```php
// From ImutData to Notes
$imutData = ImutData::find(1);
$notes = $imutData->notes;

// From Note to ImutData
$note = ImutDataNote::find(1);
$imutData = $note->imutData;

// From Note to Creator
$creator = $note->creator;
```

## Widget Configuration

### Adding Widget to a Page
```php
public function getFooterWidgets(): array
{
    return [
        \App\Filament\Resources\ImutDataResource\Widgets\ImutDataNotesReport::make([
            'imutDataId' => $this->imutData?->id
        ]),
    ];
}
```

### Widget Properties
- `imutDataId`: Required - ID of the IMUT Data to show notes for
- `columnSpan`: Optional - Default is 'full'

## Form Fields

### Required Fields
- `note_name`: String (max 255)
- `priority`: Enum (low, medium, high)
- `created_by`: User ID (auto-populated)

### Optional Fields
- `period_start`: Date
- `period_end`: Date
- `related_laporan_ids`: Array of integers
- `recommendation`: Text
- `analysis`: Text
- `additional_notes`: Text
- `is_active`: Boolean (default: true)

## Priority Levels

| Priority | Color  | Use Case |
|----------|--------|----------|
| `high`   | Red    | Critical issues, urgent actions needed |
| `medium` | Yellow | Important but not urgent |
| `low`    | Green  | General information, nice-to-have |

## Common Queries

### Get Notes for Current Period
```php
$currentMonth = now()->startOfMonth();
$currentMonthEnd = now()->endOfMonth();

$notes = ImutDataNote::forImutData($imutDataId)
    ->whereBetween('period_start', [$currentMonth, $currentMonthEnd])
    ->get();
```

### Get Notes for Specific Laporan
```php
$laporanId = 1;
$notes = ImutDataNote::forImutData($imutDataId)
    ->whereJsonContains('related_laporan_ids', $laporanId)
    ->get();
```

### Get Recent Notes
```php
$recentNotes = ImutDataNote::forImutData($imutDataId)
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();
```

### Count by Priority
```php
$counts = [
    'high' => ImutDataNote::forImutData($imutDataId)->byPriority('high')->count(),
    'medium' => ImutDataNote::forImutData($imutDataId)->byPriority('medium')->count(),
    'low' => ImutDataNote::forImutData($imutDataId)->byPriority('low')->count(),
];
```

## Artisan Commands

### Run Migration
```bash
php artisan migrate
```

### Rollback Migration
```bash
php artisan migrate:rollback --step=1
```

### Seed Sample Data
```bash
php artisan db:seed --class=ImutDataNoteSeeder
```

### Clear Cached Data
```bash
php artisan cache:clear
php artisan view:clear
```

## Troubleshooting

### Notes Not Showing
1. Check if `imutDataId` is correctly passed to widget
2. Verify migration has been run: `php artisan migrate:status`
3. Check if there are notes in database: `SELECT COUNT(*) FROM imut_data_notes;`

### Permission Errors
1. Ensure user has proper permissions
2. Check Policy settings in `ImutDataNotePolicy.php`
3. Verify Shield permissions are configured

### Form Validation Errors
- `note_name` is required (max 255 chars)
- `period_end` must be after or equal to `period_start`
- `priority` must be one of: low, medium, high
- `related_laporan_ids` must be valid laporan IDs

## File Locations

| Component | Path |
|-----------|------|
| Migration | `database/migrations/2025_11_10_233330_create_imut_data_notes_table.php` |
| Model | `app/Models/ImutDataNote.php` |
| Widget | `app/Filament/Resources/ImutDataResource/Widgets/ImutDataNotesReport.php` |
| View Detail | `resources/views/filament/resources/imut-data-resource/widgets/note-detail.blade.php` |
| Page | `app/Filament/Resources/ImutDataResource/Pages/SummaryDiagram.php` |
| Blade View | `resources/views/filament/resources/imut-data-resource/pages/summary-imut-data-diagram.blade.php` |
| Seeder | `database/seeders/ImutDataNoteSeeder.php` |
| Policy | `app/Policies/ImutDataNotePolicy.php` |

## Testing

### Manual Testing Checklist
- [ ] Can create new note
- [ ] Can view note details
- [ ] Can edit existing note
- [ ] Can delete note
- [ ] Can filter by priority
- [ ] Can filter by status
- [ ] Tabs switch properly
- [ ] Dates display correctly
- [ ] Laporan names display correctly
- [ ] Priority badges show correct colors
- [ ] Pagination works
- [ ] Search works
- [ ] Bulk delete works

### Unit Test Example
```php
use App\Models\ImutDataNote;
use App\Models\ImutData;
use Tests\TestCase;

class ImutDataNoteTest extends TestCase
{
    public function test_can_create_note()
    {
        $imutData = ImutData::factory()->create();
        
        $note = ImutDataNote::create([
            'imut_data_id' => $imutData->id,
            'note_name' => 'Test Note',
            'priority' => 'high',
            'created_by' => 1,
        ]);
        
        $this->assertDatabaseHas('imut_data_notes', [
            'note_name' => 'Test Note',
        ]);
    }
}
```

## Best Practices

1. **Always provide meaningful note names** - Make them descriptive
2. **Use periods wisely** - Set accurate start and end dates
3. **Link to relevant laporans** - Helps in tracking and reporting
4. **Write clear recommendations** - Be specific and actionable
5. **Set appropriate priority** - Use high priority sparingly
6. **Keep notes active** - Mark as inactive only when truly obsolete
7. **Add context in additional notes** - Include any relevant information

## Support

For issues or questions:
1. Check the full documentation: `docs/imut-data-notes-feature.md`
2. Review error logs: `storage/logs/laravel.log`
3. Check database: Use phpMyAdmin or `php artisan tinker`
