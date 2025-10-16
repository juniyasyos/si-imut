<?php

namespace Database\seeders;

use App\Traits\ImutInitializer;
use App\Domains\Imut\Models\ImutCategory;
use App\Domains\Imut\Models\ImutData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ImutDataSeeder extends Seeder
{
    use ImutInitializer;

    public function run(): void
    {
        $this->initImut();

        $files = [
            'INM'      => 'inm.json',
            'IMP-UNIT' => 'imp-unit.json',
            'IMP-RS'   => 'imp-rs.json',
            'IMIKP'    => 'imp_kp.json',
            'UNIT'     => 'unit.json',
        ];

        foreach ($files as $short => $file) {
            $cat = ImutCategory::where('short_name', $short)->first();
            if (! $cat) {
                $this->command->warn("Kategori $short tidak ditemukan, lewati.");
                continue;
            }
            $path = database_path("data/{$file}");
            if (! File::exists($path)) {
                $this->command->warn("File $file tidak ada.");
                continue;
            }
            $data = json_decode(File::get($path), true);
            foreach ($data as $ind) {
                ImutData::firstOrCreate([
                    'title'           => $ind['title'],
                    'imut_kategori_id' => $cat->id,
                    'description'     => $ind['description'],
                    'status'          => true,
                    'created_by'      => $this->adminUserId,
                ]);
            }
        }
    }
}
