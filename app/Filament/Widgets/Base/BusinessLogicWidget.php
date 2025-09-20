<?php

namespace App\Filament\Widgets\Base;

use App\Adapters\Filament\Contracts\FilamentResourceAdapterInterface;
use Filament\Widgets\Widget;

/**
 * Base Business Logic Widget
 *
 * Base class for Filament widgets that use business logic
 */
abstract class BusinessLogicWidget extends Widget
{
    protected ?FilamentResourceAdapterInterface $businessAdapter = null;

    /**
     * Get the business logic adapter for this widget
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
     * Must be implemented by concrete widgets
     */
    abstract protected function getBusinessAdapterClass(): string;

    /**
     * Get widget data using business logic
     */
    protected function getWidgetData(array $parameters = []): array
    {
        return $this->getBusinessAdapter()->getWidgetData($parameters);
    }

    /**
     * Get chart data using business logic
     */
    protected function getChartData(array $parameters = []): array
    {
        $adapter = $this->getBusinessAdapter();

        // Check if adapter has getChartData method using reflection
        if (method_exists($adapter, 'getChartData')) {
            return call_user_func([$adapter, 'getChartData'], $parameters);
        }

        return $this->getWidgetData($parameters);
    }

    /**
     * Get default parameters from the widget properties
     */
    protected function getDefaultParameters(): array
    {
        $parameters = [];

        // Extract common parameters from widget
        if (property_exists($this, 'record') && $this->record) {
            $parameters['record_id'] = $this->record->id;
        }

        if (property_exists($this, 'filters') && !empty($this->filters)) {
            $parameters['filters'] = $this->filters;
        }

        return $parameters;
    }

    /**
     * Merge parameters with defaults
     */
    protected function mergeParameters(array $parameters = []): array
    {
        return array_merge($this->getDefaultParameters(), $parameters);
    }
}
