# Filament Abstraction Layer Documentation

## Overview

This document describes the comprehensive abstraction layer created to decouple Filament UI from business logic, ensuring that Filament upgrades won't break core functionality.

## Architecture Components

### 1. Command Pattern (`app/Commands/`)

The Command Pattern encapsulates business operations as objects, making them UI-agnostic and testable.

#### Base Classes:
- `BaseCommand`: Foundation for all commands with validation
- `CommandInterface`: Contract for all commands
- `QueryCommandInterface`: Contract for data retrieval commands
- `MutationCommandInterface`: Contract for data modification commands

#### LaporanImut Commands:
- `CreateLaporanImutCommand`: Handles laporan creation
- `UpdateLaporanImutCommand`: Handles laporan updates
- `DeleteLaporanImutCommand`: Handles laporan deletion
- `GetLaporanImutListCommand`: Handles laporan querying with filters

#### Usage Example:
```php
// Create a new laporan
$data = [
    'name' => 'Test Laporan',
    'status' => 'process',
    'assessment_period_start' => '2024-01-01',
    'assessment_period_end' => '2024-01-31',
    'created_by' => auth()->id(),
];

$laporan = CreateLaporanImutCommand::createWithValidation($data);

// Update existing laporan
$updatedLaporan = UpdateLaporanImutCommand::updateWithValidation($laporan->id, [
    'status' => 'complete'
]);

// Delete laporan
$success = DeleteLaporanImutCommand::deleteById($laporan->id);
```

### 2. Adapter Pattern (`app/Adapters/Filament/`)

The Adapter Pattern bridges Filament UI with our business logic, translating between UI requirements and business operations.

#### Main Adapter:
- `LaporanImutFilamentAdapter`: Primary adapter for LaporanImut operations

#### Key Methods:
- `getTableQuery()`: Returns Eloquent Builder for Filament tables
- `getFormData()`: Prepares data for Filament forms
- `createRecord()`: Handles record creation from Filament
- `updateRecord()`: Handles record updates from Filament
- `deleteRecord()`: Handles record deletion from Filament
- `getWidgetData()`: Provides data for Filament widgets

#### Usage Example:
```php
$adapter = app(LaporanImutFilamentAdapter::class);

// Get table query with filters
$query = $adapter->getTableQuery([
    ['field' => 'status', 'value' => 'process']
]);

// Get form data for editing
$formData = $adapter->getFormData($laporan);

// Create record from form submission
$newRecord = $adapter->createRecord($formData);
```

### 3. Facade Pattern (`app/Facades/`)

The Facade Pattern provides a simple, unified interface to complex business logic subsystems.

#### Main Facade:
- `LaporanImutFacade`: Simple interface for LaporanImut operations

#### Usage Example:
```php
use App\Facades\LaporanImutFacade;

// Create laporan
$laporan = LaporanImutFacade::create($data);

// Get paginated list
$list = LaporanImutFacade::list($filters, $sorting, $page, $perPage);

// Get widget data
$widgetData = LaporanImutFacade::getWidgetData(['laporan_id' => $laporan->id]);
```

### 4. Traits (`app/Traits/Filament/`)

Traits provide reusable functionality for Filament resources to integrate with business logic.

#### Main Trait:
- `UsesBusinessLogic`: Integrates business logic into Filament resources

#### Usage Example:
```php
class LaporanImutResource extends Resource
{
    use UsesBusinessLogic;

    protected function getBusinessAdapterClass(): string
    {
        return LaporanImutFilamentAdapter::class;
    }

    // Filament will now use business logic automatically
}
```

### 5. Custom Validation Rules (`app/Rules/`)

Custom validation rules ensure data integrity while remaining UI-agnostic.

#### Available Rules:
- `UniqueAssessmentPeriod`: Validates non-overlapping assessment periods
- `QualityRange`: Validates quality metrics within acceptable ranges
- `ValidFormula`: Validates mathematical formula syntax

#### Usage Example:
```php
use App\Rules\UniqueAssessmentPeriod;
use App\Rules\QualityRange;

$rules = [
    'assessment_period_start' => ['required', 'date', new UniqueAssessmentPeriod()],
    'target_value' => ['required', 'numeric', new QualityRange(0, 100, 'percentage')],
];
```

## Integration Guide

### Step 1: Create Commands for Your Entity

```php
// app/Commands/YourEntity/CreateYourEntityCommand.php
class CreateYourEntityCommand extends BaseCommand implements MutationCommandInterface
{
    public function __construct(
        private YourEntityRepositoryInterface $repository,
        private YourEntityFactory $factory
    ) {
        $this->setValidationRules([
            'name' => 'required|string|max:255',
            // ... other rules
        ]);
    }

    public function execute(): YourEntity
    {
        return $this->factory->create($this->data);
    }
}
```

### Step 2: Create Filament Adapter

```php
// app/Adapters/Filament/YourEntityFilamentAdapter.php
class YourEntityFilamentAdapter implements FilamentResourceAdapterInterface
{
    public function getTableQuery(array $filters = [], array $sorting = []): Builder
    {
        // Return query builder with applied filters and sorting
    }

    public function createRecord(array $data)
    {
        return CreateYourEntityCommand::createWithValidation($data);
    }

    // ... implement other interface methods
}
```

### Step 3: Update Your Filament Resource

```php
// app/Filament/Resources/YourEntityResource.php
class YourEntityResource extends Resource
{
    use UsesBusinessLogic;

    protected function getBusinessAdapterClass(): string
    {
        return YourEntityFilamentAdapter::class;
    }

    // Filament methods will automatically use business logic
}
```

### Step 4: Create Widget Using Business Logic

```php
// app/Filament/Widgets/YourEntityWidget.php
class YourEntityWidget extends BusinessLogicWidget
{
    protected function getBusinessAdapterClass(): string
    {
        return YourEntityFilamentAdapter::class;
    }

    protected function getData(): array
    {
        return $this->getWidgetData(['entity_id' => $this->record->id]);
    }
}
```

## Benefits

### 1. UI Framework Independence
- Business logic is completely separated from Filament
- Easy to switch UI frameworks or upgrade Filament
- Logic can be reused in API endpoints, console commands, etc.

### 2. Testability
- Commands can be unit tested without UI framework
- Business logic is isolated and mockable
- Validation rules are testable independently

### 3. Maintainability
- Clear separation of concerns
- Single responsibility principle applied
- Easy to modify business logic without affecting UI

### 4. Reusability
- Commands can be used in multiple contexts
- Adapters can be extended for different UI needs
- Facades provide simple access from anywhere

## Testing Strategy

### Unit Tests
```php
// Test commands directly
test('can create entity with valid data', function () {
    $data = ['name' => 'Test Entity'];
    $entity = CreateEntityCommand::createWithValidation($data);
    expect($entity->name)->toBe('Test Entity');
});

// Test adapters
test('adapter creates record through command', function () {
    $adapter = app(EntityFilamentAdapter::class);
    $record = $adapter->createRecord(['name' => 'Test']);
    expect($record)->toBeInstanceOf(Entity::class);
});
```

### Integration Tests
```php
// Test Filament integration
test('resource creates record using business logic', function () {
    $data = ['name' => 'Test Entity'];
    
    // Simulate Filament form submission
    $resource = new EntityResource();
    $record = $resource->handleRecordCreation($data);
    
    expect($record->name)->toBe('Test Entity');
});
```

## Migration Strategy

### For Existing Resources:

1. **Identify Business Logic**: Extract any business logic from Filament resources
2. **Create Commands**: Move logic into command classes
3. **Create Adapter**: Bridge commands with Filament requirements
4. **Update Resource**: Use the `UsesBusinessLogic` trait
5. **Test**: Ensure everything works as before

### Example Migration:

Before:
```php
// Filament Resource with embedded business logic
class LaporanImutResource extends Resource
{
    public static function create(array $data): Model
    {
        // Business logic mixed with UI code
        $laporan = new LaporanImut();
        $laporan->fill($data);
        $laporan->slug = Str::slug($data['name']);
        $laporan->save();
        
        if (!empty($data['unit_kerja_ids'])) {
            $laporan->unitKerjas()->sync($data['unit_kerja_ids']);
        }
        
        return $laporan;
    }
}
```

After:
```php
// Clean separation using abstraction layer
class LaporanImutResource extends Resource
{
    use UsesBusinessLogic;

    protected function getBusinessAdapterClass(): string
    {
        return LaporanImutFilamentAdapter::class;
    }
    
    // Business logic is now handled by commands through the adapter
}
```

## Performance Considerations

### Caching
- Commands can implement caching where appropriate
- Adapters can cache frequently accessed data
- Use cache tags for efficient invalidation

### Database Optimization
- Commands use repositories for consistent data access
- Query optimization handled in repository layer
- Eager loading configured in adapters

### Memory Management
- Commands are lightweight and stateless
- Adapters use dependency injection efficiently
- Facades provide lazy loading of services

## Future Enhancements

### 1. Event Sourcing
Commands can be extended to support event sourcing:
```php
class CreateLaporanImutCommand extends BaseCommand implements EventSourcedCommand
{
    public function execute(): LaporanImut
    {
        $laporan = $this->factory->create($this->data);
        
        // Dispatch domain events
        $this->dispatchEvent(new LaporanImutCreated($laporan));
        
        return $laporan;
    }
}
```

### 2. CQRS (Command Query Responsibility Segregation)
Separate read and write models:
```php
// Write model
class CreateLaporanImutCommand extends BaseCommand { }

// Read model
class LaporanImutQueryService 
{
    public function getForListing(array $filters): Collection { }
    public function getForWidget(int $id): array { }
}
```

### 3. API Integration
Commands can be easily exposed via API:
```php
// API Controller
class LaporanImutApiController extends Controller
{
    public function store(Request $request)
    {
        return CreateLaporanImutCommand::createWithValidation($request->all());
    }
}
```

This abstraction layer ensures your application is future-proof, maintainable, and easily testable while keeping Filament as a thin UI layer on top of robust business logic.
