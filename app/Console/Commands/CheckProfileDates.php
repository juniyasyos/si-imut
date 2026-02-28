<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ImutProfile;

class CheckProfileDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imut:check-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan imut_profil table for invalid date ranges (valid_from after valid_until)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking IMUT profiles for invalid date ranges...');

        $query = ImutProfile::query()
            ->whereNotNull('valid_from')
            ->whereNotNull('valid_until')
            ->whereColumn('valid_from', '>', 'valid_until');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No invalid profiles found.');
            return 0;
        }

        $this->warn("Found {$count} profile(s) with valid_from later than valid_until:");

        $query->select(['id', 'imut_data_id', 'valid_from', 'valid_until', 'slug'])
            ->orderBy('valid_from')
            ->chunk(50, function ($profiles) {
                $rows = $profiles->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'imut_data_id' => $p->imut_data_id,
                        'slug' => $p->slug,
                        'valid_from' => $p->valid_from->toDateString(),
                        'valid_until' => $p->valid_until->toDateString(),
                    ];
                })->toArray();

                $this->table(
                    ['ID', 'ImutData', 'Slug', 'Valid From', 'Valid Until'],
                    $rows
                );
            });

        $this->info('Run `php artisan imut:check-dates` regularly or before importing data.');

        return 0;
    }
}
