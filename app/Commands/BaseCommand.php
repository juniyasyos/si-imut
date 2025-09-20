<?php

namespace App\Commands;

use App\Commands\Contracts\CommandInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Base Command Implementation
 *
 * Provides common functionality for all commands
 */
abstract class BaseCommand implements CommandInterface
{
    protected array $data = [];
    protected array $errors = [];
    protected array $validationRules = [];
    protected array $validationMessages = [];

    /**
     * Set command data
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get command data
     *
     * @return array
     */
    protected function getData(): array
    {
        return $this->data;
    }

    /**
     * Validate command parameters
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (empty($this->validationRules)) {
            return true;
        }

        try {
            $validator = Validator::make($this->data, $this->validationRules, $this->validationMessages);

            if ($validator->fails()) {
                $this->errors = $validator->errors()->toArray();
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->errors = ['validation' => [$e->getMessage()]];
            return false;
        }
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Execute with validation
     *
     * @return mixed
     * @throws ValidationException
     */
    public function executeWithValidation()
    {
        if (!$this->validate()) {
            throw new ValidationException(
                Validator::make($this->data, $this->validationRules, $this->validationMessages)
            );
        }

        return $this->execute();
    }

    /**
     * Set validation rules
     *
     * @param array $rules
     * @return $this
     */
    protected function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Set validation messages
     *
     * @param array $messages
     * @return $this
     */
    protected function setValidationMessages(array $messages): self
    {
        $this->validationMessages = $messages;
        return $this;
    }

    /**
     * Abstract execute method to be implemented by concrete commands
     */
    abstract public function execute();
}
