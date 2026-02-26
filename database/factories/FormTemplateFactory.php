<?php

namespace Database\Factories;

use App\Models\FormTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormTemplate>
 */
class FormTemplateFactory extends Factory
{
    protected $model = FormTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            // attach an imut profile so that the `imut_profile_id` column is
            // never null (database enforces not-null).
            'imut_profile_id' => \App\Models\ImutProfile::factory(),
            'compliance_method' => 'simple',
            'auto_fail_on_critical' => false,
            'scoring_config' => [],
        ];
    }
}
