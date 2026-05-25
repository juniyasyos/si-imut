<?php

namespace App\Services\DailyReport;

use App\Domain\DailyReport\TableViewDomain;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use App\Repositories\Interfaces\ImutPenilaianRepositoryInterface;
use App\Services\Support\SignatoryService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TableViewService
{
    public function __construct(
        protected TableViewDomain $domain,
        protected SignatoryService $signatoryService,
        protected DailyReportResponseRepositoryInterface $dailyReportRepository,
        protected ImutPenilaianRepositoryInterface $penilaianRepository
    ) {
    }

    public function buildTableViewData(User $user, ?int $formTemplateId, ?int $unitKerjaId, string $period): array
    {
        [$year, $month] = explode('-', $period);
        $date = Carbon::createFromDate((int) $year, (int) $month, 1);
        $startDate = $date->copy()->startOfMonth()->startOfDay();
        $endDate = $date->copy()->endOfMonth()->endOfDay();

        $entries = $this->dailyReportRepository->getTableViewEntries(
            $user,
            $formTemplateId,
            $unitKerjaId,
            $startDate,
            $endDate
        );

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

        $penilaianRec = $this->penilaianRepository->findLatestAnalysisForProfile(
            $profileId,
            $startDate,
            $endDate,
            $unitKerjaId ?: $entries->first()?->unitKerja?->id
        );

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
