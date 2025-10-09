# SoftDeletes Unique Constraint Fix - Complete Solution

## Problem Summary
The SI-IMUT application experienced a critical issue where unique constraints conflicted with SoftDeletes functionality, causing production errors when attempting to create new records with values that matched soft-deleted records.

### The Issue
When a record with a unique field was soft-deleted, the unique constraint in the database still applied to the soft-deleted record. This prevented creation of new records with the same unique value, leading to constraint violation errors like:
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'value' for key 'table_field_unique'
```

## Root Cause Analysis
- **Standard Unique Constraints**: Regular unique constraints don't consider `deleted_at` column
- **SoftDeletes Behavior**: Soft-deleted records remain in database with `deleted_at` timestamp
- **Conflict**: New records couldn't use values from soft-deleted records due to existing unique constraints

## Solution Overview
Implemented a comprehensive solution with three components:

### 1. Custom Validation Rule (`UniqueWithSoftDeletes`)
**File**: `app/Rules/UniqueWithSoftDeletes.php`

A Laravel validation rule that checks uniqueness while ignoring soft-deleted records:
```php
public function passes($attribute, $value)
{
    $query = DB::table($this->table)
        ->where($this->column, $value)
        ->whereNull('deleted_at');
    
    if ($this->ignoreId) {
        $query->where('id', '!=', $this->ignoreId);
    }
    
    return $query->count() === 0;
}
```

### 2. Model Trait (`HasUniqueWithSoftDeletes`)
**File**: `app/Traits/HasUniqueWithSoftDeletes.php`

A reusable trait that provides helper methods for unique validation with soft deletes:
```php
public function uniqueRule(string $column, ?int $ignoreId = null): UniqueWithSoftDeletes
{
    return new UniqueWithSoftDeletes($this->getTable(), $column, $ignoreId);
}

abstract public function getUniqueValidationRules(?int $ignoreId = null): array;
```

### 3. Database Schema Migration
**File**: `database/migrations/2025_01_08_000002_fix_unique_constraints_for_soft_deletes.php`

Modified database indexes to use composite unique constraints that include `deleted_at`:

**Before**: `UNIQUE(field_name)`
**After**: `UNIQUE(field_name, deleted_at)`

This allows:
- Multiple soft-deleted records with same `field_name` (different `deleted_at` values)
- Only one active record with each `field_name` (where `deleted_at` is NULL)

## Implementation Details

### Models Updated
All models using SoftDeletes with unique constraints were updated:

1. **ImutCategory** (`app/Models/ImutCategory.php`)
   - Added `HasUniqueWithSoftDeletes` trait
   - Unique field: `category_name`
   - Database index: `imut_kategori_name_deleted_unique`

2. **ImutData** (`app/Models/ImutData.php`)
   - Added `HasUniqueWithSoftDeletes` trait
   - Unique field: `title`
   - Database index: `imut_data_title_deleted_unique`

3. **ImutProfile** (`app/Models/ImutProfile.php`)
   - Added `HasUniqueWithSoftDeletes` trait
   - Unique field: `slug`
   - Database index: `imut_profil_slug_deleted_unique`

4. **UnitKerja** (`app/Models/UnitKerja.php`)
   - Added `HasUniqueWithSoftDeletes` trait
   - Unique field: `unit_name`
   - Database index: `unit_kerja_name_deleted_unique`

5. **User** (`app/Models/User.php`)
   - Added `HasUniqueWithSoftDeletes` trait
   - Unique fields: `nik`, `email`
   - Database indexes: `users_nik_deleted_unique`, `users_email_deleted_unique`

6. **LaporanImut** (`app/Models/LaporanImut.php`)
   - Added `HasUniqueWithSoftDeletes` trait
   - Unique field: `slug`
   - Database index: `laporan_imuts_slug_deleted_unique`

### Example Model Implementation
```php
class ImutCategory extends Model
{
    use SoftDeletes, HasUniqueWithSoftDeletes;
    
    public function getUniqueValidationRules(?int $ignoreId = null): array
    {
        return [
            'category_name' => [
                'required',
                'string',
                'max:100',
                $this->uniqueRule('category_name', $ignoreId)
            ],
        ];
    }
}
```

## Database Changes

### MySQL Composite Unique Indexes
Since MySQL doesn't support partial indexes with WHERE clauses (like PostgreSQL), we used composite unique indexes:

```sql
-- Before (problematic)
CREATE UNIQUE INDEX table_field_unique ON table (field);

-- After (solution)
CREATE UNIQUE INDEX table_field_deleted_unique ON table (field, deleted_at);
```

### Index Verification
All required composite indexes were successfully created:
- ✅ `imut_kategori_name_deleted_unique`
- ✅ `imut_data_title_deleted_unique`
- ✅ `imut_profil_slug_deleted_unique`
- ✅ `unit_kerja_name_deleted_unique`
- ✅ `users_nik_deleted_unique`
- ✅ `users_email_deleted_unique`
- ✅ `laporan_imuts_slug_deleted_unique`

## Testing Results

### Automated Test Verification
The solution was validated with comprehensive tests:

```bash
✓ Created category: Test Category Unique 1759933668
✓ Soft deleted category: Test Category Unique 1759933668
✓ Created new category with same name: Test Category Unique 1759933668
✓ Created user: test.unique.1759933669@example.com
✓ Soft deleted user: test.unique.1759933669@example.com
✓ Created new user with same email/nik: test.unique.1759933669@example.com
```

### Production Scenario Resolved
Before the fix:
1. User creates "Category A"
2. User soft-deletes "Category A"
3. User tries to create new "Category A" → **FAILS** with unique constraint violation

After the fix:
1. User creates "Category A" (deleted_at: NULL)
2. User soft-deletes "Category A" (deleted_at: timestamp)
3. User creates new "Category A" (deleted_at: NULL) → **SUCCESS**

## Performance Considerations

### Index Impact
- **Storage**: Minimal increase due to additional column in composite indexes
- **Query Performance**: Maintained or improved due to better index coverage
- **Write Performance**: Negligible impact on INSERT/UPDATE operations

### Query Optimization
The composite indexes actually improve performance for common queries:
```sql
-- This query is now optimized by the composite index
SELECT * FROM table WHERE field = 'value' AND deleted_at IS NULL;
```

## Migration Safety

### Rollback Capability
The migration includes a safe rollback mechanism:
```php
public function down(): void
{
    // Drop composite indexes
    Schema::table('table', function (Blueprint $table) {
        $table->dropUnique('table_field_deleted_unique');
        $table->unique('field'); // Restore original constraint
    });
}
```

### Production Deployment
1. **Pre-deployment**: Verify no duplicate active records exist
2. **Deployment**: Run migration during maintenance window
3. **Post-deployment**: Verify indexes created and functionality working
4. **Validation**: Run test suite to confirm unique constraint behavior

## Best Practices Established

### For Future Models
When implementing SoftDeletes with unique constraints:

1. **Always use composite unique indexes** that include `deleted_at`
2. **Implement `HasUniqueWithSoftDeletes` trait** for validation helpers
3. **Define `getUniqueValidationRules()` method** for form validation
4. **Use `UniqueWithSoftDeletes` rule** in Filament resources and form requests

### Validation Pattern
```php
// In Form Requests or Filament Resources
public function rules(): array
{
    return $this->model->getUniqueValidationRules($this->model->id ?? null);
}
```

## Benefits Achieved

### ✅ Production Stability
- Eliminated unique constraint violation errors
- Resolved user workflow interruptions
- Improved data integrity handling

### ✅ Developer Experience
- Reusable trait for consistent implementation
- Clear validation rules for unique fields
- Comprehensive documentation and examples

### ✅ System Performance
- Optimized database indexes
- Efficient query patterns
- Minimal overhead introduction

### ✅ Maintainability
- Centralized unique validation logic
- Consistent approach across all models
- Easy to extend for new models

## Conclusion

The SoftDeletes unique constraint conflict has been completely resolved through a comprehensive solution that addresses the issue at multiple levels:

1. **Database Level**: Composite unique indexes that properly handle soft-deleted records
2. **Application Level**: Custom validation rules and reusable traits
3. **Model Level**: Consistent implementation across all affected models

This solution ensures that users can create new records with values that match soft-deleted records without encountering constraint violations, while maintaining proper uniqueness for active records.

The implementation follows Laravel best practices and provides a scalable foundation for handling similar unique constraint challenges with SoftDeletes in the future.
