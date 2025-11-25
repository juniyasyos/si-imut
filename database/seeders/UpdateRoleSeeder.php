<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UpdateRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapping slug => label
        $map = [
            'tim_it'      => 'IT',
            'tim_mutu'    => 'Tim Mutu',
            'unit_kerja'  => 'Unit Kerja',
            'super_admin' => 'Super Administrator',
        ];

        foreach ($map as $slug => $label) {
            Role::updateOrCreate(
                ['name' => $slug, 'guard_name' => 'web'],
                ['label' => $label]
            );
        }
    }
}
