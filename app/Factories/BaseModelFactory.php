<?php

namespace App\Factories;

abstract class BaseModelFactory
{
    /**
     * Create a model instance with default data
     *
     * @param array $attributes
     * @return mixed
     */
    abstract public function create(array $attributes = []);

    /**
     * Validate attributes before creation
     *
     * @param array $attributes
     * @return array
     */
    protected function validateAttributes(array $attributes): array
    {
        return $attributes;
    }

    /**
     * Apply default values
     *
     * @param array $attributes
     * @return array
     */
    protected function applyDefaults(array $attributes): array
    {
        return array_merge($this->getDefaults(), $attributes);
    }

    /**
     * Get default attributes for the model
     *
     * @return array
     */
    protected function getDefaults(): array
    {
        return [];
    }

    /**
     * Execute post-creation logic
     *
     * @param mixed $model
     * @return mixed
     */
    protected function afterCreate($model)
    {
        return $model;
    }
}
