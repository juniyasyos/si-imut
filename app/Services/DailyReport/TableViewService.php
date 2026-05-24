<?php

namespace App\Services\DailyReport;

use App\Domain\DailyReport\TableViewDomain;
use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\ImutPenilaian;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\Support\SignatoryService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TableViewService
{
    public function __construct(
        protected TableViewDomain $domain,
        protected SignatoryService $signatoryService
    ) {
    }

    public function buildTableViewData(User $user, ?int $formTemplateId, ?int $unitKerjaId, string $period): array
    {
        [$year, $month] = explode('-', $period);
        $date = Carbon::createFromDate((int) $year, (int) $month, 1);
        $startDate = $date->copy()->startOfMonth()->startOfDay();
        $endDate = $date->copy()->endOfMonth()->endOfDay();

        $query = DailyReportResponse::query()
            ->with([
                'formTemplate.formFields.options',
                'formTemplate.imutProfile.imutData',
                'unitKerja',
                'submittedBy',
                'validator',
                'fieldResponses.formField.options',
            ])
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($formTemplateId) {
            $query->where('form_template_id', $formTemplateId);
        } else {
            $query->whereHas('formTemplate', fn ($q) => $q->where('is_active', true));
        }

        if ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        } else {
            $query->forUserUnits($user);
        }

        $entries = $query->orderBy('report_date', 'asc')->get();

        if ($entries->isEmpty()) {
            return [
                'tableTitle' => 'Data Laporan Harian',
                'tableDescription' => 'Tidak ada data untuk periode yang dipilih',
                'tableConfig' => null,
                'tableData' => [],
                'metadata' => $this->domain->buildMetadata($unitKerjaId, $period, $startDate, $endDate),
                'analysis' => '',
                'recommendations' => '',
                'user' => $this->domain->getUserInfo($user),
                'usersByUnit' => [],
            ];
        }

        $formTemplate = $entries->first()->formTemplate;
        $tableConfig = $this->domain->buildTableConfig($formTemplate, $entries);
        $tableData = $this->domain->transformEntriesToTableData($entries, $formTemplate);
        $metadata = $this->domain->buildMetadata(
            $unitKerjaId,
            $period,
            $startDate,
            $endDate,
            $formTemplate,
            $entries->first()->unitKerja
        );
        $summary = $this->domain->buildSummary($tableData, $entries, $formTemplate);

        [$analysis, $recommendations] = $this->buildAnalysis($formTemplate, $unitKerjaId, $startDate, $endDate, $entries);

        return [
            'tableTitle' => $metadata['imut_data'] ?? 'Data Laporan Harian',
            'tableDescription' => sprintf(
                'Menampilkan %d data laporan harian %s periode %s',
                $entries->count(),
                $metadata['unit_kerja'] ?? '',
                $metadata['period_label'] ?? $period
            ),
            'tableConfig' => $tableConfig,
            'tableData' => $tableData,
            'metadata' => $metadata,
            'summary' => $summary,
            'analysis' => $analysis,
            'recommendations' => $recommendations,
            'user' => $this->domain->getUserInfo($user),
            'usersByUnit' => $this->getUsersByUnit($entries->first()->unitKerja, $entries),
        ];
    }

    protected function buildAnalysis(FormTemplate $formTemplate, ?int $unitKerjaId, Carbon $startDate, Carbon $endDate, Collection $entries): array
    {
        $profileId = $formTemplate->imut_profile_id;
        if (! $profileId) {
            return ['', ''];
        }

        $penilaianQuery = ImutPenilaian::query()
            ->where('imut_profil_id', $profileId)
            ->whereHas('laporanUnitKerja.laporanImut', function ($q) use ($startDate, $endDate) {
                $q->where(function ($q2) use ($startDate, $endDate) {
                    $q2->whereBetween('assessment_period_start', [$startDate, $endDate])
                        ->orWhereBetween('assessment_period_end', [$startDate, $endDate]);
                });
            });

        if ($unitKerjaId) {
            $penilaianQuery->whereHas('laporanUnitKerja', fn ($q) => $q->where('unit_kerja_id', $unitKerjaId));
        } elseif ($entries->first()?->unitKerja?->id) {
            $penilaianQuery->whereHas('laporanUnitKerja', fn ($q) => $q->where('unit_kerja_id', $entries->first()->unitKerja->id));
        }

        $penilaianRec = $penilaianQuery->latest('id')->first();

        return [
            $penilaianRec?->analysis ?? '',
            $penilaianRec?->recommendations ?? '',
        ];
    }

    protected function getUsersByUnit(?UnitKerja $unitKerja, ?Collection $entries = null): array
    {
        if (! $unitKerja) {
            return ['pengumpul_data' => [], 'validator' => [], 'unit_kerja_id' => null, 'unit_kerja_name' => null, 'unit_users' => []];
        }

        $signatories = $this->signatoryService->pickForUnit($unitKerja, $entries);
        $unitUsers = $signatories['unit_users'];

        $formatUser = function ($user) use ($unitUsers) {
            if (! $user) {
                return null;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'ttd_url' => trim($this->signatoryService->getTtdUrl($user) ?? ''),
                'unit_users' => $unitUsers,
            ];
        };

        return [
            'pengumpul_data' => $signatories['pengumpul'] ? [$formatUser($signatories['pengumpul'])] : [],
            'validator' => $signatories['validator'] ? [$formatUser($signatories['validator'])] : [],
            'unit_kerja_id' => $unitKerja->id,
            'unit_kerja_name' => $unitKerja->unit_name,
            'unit_users' => $unitUsers->map($formatUser)->values()->toArray(),
        ];
    }
}
