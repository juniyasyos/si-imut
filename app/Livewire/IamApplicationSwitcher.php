<?php

namespace App\Livewire;

use App\Services\IamApplicationService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class IamApplicationSwitcher extends Component
{
    public array $applications = [];
    public bool $isIamEnabled = false;

    public function mount(): void
    {
        $this->loadApplications();
    }

    public function loadApplications(): void
    {
        $this->isIamEnabled = config('iam.enabled');

        if ($this->isIamEnabled) {
            try {
                $service = app(IamApplicationService::class);
                $this->applications = $service->getFormattedApplications();
            } catch (\Exception $e) {
                Log::error('Failed to load IAM applications', [
                    'error' => $e->getMessage(),
                ]);
                $this->applications = [];
            }
        }
    }

    public function redirectToApplication(string $appKey): void
    {
        $app = collect($this->applications)->firstWhere('app_key', $appKey);

        if ($app && !empty($app['app_url'])) {
            $this->redirect($app['app_url'], navigate: false);
        }
    }

    public function render()
    {
        return view('livewire.iam-application-switcher');
    }
}
