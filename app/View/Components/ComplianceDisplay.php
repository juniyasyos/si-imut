<?php

namespace App\View\Components;

use App\Services\DailyReport\UnifiedComplianceService;
use Illuminate\View\Component;
use Illuminate\Support\HtmlString;
use App\Models\FormTemplate;

class ComplianceDisplay extends Component
{
    public FormTemplate $formTemplate;
    public array $currentData;
    public array $compliance;

    public function __construct(FormTemplate $formTemplate, array $currentData = [])
    {
        $this->formTemplate = $formTemplate;
        $this->currentData = $currentData;

        // Perform compliance calculation here (off Livewire handler)
        $unified = app(UnifiedComplianceService::class)
            ->calculate($this->formTemplate, $this->currentData);

        $fieldBreakdown = $unified['calculation_details']['field_breakdown'] ?? [];
        $fieldsAssoc = [];
        foreach ($fieldBreakdown as $fb) {
            $key = $fb['field_key'] ?? null;
            if ($key) {
                $fieldsAssoc[$key] = [
                    'score' => $fb['score'] ?? 0,
                    'weight' => $fb['weight'] ?? 0,
                ];
            }
        }

        $this->compliance = [
            'score' => $unified['score'] ?? ($unified['calculation_details']['weighted_percentage'] ?? 0),
            'total_score' => $unified['calculation_details']['raw_score'] ?? 0,
            'fields' => $fieldsAssoc,
            'warnings' => $unified['calculation_details']['warnings'] ?? [],
            'auto_fail' => $unified['critical_failed'] ?? false,
        ];
    }

    public function render()
    {
        return view('components.compliance-display');
    }
}
