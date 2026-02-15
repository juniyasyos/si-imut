<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestUserResourceSchema extends Command
{
    protected $signature = 'test:user-resource-schema';
    protected $description = 'Test UserResource schema with TTD and roles';

    public function handle()
    {
        $this->info('=== Testing UserResource Schema ===');

        // 1. Check schema generation
        $this->line("\n1. Checking Schema Generation:");
        try {
            $schema = \App\Filament\Resources\UserResource\Schema\UserResourceSchema::make();
            $this->line("   ✓ Schema generated successfully");
            $this->line("   Total sections: " . count($schema));

            $sectionTitles = [];
            foreach ($schema as $section) {
                if (method_exists($section, 'getLabel')) {
                    $sectionTitles[] = $section->getLabel() ?: 'Unnamed Section';
                }
            }
            $this->line("   Sections: " . implode(', ', $sectionTitles));
        } catch (\Exception $e) {
            $this->error("   ✗ Schema generation failed: " . $e->getMessage());
            return 1;
        }

        // 2. Check TTD field
        $this->line("\n2. Checking TTD Field:");
        $schemaContent = file_get_contents(app_path('Filament/Resources/UserResource/Schema/UserResourceSchema.php'));

        $checks = [
            'TTD FileUpload field' => strpos($schemaContent, "FileUpload::make('ttd_url')") !== false,
            'TTD disk S3' => strpos($schemaContent, "->disk('s3')") !== false,
            'TTD directory' => strpos($schemaContent, "->directory('ttd')") !== false,
            'TTD image validation' => strpos($schemaContent, "->image()") !== false,
            'TTD file types' => strpos($schemaContent, "'image/png', 'image/jpeg', 'image/jpg'") !== false,
            'TTD max size' => strpos($schemaContent, "->maxSize(2048)") !== false,
        ];

        foreach ($checks as $check => $passed) {
            if ($passed) {
                $this->line("   ✓ " . $check);
            } else {
                $this->error("   ✗ " . $check);
            }
        }

        // 3. Check Roles field
        $this->line("\n3. Checking Roles Field:");
        $roleChecks = [
            'Roles Select field' => strpos($schemaContent, "Select::make('roles')") !== false,
            'Roles relationship' => strpos($schemaContent, "->relationship('roles', 'name')") !== false,
            'Roles multiple' => strpos($schemaContent, "->multiple()") !== false,
            'Roles preload' => strpos($schemaContent, "->preload()") !== false,
            'Roles searchable' => strpos($schemaContent, "->searchable()") !== false,
        ];

        foreach ($roleChecks as $check => $passed) {
            if ($passed) {
                $this->line("   ✓ " . $check);
            } else {
                $this->error("   ✗ " . $check);
            }
        }

        // 4. Check UserResource methods
        $this->line("\n4. Checking UserResource Methods:");
        $resourceContent = file_get_contents(app_path('Filament/Resources/UserResource.php'));

        $methodChecks = [
            'mutateFormDataBeforeCreate' => strpos($resourceContent, 'mutateFormDataBeforeCreate') !== false,
            'mutateFormDataBeforeSave' => strpos($resourceContent, 'mutateFormDataBeforeSave') !== false,
            'afterCreate' => strpos($resourceContent, 'afterCreate') !== false,
            'afterSave' => strpos($resourceContent, 'afterSave') !== false,
            'mutateFormDataBeforeFill' => strpos($resourceContent, 'mutateFormDataBeforeFill') !== false,
            'syncRoles call' => strpos($resourceContent, 'syncRoles') !== false,
        ];

        foreach ($methodChecks as $check => $passed) {
            if ($passed) {
                $this->line("   ✓ " . $check);
            } else {
                $this->error("   ✗ " . $check);
            }
        }

        // 5. Check User model fillable
        $this->line("\n5. Checking User Model:");
        $userModel = new \App\Models\User();
        $fillable = $userModel->getFillable();

        $modelChecks = [
            'ttd_url in fillable' => in_array('ttd_url', $fillable),
            'User has HasRoles trait' => in_array('Spatie\Permission\Traits\HasRoles', class_uses($userModel)),
        ];

        foreach ($modelChecks as $check => $passed) {
            if ($passed) {
                $this->line("   ✓ " . $check);
            } else {
                $this->error("   ✗ " . $check);
            }
        }

        // 6. Check available roles
        $this->line("\n6. Checking Available Roles:");
        try {
            $roles = \Spatie\Permission\Models\Role::all();
            $this->line("   ✓ Found " . $roles->count() . " roles:");
            foreach ($roles as $role) {
                $this->line("     - " . $role->name);
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Error checking roles: " . $e->getMessage());
        }

        $this->info("\n✓ UserResource Schema test completed!\n");
        return 0;
    }
}
