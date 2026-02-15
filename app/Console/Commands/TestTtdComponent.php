<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Livewire\TtdUploadComponent;
use App\Models\User;

class TestTtdComponent extends Command
{
    protected $signature = 'test:ttd-component';
    protected $description = 'Test TtdUploadComponent Livewire component';

    public function handle()
    {
        $this->info('=== Testing TtdUploadComponent ===');

        // 1. Check component class
        $this->line("\n1. Component Class Check:");
        try {
            $component = new TtdUploadComponent();
            $this->line("   ✓ Component dapat diinstansi");

            // Check properties
            $this->line("   Component properties:");
            $this->line("   - view: " . $component->view);
            $this->line("   - only: " . json_encode($component->only));
            $this->line("   - data: " . json_encode($component->data ?? []));
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        // 2. Check form method
        $this->line("\n2. Form Method Check:");
        try {
            $component = new TtdUploadComponent();
            $form = $component->form(\Filament\Forms\Form::make());
            $this->line("   ✓ Form method works");

            $schema = $form->getSchema();
            $this->line("   Form schema items: " . count($schema));

            foreach ($schema as $field) {
                if (method_exists($field, 'getName')) {
                    $this->line("   - Field: " . $field->getName());
                }
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        // 3. Simulate mount
        $this->line("\n3. Component Mount Simulation:");
        try {
            $user = User::first();
            if ($user) {
                $component = new TtdUploadComponent();
                $component->user = $user;
                $component->userClass = get_class($user);

                // Initialize form
                $component->form = \Filament\Forms\Form::make();
                $component->form = $component->form($component->form);

                $this->line("   ✓ Mount simulation successful");
                $this->line("   User: " . $user->name);
                $this->line("   Current TTD: " . ($user->ttd_url ?? 'none'));
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
        }

        // 4. Check view file
        $this->line("\n4. View File Check:");
        $viewPath = resource_path('views/livewire/ttd-upload-component.blade.php');
        if (file_exists($viewPath)) {
            $this->line("   ✓ View file exists");
            $content = file_get_contents($viewPath);

            if (strpos($content, 'wire:submit.prevent="submit"') !== false) {
                $this->line("   ✓ Form submit directive found");
            } else {
                $this->warn("   ✗ Form submit directive NOT found");
            }

            if (strpos($content, '$this->form') !== false) {
                $this->line("   ✓ Form rendering found");
            } else {
                $this->warn("   ✗ Form rendering NOT found");
            }
        } else {
            $this->error("   ✗ View file tidak ditemukan: " . $viewPath);
        }

        // 5. Check Filament registration
        $this->line("\n5. Filament Registration Check:");
        $panelProvider = app_path('Providers/Filament/AdminPanelProvider.php');
        $content = file_get_contents($panelProvider);

        if (strpos($content, "TtdUploadComponent::class") !== false) {
            $this->line("   ✓ TtdUploadComponent registered in AdminPanelProvider");

            if (strpos($content, "'ttd_upload' => TtdUploadComponent::class") !== false) {
                $this->line("   ✓ Registered with correct key 'ttd_upload'");
            } else {
                $this->warn("   ⚠ Check registration key");
            }
        } else {
            $this->error("   ✗ TtdUploadComponent NOT registered!");
        }

        $this->info("\n=== Test Selesai ===\n");
        return 0;
    }
}
