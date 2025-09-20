<?php

namespace App\Services\Facades;

use App\Commands\LaporanImut\CreateLaporanImutCommand;
use App\Commands\LaporanImut\UpdateLaporanImutCommand;
use App\Commands\LaporanImut\DeleteLaporanImutCommand;
use App\Commands\LaporanImut\GetLaporanImutListCommand;
use App\Adapters\Filament\LaporanImutFilamentAdapter;
use App\Models\LaporanImut;

/**
 * LaporanImut Business Logic Service
 *
 * Implements the actual business logic behind the facade
 */
class LaporanImutBusinessLogicService
{
    public function __construct(
        private LaporanImutFilamentAdapter $adapter
    ) {}

    /**
     * Create new LaporanImut
     */
    public function create(array $data): LaporanImut
    {
        return CreateLaporanImutCommand::createWithValidation($data);
    }

    /**
     * Update existing LaporanImut
     */
    public function update(int $id, array $data): LaporanImut
    {
        return UpdateLaporanImutCommand::updateWithValidation($id, $data);
    }

    /**
     * Delete LaporanImut
     */
    public function delete(int $id): bool
    {
        return DeleteLaporanImutCommand::deleteById($id);
    }

    /**
     * Get paginated list of LaporanImut
     */
    public function list(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 15)
    {
        return GetLaporanImutListCommand::getPaginatedList($filters, $sorting, $page, $perPage);
    }

    /**
     * Get widget data
     */
    public function getWidgetData(array $parameters = []): array
    {
        return $this->adapter->getWidgetData($parameters);
    }

    /**
     * Get chart data
     */
    public function getChartData(array $parameters = []): array
    {
        return $this->adapter->getChartData($parameters);
    }

    /**
     * Get table statistics
     */
    public function getTableStatistics(array $parameters = []): array
    {
        return $this->adapter->getTableStatistics($parameters);
    }

    /**
     * Get form data for UI
     */
    public function getFormData($record = null): array
    {
        return $this->adapter->getFormData($record);
    }
}
