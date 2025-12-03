<?php

namespace Database\Seeders;

use App\Models\FormHeader;
use App\Models\FormField;
use App\Models\ImutData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FormHeaderSeeder extends Seeder
{
    /**
     * Path ke direktori form configurations
     */
    private string $configPath = 'database/data/form-configurations';

    /**
     * Load form configurations dari file JSON
     */
    private function loadFormConfigurations(): array
    {
        $configurations = [];
        $path = base_path($this->configPath);

        if (!File::exists($path)) {
            $this->command->warn("Direktori form configurations tidak ditemukan: {$path}");
            return $configurations;
        }

        $files = File::files($path);

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $content = File::get($file->getPathname());
                $config = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $configurations[] = $config;
                    $this->command->info("Loaded configuration: {$file->getFilename()}");
                } else {
                    $this->command->error("Error parsing JSON in {$file->getFilename()}: " . json_last_error_msg());
                }
            }
        }

        return $configurations;
    }

    public function run(): void
    {
        $formConfigurations = $this->loadFormConfigurations();

        if (empty($formConfigurations)) {
            $this->command->warn("Tidak ada konfigurasi form yang ditemukan.");
            return;
        }

        foreach ($formConfigurations as $config) {
            $imutData = ImutData::where('slug', $config['slug'])->first();

            if (!$imutData) {
                $this->command->warn("ImutData dengan slug '{$config['slug']}' tidak ditemukan. Skip.");
                continue;
            }

            // Create form header dengan title dari ImutData
            $formHeader = FormHeader::create([
                'imutdata_id' => $imutData->id,
                'title' => $imutData->title,
                'description' => 'Form pengumpulan data harian untuk indikator ' . $imutData->title,
            ]);

            // Create fields
            foreach ($config['fields'] as $fieldData) {
                FormField::create([
                    'form_header_id' => $formHeader->id,
                    'key' => $fieldData['key'],
                    'label' => $fieldData['label'],
                    'description' => $fieldData['description'] ?? null,
                    'type' => $fieldData['type'],
                    'is_required' => $fieldData['is_required'],
                    'options' => $fieldData['options'],
                    'order' => $fieldData['order'],
                ]);
            }

            $this->command->info("✓ Form untuk '{$imutData->title}' berhasil dibuat dengan " . count($config['fields']) . " fields.");
        }

        $this->command->info("\nTotal {count($formConfigurations)} form configuration berhasil diproses.");
    }
}
