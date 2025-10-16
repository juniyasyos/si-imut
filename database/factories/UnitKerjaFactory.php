<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Organization\Models\UnitKerja>
 */
class UnitKerjaFactory extends Factory
{
    protected $model = \App\Domains\Organization\Models\UnitKerja::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_name' => fake()->unique()->company(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
