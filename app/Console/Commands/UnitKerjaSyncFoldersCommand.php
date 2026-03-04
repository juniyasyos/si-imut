<?php

namespace App\Console\Commands;

use App\Models\UnitKerja;
use App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Throwable;

class UnitKerjaSyncFoldersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unit-kerja:sync-folders 
                            {--check : Only check missing folders without creating}
                            {--unit-id=* : Sync specific unit kerja by ID}
                            {--unit-slug=* : Sync specific unit kerja by slug}
                            {--dry-run : Show what would be done without executing}
                            {--force : Force recreate all folders (danger!)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing folders for unit kerja (after database migration from prod)';

    protected UnitKerjaFolderRepositoryInterface $repository;

    public function __construct(UnitKerjaFolderRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Unit Kerja Folder Sync Tool');
        $this->info('=====================================');

        try {
            // Get unit kerja to process
            $unitKerjaCollection = $this->getUnitKerjaToProcess();

            if ($unitKerjaCollection->isEmpty()) {
                $this->warn('❌ No unit kerja found to process.');
                return self::SUCCESS;
            }

            $this->info("📊 Found {$unitKerjaCollection->count()} unit kerja to check");
            $this->newLine();

            // Analyze current state
            $analysis = $this->analyzeCurrentState($unitKerjaCollection);
            $this->displayAnalysis($analysis);

            // Check mode - just report
            if ($this->option('check')) {
                return self::SUCCESS;
            }

            // Confirm before proceeding (unless dry-run)
            if (!$this->option('dry-run') && !$this->confirmProceed($analysis)) {
                $this->warn('⚠️ Operation cancelled by user.');
                return self::SUCCESS;
            }

            // Process missing folders
            return $this->processMissingFolders($analysis);
        } catch (Throwable $e) {
            $this->error("❌ Command failed: {$e->getMessage()}");
            Log::error("Unit Kerja Sync Folders failed", [
                'exception' => $e,
                'options' => $this->options(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Get unit kerja collection based on options
     */
    private function getUnitKerjaToProcess()
    {
        $query = UnitKerja::query();

        // Filter by specific IDs
        if ($unitIds = $this->option('unit-id')) {
            $query->whereIn('id', $unitIds);
        }

        // Filter by specific slugs
        if ($unitSlugs = $this->option('unit-slug')) {
            $query->whereIn('slug', $unitSlugs);
        }

        return $query->orderBy('unit_name')->get();
    }

    /**
     * Analyze current folder state
     */
    private function analyzeCurrentState($unitKerjaCollection): array
    {
        $missing = collect();
        $existing = collect();
        $broken = collect();

        foreach ($unitKerjaCollection as $unitKerja) {
            $folderState = $this->checkFolderState($unitKerja);

            switch ($folderState['status']) {
                case 'missing':
                    $missing->push($unitKerja);
                    break;
                case 'existing':
                    $existing->push($unitKerja);
                    break;
                case 'broken':
                    $broken->push(['unit' => $unitKerja, 'issue' => $folderState['issue']]);
                    break;
            }
        }

        return [
            'total' => $unitKerjaCollection->count(),
            'missing' => $missing,
            'existing' => $existing,
            'broken' => $broken,
        ];
    }

    /**
     * Check folder state for a unit kerja
     */
    private function checkFolderState(UnitKerja $unitKerja): array
    {
        $collection = Str::slug($unitKerja->unit_name);

        // Check for duplicate main folders (multiple folders with same collection)
        $mainFolders = Folder::where('collection', $collection)
            ->whereNull('parent_id')
            ->get();

        if ($mainFolders->count() > 1) {
            return [
                'status' => 'broken',
                'issue' => "Duplicate main folders detected: {$mainFolders->count()} folders with collection '{$collection}'"
            ];
        }

        $mainFolder = $mainFolders->first();

        if (!$mainFolder) {
            return ['status' => 'missing', 'issue' => null];
        }

        // Check subfolders count
        $subfoldersCount = Folder::where('parent_id', $mainFolder->id)->count();
        $expectedSubfolders = 5; // dari standardSubfolders di repository

        if ($subfoldersCount < $expectedSubfolders) {
            return [
                'status' => 'broken',
                'issue' => "Missing subfolders: {$subfoldersCount}/{$expectedSubfolders}"
            ];
        }

        return ['status' => 'existing', 'issue' => null];
    }

    /**
     * Display analysis results
     */
    private function displayAnalysis(array $analysis): void
    {
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['✅ Complete', $analysis['existing']->count(), $this->percentage($analysis['existing']->count(), $analysis['total'])],
                ['❌ Missing Folders', $analysis['missing']->count(), $this->percentage($analysis['missing']->count(), $analysis['total'])],
                ['⚠️ Broken Structure', $analysis['broken']->count(), $this->percentage($analysis['broken']->count(), $analysis['total'])],
                ['📊 Total', $analysis['total'], '100%'],
            ]
        );

        // Show details for missing
        if ($analysis['missing']->isNotEmpty()) {
            $this->newLine();
            $this->warn("❌ Unit Kerja Missing Folders ({$analysis['missing']->count()}):");
            foreach ($analysis['missing'] as $unit) {
                $this->line("  - ID {$unit->id}: {$unit->unit_name}");
            }
        }

        // Show details for broken
        if ($analysis['broken']->isNotEmpty()) {
            $this->newLine();
            $this->warn("⚠️ Unit Kerja with Broken Structure ({$analysis['broken']->count()}):");
            foreach ($analysis['broken'] as $item) {
                $unit = $item['unit'];
                $issue = $item['issue'];
                $this->line("  - ID {$unit->id}: {$unit->unit_name} ({$issue})");
            }
        }

        $this->newLine();
    }

    /**
     * Calculate percentage
     */
    private function percentage(int $count, int $total): string
    {
        if ($total === 0) return '0%';
        return round(($count / $total) * 100, 1) . '%';
    }

    /**
     * Confirm before proceeding
     */
    private function confirmProceed(array $analysis): bool
    {
        $needsAction = $analysis['missing']->count() + $analysis['broken']->count();

        if ($needsAction === 0) {
            $this->info('✅ All unit kerja already have complete folder structure!');
            return false;
        }

        if ($this->option('force')) {
            $this->warn('⚠️ FORCE mode: Will recreate ALL folders (existing will be deleted)!');
        }

        // For non-interactive mode, proceed automatically
        if ($this->option('no-interaction')) {
            return true;
        }

        return $this->confirm("Create missing folders for {$needsAction} unit kerja?");
    }

    /**
     * Process missing folders
     */
    private function processMissingFolders(array $analysis): int
    {
        $toProcess = collect();

        // Add missing folders
        $toProcess = $toProcess->merge($analysis['missing']);

        // Add broken structures  
        foreach ($analysis['broken'] as $item) {
            $toProcess->push($item['unit']);
        }

        // Force mode - recreate all
        if ($this->option('force')) {
            $toProcess = $analysis['missing']
                ->merge($analysis['existing'])
                ->merge($analysis['broken']->pluck('unit'));
        }

        if ($toProcess->isEmpty()) {
            $this->info('✅ Nothing to process!');
            return self::SUCCESS;
        }

        $this->info("🚀 Processing {$toProcess->count()} unit kerja...");
        $this->newLine();

        $success = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($toProcess->count());
        $progressBar->start();

        foreach ($toProcess as $unitKerja) {
            try {
                if ($this->option('dry-run')) {
                    $this->line("  [DRY RUN] Would create folders for: {$unitKerja->unit_name}");
                } else {
                    // Check for duplicates before creating
                    if (!$this->option('force') && $this->hasDuplicateFolders($unitKerja)) {
                        $this->line("  ⚠️ Skipping {$unitKerja->unit_name}: Duplicate folders detected. Use --force to recreate.");
                        continue;
                    }

                    // Force mode - delete existing folders first
                    if ($this->option('force')) {
                        $this->deleteExistingFolders($unitKerja);
                    }

                    // Create folders using repository (with duplicate prevention)
                    $this->repository->createFolder($unitKerja);
                }

                $success++;
            } catch (Throwable $e) {
                $failed++;
                Log::error("Failed to create folder for Unit Kerja ID {$unitKerja->id}", [
                    'exception' => $e,
                    'unit_name' => $unitKerja->unit_name,
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->displaySummary($success, $failed, $this->option('dry-run'));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Check if unit kerja already has duplicate folders
     */
    private function hasDuplicateFolders(UnitKerja $unitKerja): bool
    {
        $collection = Str::slug($unitKerja->unit_name);

        $mainFoldersCount = Folder::where('collection', $collection)
            ->whereNull('parent_id')
            ->count();

        return $mainFoldersCount > 0;
    }

    /**
     * Delete existing folders (for force mode)
     */
    private function deleteExistingFolders(UnitKerja $unitKerja): void
    {
        $collection = Str::slug($unitKerja->unit_name);

        $mainFolder = Folder::where('collection', $collection)
            ->whereNull('parent_id')
            ->first();

        if ($mainFolder) {
            // Delete subfolders first
            Folder::where('parent_id', $mainFolder->id)->delete();

            // Delete main folder
            $mainFolder->delete();
        }
    }

    /**
     * Display final summary
     */
    private function displaySummary(int $success, int $failed, bool $isDryRun): void
    {
        $mode = $isDryRun ? '[DRY RUN] ' : '';

        $this->info("📋 {$mode}Summary:");
        $this->info("✅ Successful: {$success}");

        if ($failed > 0) {
            $this->error("❌ Failed: {$failed}");
        }

        if (!$isDryRun && $success > 0) {
            $subfoldersCreated = $success * 5; // 5 subfolders per unit
            $totalFolders = $success + $subfoldersCreated;
            $this->info("📁 Total folders created: {$totalFolders} ({$success} main + {$subfoldersCreated} subfolders)");
        }

        $this->newLine();
        $this->info($isDryRun ? '🏁 Dry run completed!' : '🏁 Sync completed!');
    }
}
