<?php

namespace App\Commands\Contracts;

/**
 * Command Pattern Interface
 *
 * All business operations should implement this interface
 * to ensure UI-agnostic execution
 */
interface CommandInterface
{
    /**
     * Execute the command
     *
     * @return mixed
     */
    public function execute();

    /**
     * Validate command parameters before execution
     *
     * @return bool
     */
    public function validate(): bool;

    /**
     * Get validation errors if any
     *
     * @return array
     */
    public function getErrors(): array;
}
