<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RoleLabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔁 Updating role labels based on name slug');

        // manual overrides for anything that doesn't follow the simple title-case rule
        $overrides = [
            'super_admin'    => 'Super Admin',
            'tim_mutu'       => 'Tim Mutu',
            'pengumpul_data' => 'Pengumpul Data',
            'validator_pic'  => 'Validator PIC',
            'tim_it'         => 'IT',
            // add more custom labels here when needed
        ];

        Role::cursor()->each(function (Role $role) use ($overrides) {
            $slug = $role->name;

            if (array_key_exists($slug, $overrides)) {
                $label = $overrides[$slug];
            } else {
                // convert snake_case slug into a readable title
                $label = Str::title(str_replace(['_', '-'], [' ', ' '], $slug));
            }

            if ($role->label !== $label) {
                $role->update(['label' => $label]);
                $this->command->info("   • {$slug} => {$label}");
            }
        });

        $this->command->info('✅ Role labels updated.');
    }
}
