<?php

namespace Database\Seeders;

use App\Models\FormHeader;
use App\Models\FormField;
use Illuminate\Database\Seeder;

class FormHeaderSeeder extends Seeder
{
    public function run(): void
    {
        $formHeader = FormHeader::create([
            'imutdata_id' => 1,
            'title' => 'Indikator High Alert',
            'description' => 'Pengumpulan data harian',
        ]);

        $fields = [
            [
                'form_header_id' => $formHeader->id,
                'key' => 'tanggal',
                'label' => 'Tanggal',
                'type' => 'date',
                'is_required' => true,
                'options' => null,
                'order' => 1,
            ],
            [
                'form_header_id' => $formHeader->id,
                'key' => 'no_rm',
                'label' => 'No. RM',
                'type' => 'text',
                'is_required' => true,
                'options' => null,
                'order' => 2,
            ],
            [
                'form_header_id' => $formHeader->id,
                'key' => 'tanda_ha',
                'label' => 'Tanda HA',
                'type' => 'bool',
                'is_required' => false,
                'options' => null,
                'order' => 3,
            ],
            [
                'form_header_id' => $formHeader->id,
                'key' => 'dobel_cek',
                'label' => 'Dobel Cek',
                'type' => 'bool',
                'is_required' => false,
                'options' => null,
                'order' => 4,
            ],
            [
                'form_header_id' => $formHeader->id,
                'key' => 'identifikasi',
                'label' => 'Identifikasi',
                'type' => 'bool',
                'is_required' => false,
                'options' => null,
                'order' => 5,
            ],
        ];

        foreach ($fields as $field) {
            FormField::create($field);
        }
    }
}
