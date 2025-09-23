<?php

namespace App\Services\Filament;

use App\Services\ImutDataService;
use App\Models\ImutData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImutDataFilamentService
{
    public function __construct(
        private ImutDataService $imutDataService
    ) {}

    /**
     * Get query builder with user permissions applied
     */
    public function getTableQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->can('view_all_data_imut::data')) {
            return ImutData::query();
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            $unitKerjaIds = $user->unitKerjas->pluck('id')->toArray();

            return ImutData::query()
                ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                    $query->whereIn('unit_kerja.id', $unitKerjaIds);
                })->orWhere('created_by', $user->id);
        }

        return ImutData::query()->whereRaw('1 = 0');
    }

    /**
     * Handle IMUT data creation with unit kerja assignment
     */
    public function createImutDataWithUnitKerja(array $data): ImutData
    {
        // Create the IMUT data first
        $imutData = $this->imutDataService->createImutData($data);

        // Handle unit kerja assignment
        $this->assignUnitKerjaToImutData($imutData, $data);

        return $imutData;
    }

    /**
     * Update IMUT data with unit kerja management
     */
    public function updateImutDataWithUnitKerja(int $id, array $data): bool
    {
        // Update the IMUT data
        $updated = $this->imutDataService->updateImutData($id, $data);

        if ($updated && isset($data['unitKerjaIds'])) {
            $imutData = $this->imutDataService->findImutData($id);
            $this->syncUnitKerjaForImutData($imutData, $data['unitKerjaIds']);
        }

        return $updated;
    }

    /**
     * Get status toggle permission for user
     */
    public function canToggleStatus(): bool
    {
        return Gate::any(['update_imut::data']);
    }

    /**
     * Get badge color for category
     */
    public function getCategoryBadgeColor($categoryId): string
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
        return $colors[($categoryId ?? 0) % count($colors)];
    }

    /**
     * Get globally searchable attributes
     */
    public function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    /**
     * Get global search result details
     */
    public function getGlobalSearchResultDetails($record): array
    {
        return [
            __('filament-forms::imut-data.fields.imut_kategori_id') => $record->kategori->category_name ?? '-',
        ];
    }

    private function assignUnitKerjaToImutData(ImutData $imutData, array $data): void
    {
        /** @var User $user */
        $user = Auth::user();

        $unitKerjaIds = $user->can('attach_imut_data_to_unit_kerja_unit::kerja')
            ? ($data['unitKerjaIds'] ?? [])
            : $user->unitKerjas->pluck('unit_kerja.id')->toArray();

        foreach ($unitKerjaIds as $unitKerjaId) {
            \App\Models\ImutDataUnitKerja::firstOrCreate([
                'imut_data_id' => $imutData->id,
                'unit_kerja_id' => $unitKerjaId,
            ], [
                'assigned_by' => $user->id,
                'assigned_at' => now(),
            ]);
        }
    }

    private function syncUnitKerjaForImutData(ImutData $imutData, array $unitKerjaIds): void
    {
        /** @var User $user */
        $user = Auth::user();

        // Remove existing assignments
        \App\Models\ImutDataUnitKerja::where('imut_data_id', $imutData->id)->delete();

        // Add new assignments
        foreach ($unitKerjaIds as $unitKerjaId) {
            \App\Models\ImutDataUnitKerja::create([
                'imut_data_id' => $imutData->id,
                'unit_kerja_id' => $unitKerjaId,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
            ]);
        }
    }
}
