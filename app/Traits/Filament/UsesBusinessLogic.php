<?php

namespace App\Traits\Filament;

use App\Adapters\Filament\Contracts\FilamentResourceAdapterInterface;

/**
 * Filament Business Logic Integration Trait
 *
 * Provides easy integration with business logic for Filament resources
 */
trait UsesBusinessLogic
{
    protected ?FilamentResourceAdapterInterface $businessAdapter = null;

    /**
     * Get the business logic adapter for this resource
     */
    protected function getBusinessAdapter(): FilamentResourceAdapterInterface
    {
        if ($this->businessAdapter === null) {
            $adapterClass = $this->getBusinessAdapterClass();
            $this->businessAdapter = app($adapterClass);
        }

        return $this->businessAdapter;
    }

    /**
     * Get the business adapter class name
     * Override this method in your resource
     */
    protected function getBusinessAdapterClass(): string
    {
        throw new \Exception('You must override getBusinessAdapterClass() method in your Filament resource');
    }

    /**
     * Override Filament's table query to use business logic
     */
    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->getBusinessAdapter()->getTableQuery(
            $this->getTableFilters(),
            $this->getTableSorting()
        );
    }

    /**
     * Get table filters in a format suitable for business logic
     */
    protected function getTableFilters(): array
    {
        // Override this method to extract filters from Filament
        return [];
    }

    /**
     * Get table sorting in a format suitable for business logic
     */
    protected function getTableSorting(): array
    {
        // Override this method to extract sorting from Filament
        return [];
    }

    /**
     * Handle form submission using business logic
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->getBusinessAdapter()->createRecord($data);
    }

    /**
     * Handle record update using business logic
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->getBusinessAdapter()->updateRecord($record, $data);
    }

    /**
     * Handle record deletion using business logic
     */
    protected function handleRecordDeletion(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $this->getBusinessAdapter()->deleteRecord($record);
    }

    /**
     * Get widget data using business logic
     */
    protected function getWidgetData(array $parameters = []): array
    {
        return $this->getBusinessAdapter()->getWidgetData($parameters);
    }

    /**
     * Get form data using business logic
     */
    protected function getFormData($record = null): array
    {
        return $this->getBusinessAdapter()->getFormData($record);
    }

    /**
     * Mutate form data before fill (for editing)
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->getFormData($this->record ?? null);
    }

    /**
     * Mutate form data before save
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Let business logic handle any pre-processing
        return $this->getBusinessAdapter()->processFormSubmission($data, $this->record ?? null);
    }
}
