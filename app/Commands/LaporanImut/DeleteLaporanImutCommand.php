<?php

namespace App\Commands\LaporanImut;

use App\Commands\BaseCommand;
use App\Commands\Contracts\MutationCommandInterface;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Delete LaporanImut Command
 *
 * Handles the deletion of LaporanImut entities
 */
class DeleteLaporanImutCommand extends BaseCommand implements MutationCommandInterface
{
    protected $entityId;
    protected bool $forceDelete = false;

    public function __construct(
        private LaporanImutRepositoryInterface $repository
    ) {
        // No validation rules needed for deletion
    }

    /**
     * Set entity ID for deletion
     */
    public function setEntityId($id): self
    {
        $this->entityId = $id;
        return $this;
    }

    /**
     * Set data (not used for deletion)
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        $this->forceDelete = $data['force_delete'] ?? false;
        return $this;
    }

    /**
     * Execute the delete command
     *
     * @return bool
     */
    public function execute(): bool
    {
        return DB::transaction(function () {
            $laporan = $this->repository->find($this->entityId);

            if (!$laporan) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    'LaporanImut not found with ID: ' . $this->entityId
                );
            }

            // Detach all related unit kerjas
            $laporan->unitKerjas()->detach();

            // Delete or force delete
            if ($this->forceDelete) {
                return $laporan->forceDelete();
            }

            return $laporan->delete();
        });
    }

    /**
     * Delete laporan
     *
     * @param int $id
     * @param bool $forceDelete
     * @return bool
     */
    public static function deleteById(int $id, bool $forceDelete = false): bool
    {
        $command = app(self::class);
        return $command->setEntityId($id)->setData(['force_delete' => $forceDelete])->execute();
    }
}
