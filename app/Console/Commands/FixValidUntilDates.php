<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ImutProfile;
use Carbon\Carbon;

class FixValidUntilDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imut:fix-valid-until {--dry-run : Do not save changes, just show what would be fixed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix profiles where valid_until is 2025-07-10 and change it to 2025-12-31';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dry = $this->option('dry-run');

        // Target date: 10/07/2025 (July 10, 2025)
        $targetDate = Carbon::parse('2025-07-10');
        $newDate = Carbon::parse('2025-12-31');

        $query = ImutProfile::query()
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', $targetDate->toDateString());

        $count = $query->count();

        if ($count === 0) {
            $this->info('No profiles found with valid_until = 2025-07-10');
            return 0;
        }

        $this->warn("Found {$count} profile(s) with valid_until = {$targetDate->format('d/m/Y')}.\n");

        $rows = [];
        $query->orderBy('id')->chunk(50, function ($profiles) use (&$rows, $dry, $targetDate, $newDate) {
            foreach ($profiles as $p) {
                $rows[] = [
                    'id' => $p->id,
                    'imut_data_id' => $p->imut_data_id,
                    'slug' => $p->slug,
                    'valid_from' => $p->valid_from->translatedFormat('d/m/Y'),
                    'valid_until_old' => $p->valid_until->translatedFormat('d/m/Y'),
                    'valid_until_new' => $newDate->format('d/m/Y'),
                ];

                if (! $dry) {
                    $p->valid_until = $newDate;
                    $p->save();
                }
            }
        });

        $this->table(
            ['ID', 'ImutData', 'Slug', 'Valid From', 'Until (Old)', 'Until (New)'],
            $rows
        );

        if ($dry) {
            $this->info("\nDry run complete; no changes were written.");
            $this->comment("Run without --dry-run to apply changes.");
        } else {
            $this->info("\nAll valid_until dates have been updated to 2025-12-31.");
            $this->comment("Please verify the changes are correct.");
        }

        return 0;
    }
}
