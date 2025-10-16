<?php

namespace Database\Factories;

use App\Models\RegionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Imut\Models\ImutCategory>
 */
class ImutCategoryFactory extends Factory
{
    protected $model = \App\Domains\Imut\Models\ImutCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Contoh: "Mutu Pelayanan Rawat" → MPR
        $categoryWords = $this->faker->unique()->words(3);
        $categoryName = implode(' ', $categoryWords);
        $shortName = strtoupper(collect($categoryWords)->map(fn($word) => $word[0])->implode(''));

        return [
            'category_name' => $categoryName,
            'short_name' => $shortName,
            'description' => $this->faker->sentence(),
        ];
    }
}
