<x-filament-panels::page>
    <div class="fi-resource-list-records-page">
        {{ $this->table }}
    </div>

    <style>
        .fi-ta-cell-compliance_status .fi-badge {
            font-weight: 600;
        }

        .fi-ta-cell-total_score {
            font-weight: 600;
            text-align: center;
        }

        .fi-ta-header-cell-report_date,
        .fi-ta-header-cell-total_score,
        .fi-ta-header-cell-compliance_status {
            font-weight: 700;
        }
    </style>
</x-filament-panels::page>