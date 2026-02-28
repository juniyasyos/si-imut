<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ImutProfile;

class FixProfileDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imut:fix-dates {--dry-run : Do not save changes, just show what would be fixed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically correct profiles where valid_from is after valid_until';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dry = $this->option('dry-run');
        $query = ImutProfile::query()
            ->whereNotNull('valid_from')
            ->whereNotNull('valid_until')
            ->whereColumn('valid_from', '>', 'valid_until');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No invalid profiles to fix.');
            return 0;
        }

        $this->warn("Found {$count} profile(s) with inverted dates.\n");

        $rows = [];
        $query->orderBy('id')->chunk(50, function ($profiles) use (&$rows, $dry) {
            foreach ($profiles as $p) {
                $rows[] = [
                    'id' => $p->id,
                    'imut_data_id' => $p->imut_data_id,
                    'slug' => $p->slug,
                    'before_from' => $p->valid_from->toDateString(),
                    'before_until' => $p->valid_until->toDateString(),
                ];

                if (! $dry) {
                    // swap the values
                    $tmp = $p->valid_from;
                    $p->valid_from = $p->valid_until;
                    $p->valid_until = $tmp;
                    $p->save();
                }
            }
        });

        $this->table(
            ['ID', 'ImutData', 'Slug', 'From (old)', 'Until (old)'],
            $rows
        );

        if ($dry) {
            $this->info("\nDry run complete; no changes were written.");
        } else {
            $this->info("\nAll inverted ranges have been swapped.\nPlease verify data or run `php artisan imut:check-dates` again.");
        }

        return 0;
    }
}
