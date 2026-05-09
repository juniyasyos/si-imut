<?php

namespace App\Console\Commands;

use App\Models\FormTemplate;
use Illuminate\Console\Command;

class SyncFormTemplateDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'form-template:sync-dates {--dry-run : Do not save changes, only show what would be updated} {--delete-orphans : Delete form templates that do not have a related ImutProfile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize form template valid_from and valid_until with the related ImutProfile dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleteOrphans = (bool) $this->option('delete-orphans');

        $templates = FormTemplate::query()
            ->with('imutProfile')
            ->orderBy('imut_profile_id')
            ->orderBy('id')
            ->get();

        if ($templates->isEmpty()) {
            $this->info('No form templates found.');
            return self::SUCCESS;
        }

        $rows = [];
        $updatedCount = 0;
        $skippedCount = 0;
        $deletedCount = 0;

        foreach ($templates as $template) {
            $profile = $template->imutProfile;

            if (! $profile) {
                if ($deleteOrphans) {
                    $deletedCount++;

                    if (! $dryRun) {
                        $template->delete();
                    }

                    $rows[] = [
                        'id' => $template->id,
                        'profile_id' => '-',
                        'version' => $template->version,
                        'old_from' => $template->valid_from?->toDateString() ?? '-',
                        'old_until' => $template->valid_until?->toDateString() ?? '-',
                        'new_from' => '-',
                        'new_until' => '-',
                        'status' => $dryRun ? 'would delete orphan' : 'deleted orphan',
                    ];
                } else {
                    $skippedCount++;
                    $rows[] = [
                        'id' => $template->id,
                        'profile_id' => '-',
                        'version' => $template->version,
                        'old_from' => $template->valid_from?->toDateString() ?? '-',
                        'old_until' => $template->valid_until?->toDateString() ?? '-',
                        'new_from' => '-',
                        'new_until' => '-',
                        'status' => 'skipped: no profile',
                    ];
                }

                continue;
            }

            $targetFrom = $profile->valid_from?->toDateString();
            $targetUntil = $profile->valid_until?->toDateString();

            $currentFrom = $template->valid_from?->toDateString();
            $currentUntil = $template->valid_until?->toDateString();

            $needsUpdate = $currentFrom !== $targetFrom || $currentUntil !== $targetUntil;

            if ($needsUpdate) {
                $updatedCount++;

                if (! $dryRun) {
                    $template->valid_from = $targetFrom;
                    $template->valid_until = $targetUntil;
                    $template->save();
                }
            } else {
                $skippedCount++;
            }

            $rows[] = [
                'id' => $template->id,
                'profile_id' => $profile->id,
                'version' => $template->version,
                'old_from' => $currentFrom ?? '-',
                'old_until' => $currentUntil ?? '-',
                'new_from' => $targetFrom ?? '-',
                'new_until' => $targetUntil ?? '-',
                'status' => $needsUpdate ? ($dryRun ? 'would update' : 'updated') : 'already synced',
            ];
        }

        $this->table(
            ['ID', 'Profile', 'Version', 'Old From', 'Old Until', 'New From', 'New Until', 'Status'],
            $rows
        );

        $this->newLine();
        $this->info('Processed: ' . $templates->count());
        $this->info('Changed: ' . $updatedCount);
        $this->info('Deleted orphans: ' . $deletedCount);
        $this->info('Unchanged / skipped: ' . $skippedCount);

        if ($dryRun) {
            $this->comment('Dry run only. Re-run without --dry-run to write the changes.');
            if ($deleteOrphans) {
                $this->comment('Orphan templates were only marked for deletion in the report.');
            }
        }

        return self::SUCCESS;
    }
}
