<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup {--force : Force setup even if already configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application with required configurations and settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up application...');

        // Run migrations
        $this->info('Running migrations...');
        $this->call('migrate', ['--force' => true]);

        // Run settings migrations
        $this->info('Running settings migrations...');
        $this->call('settings:migrate');

        // Ensure KaidoSettings exist
        $this->info('Ensuring KaidoSettings...');
        $this->call('settings:ensure-kaido');

        // Seed database if needed
        if ($this->option('force') || $this->confirm('Do you want to seed the database?', false)) {
            $this->info('Seeding database...');
            $this->call('db:seed', ['--class' => 'KaidoSettingSeeder']);
        }

        // Cache configurations
        $this->info('Caching configurations...');
        $this->call('config:cache');

        $this->info('✓ Application setup completed successfully!');

        return 0;
    }
}
