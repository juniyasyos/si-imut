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
            'region_name' => $this->generateRegionName($regionType->type),
            'year' => $year,
            'month' => $month,
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
     * Generate region name based on type
     */
    protected function generateRegionName(string $type): string
    {
        return match (strtolower(str_replace(['🌐', '🏛️', '🏥', ' '], '', $type))) {
            'nasional' => 'Indonesia',
            'provinsi' => $this->faker->randomElement([
                'Jawa Timur',
                'Jawa Barat',
                'Jawa Tengah',
                'DKI Jakarta',
                'Bali',
                'Sumatera Utara',
            ]),
            'rumahsakit' => $this->faker->company() . ' Hospital',
            default => 'Unknown Region',
        };
    }

    /**
     * State for active benchmarking
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State for inactive benchmarking
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State for permanent benchmarking (no end date)
     */
    public function permanent(): static
    {
        return $this->state(fn(array $attributes) => [
            'period_end' => null,
        ]);
    }

    /**
     * State for specific year/month
     */
    public function forYearMonth(int $year, int $month): static
    {
        return $this->state(fn(array $attributes) => [
            'year' => $year,
            'month' => $month,
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
    public function forRegion(int $regionTypeId, ?string $regionName = null): static
    {
        return $this->state(fn(array $attributes) => [
            'region_type_id' => $regionTypeId,
            'region_name' => $regionName ?? $attributes['region_name'],
        ]);
    }
}
