<?php

namespace Database\Factories;

use App\Models\ImutData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImutProfile>
 */
class ImutProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => $this->faker->word(),
            'imut_data_id' => ImutData::factory(),
            'valid_from' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'valid_until' => null, // Most profiles are valid indefinitely
            'rationale' => $this->faker->paragraph(),
            'quality_dimension' => $this->faker->word(),
            'objective' => $this->faker->sentence(),
            'operational_definition' => $this->faker->sentence(),
            'indicator_type' => $this->faker->randomElement(['process', 'output', 'outcome']),
            'numerator_formula' => $this->faker->sentence(),
            'denominator_formula' => $this->faker->sentence(),
            'inclusion_criteria' => $this->faker->paragraph(),
            'exclusion_criteria' => $this->faker->paragraph(),
            'data_source' => $this->faker->company(),
            'data_collection_frequency' => $this->faker->randomElement(['Bulanan', 'Triwulan', 'Tahunan']),
            'analysis_plan' => $this->faker->paragraph(),
            'target_operator' => $this->faker->randomElement(['=', '>=', '<=', '<', '>']),
            'target_value' => $this->faker->numberBetween(70, 100),
            'analysis_period_type' => $this->faker->randomElement(['mingguan', 'bulanan']),
            'analysis_period_value' => $this->faker->numberBetween(1, 12),
            'data_collection_method' => $this->faker->sentence(),
            'sampling_method' => $this->faker->sentence(),
            'data_collection_tool' => $this->faker->paragraph(),
            'responsible_person' => $this->faker->name(),
        ];
    }

    /**
     * Create a profile that is valid for a specific period
     */
    public function validForPeriod($startDate, $endDate = null)
    {
        return $this->state(function (array $attributes) use ($startDate, $endDate) {
            return [
                'valid_from' => is_string($startDate) ? $startDate : $startDate->format('Y-m-d'),
                'valid_until' => $endDate ? (is_string($endDate) ? $endDate : $endDate->format('Y-m-d')) : null,
            ];
        });
    }

    /**
     * Create a profile that is valid from a specific date
     */
    public function validFrom($date)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'valid_from' => is_string($date) ? $date : $date->format('Y-m-d'),
                'valid_until' => null,
            ];
        });
    }
}
