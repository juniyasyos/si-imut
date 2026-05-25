<?php

namespace App\Services\DynamicForm;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\FormFields;
use App\Models\FormTemplate;

class ComplianceCalculatorService
{
    /**
     * Calculate compliance score based on form template and data
     */
    public static function calculateCompliance(FormTemplate $formTemplate, array $data): array
    {
        // Delegate to UnifiedComplianceService to avoid duplicate scoring logic
        $service = app(\App\Services\DailyReport\UnifiedComplianceService::class);
        $unified = $service->calculate($formTemplate, $data);

        // Map unified structure to legacy shape expected by callers of this class
        $fieldBreakdown = [];
        $uFields = $unified['calculation_details']['field_breakdown'] ?? [];
        foreach ($uFields as $fb) {
            $key = $fb['field_key'] ?? null;
            if ($key) {
                $fieldBreakdown[$key] = [
                    'score' => $fb['score'] ?? 0,
                    'weight' => $fb['weight'] ?? 0,
                ];
            }
        }

        $statusText = ($unified['compliance_status'] ?? false) ? '✅ PATUH' : '❌ TIDAK PATUH';

        return [
            'score' => $unified['total_score'] ?? ($unified['calculation_details']['weighted_percentage'] ?? 0),
            'total_score' => $unified['calculation_details']['raw_score'] ?? 0,
            'total_weight' => $unified['calculation_details']['max_score'] ?? 0,
            'status' => $statusText,
            'fields' => $fieldBreakdown,
            'warnings' => $unified['calculation_details']['warnings'] ?? [],
            'auto_fail' => $unified['critical_failed'] ?? false,
        ];
    }

    /**
     * Calculate compliance and return formatted results for storage
     */
    public static function calculateForStorage(FormTemplate $formTemplate, array $data): array
    {
        $compliance = static::calculateCompliance($formTemplate, $data);

        return [
            'compliance_score' => $compliance['score'],
            'compliance_status' => ($compliance['status'] ?? '') === '✅ PATUH' || ($compliance['status'] ?? '') === true,
            'compliance_details' => [
                'total_score' => $compliance['total_score'],
                'total_weight' => $compliance['total_weight'] ?? ($compliance['total_weight'] ?? 0),
                'field_breakdown' => $compliance['fields'] ?? [],
                'warnings' => $compliance['warnings'] ?? [],
                'auto_fail' => $compliance['auto_fail'] ?? false
            ]
        ];
    }
}
