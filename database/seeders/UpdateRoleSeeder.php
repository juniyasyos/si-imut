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
            Role::where('name', $label)->update([
                'name'  => $slug,
                'label' => $label,
            ]);
        }
    }
}
