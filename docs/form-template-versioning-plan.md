# 📋 Form Template Versioning Development Plan

## 🎯 Tujuan
Mengimplementasikan sistem versioning untuk form template dimana:
- Setiap profile dapat memiliki multiple versi form template
- Hanya 1 form template yang aktif pada waktu tertentu
- History versioning dapat dikelola dengan baik

## 📊 Analisis Struktur Saat Ini

### Current Database Structure
```
ImutProfile (1) -----> (N) FormTemplate
    - id                   - id
    - version              - imut_profile_id (FK)
    - valid_from           - title
    - valid_until          - description
    - ...                  - ...
```

### Current Issues
1. FormTemplate terikat langsung ke `imut_profile_id`
2. Tidak ada versioning system untuk form template
3. Tidak ada konsep "active" form template per profile

## 🚀 Development Plan

### Phase 1: Database Schema Enhancement

#### 1.1 Modify FormTemplate Structure
Tambah kolom untuk versioning:
```sql
ALTER TABLE form_templates ADD COLUMN version VARCHAR(50) DEFAULT 'v1.0';
ALTER TABLE form_templates ADD COLUMN is_active BOOLEAN DEFAULT false;
ALTER TABLE form_templates ADD COLUMN valid_from DATE;
ALTER TABLE form_templates ADD COLUMN valid_until DATE;
ALTER TABLE form_templates ADD COLUMN created_by_user_id BIGINT UNSIGNED;
ALTER TABLE form_templates ADD COLUMN parent_template_id BIGINT UNSIGNED NULL;

-- Add indexes
ALTER TABLE form_templates ADD INDEX idx_profile_active (imut_profile_id, is_active);
ALTER TABLE form_templates ADD INDEX idx_profile_version (imut_profile_id, version);

-- Add foreign keys
ALTER TABLE form_templates ADD FOREIGN KEY (created_by_user_id) REFERENCES users(id);
ALTER TABLE form_templates ADD FOREIGN KEY (parent_template_id) REFERENCES form_templates(id);
```

#### 1.2 Add Validation Constraints
```sql
-- Ensure only one active template per profile
CREATE UNIQUE INDEX uk_one_active_per_profile 
ON form_templates (imut_profile_id) 
WHERE is_active = true;
```

### Phase 2: Model Enhancement

#### 2.1 Update FormTemplate Model
```php
// app/Models/FormTemplate.php

class FormTemplate extends Model
{
    protected $fillable = [
        'imut_profile_id',
        'version',
        'is_active',
        'valid_from', 
        'valid_until',
        'created_by_user_id',
        'parent_template_id',
        'title',
        'description',
        'compliance_method',
        'auto_fail_on_critical',
        'scoring_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'auto_fail_on_critical' => 'boolean',
        'scoring_config' => 'array',
    ];

    // Boot method untuk validasi
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            if ($model->is_active) {
                $model->validateSingleActiveTemplate();
            }
            
            if (empty($model->version)) {
                $model->version = $model->generateNextVersion();
            }
        });
    }

    // Relasi
    public function parentTemplate()
    {
        return $this->belongsTo(FormTemplate::class, 'parent_template_id');
    }

    public function childTemplates()
    {
        return $this->hasMany(FormTemplate::class, 'parent_template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProfile($query, $profileId)
    {
        return $query->where('imut_profile_id', $profileId);
    }

    // Methods
    public function validateSingleActiveTemplate()
    {
        $existing = static::where('imut_profile_id', $this->imut_profile_id)
            ->where('is_active', true)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($existing) {
            throw new \Exception(
                'Only one form template can be active per profile at a time. ' .
                'Please deactivate the current active template first.'
            );
        }
    }

    public function generateNextVersion()
    {
        $lastVersion = static::where('imut_profile_id', $this->imut_profile_id)
            ->orderBy('version', 'desc')
            ->first();

        if (!$lastVersion) {
            return 'v1.0';
        }

        // Extract version number and increment
        preg_match('/v(\d+)\.(\d+)/', $lastVersion->version, $matches);
        $major = $matches[1] ?? 1;
        $minor = ($matches[2] ?? 0) + 1;

        return "v{$major}.{$minor}";
    }

    public function activate()
    {
        DB::transaction(function () {
            // Deactivate other templates for this profile
            static::where('imut_profile_id', $this->imut_profile_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            // Activate this template
            $this->update(['is_active' => true]);
        });
    }

    public function createNewVersion(array $data = [])
    {
        return DB::transaction(function () use ($data) {
            // Load current template with relationships
            $this->load('formFields.options');

            // Create new template
            $newTemplate = $this->replicate();
            $newTemplate->parent_template_id = $this->id;
            $newTemplate->version = $this->generateNextVersion();
            $newTemplate->is_active = false;
            $newTemplate->created_by_user_id = auth()->id();

            // Override with provided data
            foreach ($data as $key => $value) {
                $newTemplate->$key = $value;
            }

            $newTemplate->save();

            // Replicate form fields
            $this->formFields->each(function ($field) use ($newTemplate) {
                $newField = $field->replicate();
                $newField->form_template_id = $newTemplate->id;
                $newField->save();

                // Replicate field options
                $field->options->each(function ($option) use ($newField) {
                    $newOption = $option->replicate();
                    $newOption->enhanced_form_field_id = $newField->id;
                    $newOption->save();
                });
            });

            return $newTemplate;
        });
    }
}
```

#### 2.2 Update ImutProfile Model
```php
// app/Models/ImutProfile.php

// Add new relationships
public function activeFormTemplate()
{
    return $this->hasOne(FormTemplate::class)->where('is_active', true);
}

public function formTemplateVersions()
{
    return $this->hasMany(FormTemplate::class)->orderBy('version', 'desc');
}

public function latestFormTemplate()
{
    return $this->hasOne(FormTemplate::class)->latest('created_at');
}
```

### Phase 3: UI/UX Enhancement

#### 3.1 Form Template Version Management Interface
- List semua versi form template untuk profile
- Tombol untuk create new version
- Tombol untuk activate/deactivate version
- Version comparison interface
- History tracking

#### 3.2 Enhanced RelationManager
```php
// Update ProfilesRelationManager.php
public function table(Table $table): Table
{
    return $table
        // ... existing columns ...
        
        TextColumn::make('active_form_template.version')
            ->label('Active Template Version')
            ->getStateUsing(fn($record) => $record->activeFormTemplate?->version ?? 'No Active Template')
            ->badge()
            ->color(fn(string $state): string => $state === 'No Active Template' ? 'warning' : 'success'),

        TextColumn::make('form_template_versions_count')
            ->label('Total Versions')
            ->counts('formTemplateVersions')
            ->badge();
}
```

### Phase 4: Business Logic Enhancement

#### 4.1 Form Template Lifecycle Management
```php
// app/Services/FormTemplateVersionService.php

class FormTemplateVersionService
{
    public function createNewVersion(FormTemplate $baseTemplate, array $data = [])
    {
        return $baseTemplate->createNewVersion($data);
    }

    public function activateVersion(FormTemplate $template)
    {
        return $template->activate();
    }

    public function getVersionHistory(int $profileId)
    {
        return FormTemplate::forProfile($profileId)
            ->with(['createdBy', 'parentTemplate'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function compareVersions(FormTemplate $version1, FormTemplate $version2)
    {
        // Implementation for version comparison
    }
}
```

#### 4.2 Migration Strategy
```php
// database/migrations/YYYY_MM_DD_add_versioning_to_form_templates.php

public function up()
{
    Schema::table('form_templates', function (Blueprint $table) {
        $table->string('version', 50)->default('v1.0');
        $table->boolean('is_active')->default(false);
        $table->date('valid_from')->nullable();
        $table->date('valid_until')->nullable();
        $table->foreignId('created_by_user_id')->nullable()->constrained('users');
        $table->foreignId('parent_template_id')->nullable()->constrained('form_templates');

        $table->index(['imut_profile_id', 'is_active']);
        $table->index(['imut_profile_id', 'version']);
    });

    // Set existing templates as v1.0 and active
    DB::table('form_templates')->update([
        'version' => 'v1.0',
        'is_active' => true,
        'created_by_user_id' => 1, // Or appropriate default user
    ]);
}
```

## 📋 Implementation Checklist

### Database & Models
- [ ] Create migration for form template versioning
- [ ] Update FormTemplate model with versioning logic
- [ ] Add validation constraints
- [ ] Update ImutProfile relationships
- [ ] Create FormTemplateVersionService

### UI/UX
- [ ] Create version management interface
- [ ] Update ProfilesRelationManager
- [ ] Add form template version selector
- [ ] Create version comparison view
- [ ] Add version history timeline

### Testing
- [ ] Unit tests for versioning logic
- [ ] Integration tests for activation/deactivation
- [ ] UI tests for version management
- [ ] Performance tests for large datasets

### Documentation
- [ ] Update API documentation
- [ ] Create user guide for version management
- [ ] Document migration procedures

## 🎯 Timeline Estimasi

| Phase | Estimasi Waktu | Prioritas |
|-------|---------------|-----------|
| Phase 1: Database | 2-3 hari | High |
| Phase 2: Models | 3-4 hari | High |
| Phase 3: UI/UX | 4-5 hari | Medium |
| Phase 4: Business Logic | 2-3 hari | Medium |
| Testing & Polish | 3-4 hari | High |

**Total: ~2-3 minggu**

## 🚨 Considerations & Risks

1. **Data Migration**: Existing form templates need to be properly migrated
2. **Performance**: Large number of versions might impact query performance
3. **User Experience**: Version management should be intuitive
4. **Backwards Compatibility**: Ensure existing functionality still works
5. **Validation**: Robust validation needed to prevent multiple active templates

## 📋 Next Steps

1. Review and approve this plan
2. Create database migration for versioning columns
3. Update FormTemplate model with versioning logic
4. Test with existing data
5. Implement UI components incrementally