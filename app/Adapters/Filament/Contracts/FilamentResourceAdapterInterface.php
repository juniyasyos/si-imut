<?php

namespace App\Adapters\Filament\Contracts;

/**
 * Filament Resource Adapter Interface
 *
 * Defines the contract for adapting business logic to Filament UI
 */
interface FilamentResourceAdapterInterface
{
    /**
     * Get table query for Filament resource
     *
     * @param array $filters
     * @param array $sorting
     * @return mixed
     */
    public function getTableQuery(array $filters = [], array $sorting = []);

    /**
     * Get form data for Filament forms
     *
     * @param mixed $record
     * @return array
     */
    public function getFormData($record = null): array;

    /**
     * Process form submission from Filament
     *
     * @param array $data
     * @param mixed $record
     * @return mixed
     */
    public function processFormSubmission(array $data, $record = null);

    /**
     * Handle record creation
     *
     * @param array $data
     * @return mixed
     */
    public function createRecord(array $data);

    /**
     * Handle record update
     *
     * @param mixed $record
     * @param array $data
     * @return mixed
     */
    public function updateRecord($record, array $data);

    /**
     * Handle record deletion
     *
     * @param mixed $record
     * @return bool
     */
    public function deleteRecord($record): bool;

    /**
     * Get widget data for Filament widgets
     *
     * @param array $parameters
     * @return array
     */
    public function getWidgetData(array $parameters = []): array;
}
