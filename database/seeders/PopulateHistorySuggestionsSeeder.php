<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnhancedFormField;
use App\Models\FieldResponse;

class PopulateHistorySuggestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all text fields
        $textFields = EnhancedFormField::where('field_type', 'text')->get();

        foreach ($textFields as $field) {
            // Get history suggestions from existing responses
            $suggestions = FieldResponse::where('form_field_id', $field->id)
                ->whereNotNull('field_value')
                ->where('field_value', '!=', '')
                ->orderBy('created_at', 'desc')
                ->limit(20) // Get more to ensure we have unique values
                ->pluck('field_value')
                ->filter(function ($value) {
                    // Only include string values, not arrays
                    return is_string($value) && !empty(trim($value));
                })
                ->unique()
                ->take(10) // Keep top 10 unique suggestions
                ->values()
                ->toArray();

            if (!empty($suggestions)) {
                $field->update(['history_suggestions' => $suggestions]);
                $this->command->info("Updated field {$field->id} with " . count($suggestions) . " suggestions");
            }
        }

        $this->command->info('History suggestions populated successfully');
    }
}
