<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UpdateRoleSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'tim_it'      => 'IT',
            'tim_mutu'    => 'Tim Mutu',
            'unit_kerja'  => 'Unit Kerja',
            'super_admin' => 'Administrator Application',
        ];

        foreach ($map as $slug => $label) {
            // the original seeder incorrectly searched by label; we're migrating
            // existing records that use the slug as both name and label so that
            // the label becomes something human readable.
            Role::where('name', $slug)
                ->update([
                    'label' => $label,
                ]);
        }
    }
}
