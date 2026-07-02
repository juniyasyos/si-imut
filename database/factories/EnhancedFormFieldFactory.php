<?php

namespace Database\Factories;

use App\Modules\FormEngine\Models\EnhancedFormField;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnhancedFormFieldFactory extends Factory
{
    protected $model = EnhancedFormField::class;

    public function definition(): array
    {
        return [
            'field_key' => 'field_' . fake()->unique()->word(),
            'field_label' => fake()->word(),
            'field_type' => 'text',
            'validation_config' => [],
            'compliance_weight' => 1,
            'is_critical_field' => false,
            'order_index' => 1,
        ];
    }
}
