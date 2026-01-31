<?php

namespace App\Console\Commands;

use App\Models\EnhancedFormField;
use App\Models\FieldResponse;
use Illuminate\Console\Command;

class UpdateFieldHistorySuggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-field-history-suggestions {--field_id= : Update specific field by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update history suggestions for form fields based on existing responses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fieldId = $this->option('field_id');

        if ($fieldId) {
            $field = EnhancedFormField::find($fieldId);
            if (!$field) {
                $this->error("Field with ID {$fieldId} not found.");
                return 1;
            }

            $this->updateFieldHistory($field);
            $this->info("Updated history for field ID {$fieldId}");
        } else {
            $fields = EnhancedFormField::where('field_type', 'text')->get();
            $this->info("Updating history suggestions for {$fields->count()} text fields...");

            $bar = $this->output->createProgressBar($fields->count());
            $bar->start();

            foreach ($fields as $field) {
                $this->updateFieldHistory($field);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('History suggestions updated successfully!');
        }

        return 0;
    }

    private function updateFieldHistory(EnhancedFormField $field): void
    {
        // Get all non-empty responses for this field
        $responses = FieldResponse::where('form_field_id', $field->id)
            ->whereNotNull('field_value')
            ->where('field_value', '!=', '')
            ->pluck('field_value')
            ->unique()
            ->values()
            ->toArray();

        // Only update if we have responses
        if (!empty($responses)) {
            // Sort by frequency (most used first), then alphabetically
            $frequency = array_count_values($responses);
            arsort($frequency);

            // Get unique values sorted by frequency, then alphabetically
            $sortedResponses = array_keys($frequency);
            sort($sortedResponses); // Alphabetical sort for same frequency

            // Limit to last 20 suggestions to avoid too many options
            $historySuggestions = array_slice($sortedResponses, 0, 20);

            $field->update([
                'history_suggestions' => $historySuggestions
            ]);
        }
    }
}
