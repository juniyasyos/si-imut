<?php

namespace App\Commands\LaporanImut;

use App\Commands\BaseCommand;
use App\Commands\Contracts\MutationCommandInterface;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use App\Factories\LaporanImutFactory;
use App\Models\LaporanImut;
use Illuminate\Support\Facades\DB;

/**
 * Create LaporanImut Command
 *
 * Handles the creation of new LaporanImut entities
 */
class CreateLaporanImutCommand extends BaseCommand implements MutationCommandInterface
{
    protected $entityId;

    public function __construct(
        private LaporanImutRepositoryInterface $repository,
        private LaporanImutFactory $factory
    ) {
        $this->setValidationRules([
            'name' => 'required|string|min:3|max:255|unique:laporan_imut,name',
            'status' => 'required|string|in:process,complete,coming_soon',
            'assessment_period_start' => 'required|date|before:assessment_period_end',
            'assessment_period_end' => 'required|date|after:assessment_period_start',
            'created_by' => 'required|integer|exists:users,id',
            'unit_kerja_ids' => 'nullable|array',
            'unit_kerja_ids.*' => 'integer|exists:unit_kerja,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->setValidationMessages([
            'name.unique' => 'Nama laporan sudah digunakan.',
            'assessment_period_start.before' => 'Periode mulai harus sebelum periode selesai.',
            'assessment_period_end.after' => 'Periode selesai harus setelah periode mulai.',
        ]);
    }

    /**
     * Set entity ID (not used for creation)
     */
    public function setEntityId($id): self
    {
        $this->entityId = $id;
        return $this;
    }

    /**
     * Execute the create command
     *
     * @return LaporanImut
     */
    public function execute(): LaporanImut
    {
        return DB::transaction(function () {
            // Create the laporan using factory
            $laporan = $this->factory->create($this->data);

            // Attach unit kerjas if provided
            if (!empty($this->data['unit_kerja_ids'])) {
                $laporan->unitKerjas()->sync($this->data['unit_kerja_ids']);
            }

            return $laporan;
        });
    }

    /**
     * Create laporan with validation
     *
     * @param array $data
     * @return LaporanImut
     */
    public static function createWithValidation(array $data): LaporanImut
    {
        $command = app(self::class);
        return $command->setData($data)->executeWithValidation();
    }
}
