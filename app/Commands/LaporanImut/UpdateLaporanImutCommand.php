<?php

namespace App\Commands\LaporanImut;

use App\Commands\BaseCommand;
use App\Commands\Contracts\MutationCommandInterface;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use App\Models\LaporanImut;
use Illuminate\Support\Facades\DB;

/**
 * Update LaporanImut Command
 *
 * Handles the updating of existing LaporanImut entities
 */
class UpdateLaporanImutCommand extends BaseCommand implements MutationCommandInterface
{
    protected $entityId;

    public function __construct(
        private LaporanImutRepositoryInterface $repository
    ) {
        $this->setValidationRules([
            'name' => 'required|string|min:3|max:255',
            'status' => 'required|string|in:process,complete,coming_soon',
            'assessment_period_start' => 'required|date|before:assessment_period_end',
            'assessment_period_end' => 'required|date|after:assessment_period_start',
            'unit_kerja_ids' => 'nullable|array',
            'unit_kerja_ids.*' => 'integer|exists:unit_kerja,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->setValidationMessages([
            'assessment_period_start.before' => 'Periode mulai harus sebelum periode selesai.',
            'assessment_period_end.after' => 'Periode selesai harus setelah periode mulai.',
        ]);
    }

    /**
     * Set entity ID for update
     */
    public function setEntityId($id): self
    {
        $this->entityId = $id;

        // Add unique validation rule excluding current record
        $this->validationRules['name'] .= '|unique:laporan_imut,name,' . $id;

        return $this;
    }

    /**
     * Execute the update command
     *
     * @return LaporanImut
     */
    public function execute(): LaporanImut
    {
        return DB::transaction(function () {
            $laporan = $this->repository->find($this->entityId);

            if (!$laporan) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    'LaporanImut not found with ID: ' . $this->entityId
                );
            }

            // Update laporan data
            $laporan->update($this->data);

            // Sync unit kerjas if provided
            if (array_key_exists('unit_kerja_ids', $this->data)) {
                $laporan->unitKerjas()->sync($this->data['unit_kerja_ids'] ?? []);
            }

            return $laporan->fresh();
        });
    }

    /**
     * Update laporan with validation
     *
     * @param int $id
     * @param array $data
     * @return LaporanImut
     */
    public static function updateWithValidation(int $id, array $data): LaporanImut
    {
        $command = app(self::class);
        return $command->setEntityId($id)->setData($data)->executeWithValidation();
    }
}
