<?php

namespace Database\Factories;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImutBenchmarking>
 */
class ImutBenchmarkingFactory extends Factory
{
    protected $model = ImutBenchmarking::class;

    public function definition(): array
    {
        // Ambil salah satu RegionType secara acak
        $regionType = RegionType::inRandomOrder()->first();

        // Fallback jika belum ada RegionType
        if (!$regionType) {
            $regionType = RegionType::factory()->create(['type' => 'nasional']);
        }

        $year = $this->faker->numberBetween(2022, 2025);
        $month = $this->faker->numberBetween(1, 12);

        // Generate period dates based on year/month
        $periodStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $this->faker->boolean(70) // 70% chance to have end date
            ? \Carbon\Carbon::create($year, $month, 1)->endOfMonth()
            : null;

        return [
            'imut_data_id' => ImutData::factory(),
            'region_type_id' => $regionType->id,
            'benchmark_value' => $this->faker->randomFloat(2, 70, 100),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'is_active' => $this->faker->boolean(90), // 90% chance to be active
            'notes' => $this->faker->optional(0.3)->sentence(), // 30% chance to have notes
            'created_by' => User::inRandomOrder()->first()?->id,
            'updated_by' => User::inRandomOrder()->first()?->id,
        ];
    }

    /**
     * State for specific year/month
     */
    public function forYearMonth(int $year, int $month): static
    {
        return $this->state(fn(array $attributes) => [
            'period_start' => \Carbon\Carbon::create($year, $month, 1)->startOfMonth(),
            'period_end' => \Carbon\Carbon::create($year, $month, 1)->endOfMonth(),
        ]);
    }

    /**
     * State for specific indicator
     */
    public function forIndicator(int $imutDataId): static
    {
        return $this->state(fn(array $attributes) => [
            'imut_data_id' => $imutDataId,
        ]);
    }

    /**
     * State for specific region
     */
    public function forRegion(int $regionTypeId): static
    {
        return $this->state(fn(array $attributes) => [
            'region_type_id' => $regionTypeId,
        ]);
    }
}
