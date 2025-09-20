<?php

namespace App\Adapters\Filament;

use App\Adapters\Filament\Contracts\FilamentResourceAdapterInterface;
use App\Commands\LaporanImut\CreateLaporanImutCommand;
use App\Commands\LaporanImut\UpdateLaporanImutCommand;
use App\Commands\LaporanImut\DeleteLaporanImutCommand;
use App\Commands\LaporanImut\GetLaporanImutListCommand;
use App\Services\LaporanImut\LaporanImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * LaporanImut Filament Adapter
 *
 * Bridges Filament UI with LaporanImut business logic
 */
class LaporanImutFilamentAdapter implements FilamentResourceAdapterInterface
{
    public function __construct(
        private LaporanImutCalculationService $calculationService
    ) {}

    /**
     * Get table query for Filament resource
     */
    public function getTableQuery(array $filters = [], array $sorting = []): Builder
    {
        // Use our command to get data, but return a Builder for Filament compatibility
        $command = app(GetLaporanImutListCommand::class);

        // Apply filters
        foreach ($filters as $filter) {
            $command->addFilter($filter['field'], $filter['value'], $filter['operator'] ?? '=');
        }

        // Apply sorting
        if (!empty($sorting)) {
            $command->sortBy($sorting['field'], $sorting['direction'] ?? 'asc');
        }

        // For Filament compatibility, we need to return a Builder
        // So we'll use the repository directly but with our business logic
        return \App\Models\LaporanImut::query()
            ->when(!empty($filters), function ($query) use ($filters) {
                foreach ($filters as $filter) {
                    $query->where($filter['field'], $filter['operator'] ?? '=', $filter['value']);
                }
            })
            ->when(!empty($sorting), function ($query) use ($sorting) {
                $query->orderBy($sorting['field'], $sorting['direction'] ?? 'asc');
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get form data for Filament forms
     */
    public function getFormData($record = null): array
    {
        if (!$record) {
            return [
                'status' => 'process',
                'created_by' => Auth::check() ? Auth::user()->id : null,
                'assessment_period_start' => now()->startOfMonth(),
                'assessment_period_end' => now()->endOfMonth(),
            ];
        }

        return [
            'name' => $record->name,
            'status' => $record->status,
            'assessment_period_start' => $record->assessment_period_start,
            'assessment_period_end' => $record->assessment_period_end,
            'unit_kerja_ids' => $record->unitKerjas->pluck('id')->toArray(),
            'description' => $record->description ?? '',
        ];
    }

    /**
     * Process form submission from Filament
     */
    public function processFormSubmission(array $data, $record = null)
    {
        if ($record) {
            return $this->updateRecord($record, $data);
        }

        return $this->createRecord($data);
    }

    /**
     * Handle record creation
     */
    public function createRecord(array $data)
    {
        // Ensure created_by is set
        if (empty($data['created_by'])) {
            $data['created_by'] = Auth::id();
        }

        return CreateLaporanImutCommand::createWithValidation($data);
    }

    /**
     * Handle record update
     */
    public function updateRecord($record, array $data)
    {
        return UpdateLaporanImutCommand::updateWithValidation($record->id, $data);
    }

    /**
     * Handle record deletion
     */
    public function deleteRecord($record): bool
    {
        return DeleteLaporanImutCommand::deleteById($record->id);
    }

    /**
     * Get widget data for Filament widgets
     */
    public function getWidgetData(array $parameters = []): array
    {
        $laporanId = $parameters['laporan_id'] ?? null;

        if (!$laporanId) {
            return [];
        }

        // Use our existing calculation service for widget data
        $laporan = \App\Models\LaporanImut::find($laporanId);
        if (!$laporan) {
            return [];
        }

        // Get dashboard statistics using our service
        $indikatorAktif = collect([$laporanId => $laporan->profiles]);
        $penilaianByProfile = $laporan->imutPenilaians->groupBy('imut_profil_id');
        $allPenilaian = $laporan->imutPenilaians;

        return $this->calculationService->calculateDashboardStats(
            $indikatorAktif,
            $penilaianByProfile,
            $allPenilaian,
            $laporanId
        );
    }

    /**
     * Get chart data for widgets
     */
    public function getChartData(array $parameters = []): array
    {
        $unitKerjaId = $parameters['unit_kerja_id'] ?? null;
        $laporanId = $parameters['laporan_id'] ?? null;

        if (!$laporanId) {
            return [];
        }

        // Use calculation service for chart data
        $laporan = \App\Models\LaporanImut::find($laporanId);
        if (!$laporan) {
            return [];
        }

        $laporanList = collect([$laporan]);
        $indikatorAktif = collect([$laporanId => $laporan->profiles]);
        $penilaianByLaporan = collect([$laporanId => $laporan->imutPenilaians]);
        $penilaianByProfile = $laporan->imutPenilaians->groupBy('imut_profil_id');

        return $this->calculationService->processChartData(
            $laporanList,
            $indikatorAktif,
            $penilaianByLaporan,
            $penilaianByProfile
        );
    }

    /**
     * Get table statistics for Filament table headers
     */
    public function getTableStatistics(array $parameters = []): array
    {
        $record = $parameters['record'] ?? null;

        if (!$record) {
            return [];
        }

        return $this->getWidgetData(['laporan_id' => $record->id]);
    }
}
