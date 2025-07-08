<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ProjectSetupCommand extends Command
{
    protected $signature = 'project:setup {env=dev : Environment to setup (dev|prod)}';
    protected $description = 'Run project setup tasks (dev or prod)';

    public function handle()
    {
        $env = $this->argument('env');

        // 🚨 Tambahkan perlindungan jika bukan local
        if (!app()->environment('local')) {
            $this->warn('⚠️ Anda sedang tidak berada di environment local. Saat ini: ' . app()->environment());

            if (! $this->confirm('Apakah Anda yakin ingin melanjutkan proses setup ini?')) {
                $this->info('Setup dibatalkan.');
                return Command::SUCCESS;
            }
        }

        $commands = match ($env) {
            'prod' => [
                'git clean -f && git reset --hard && git pull',
                'rm -rf ./storage/debugbar/*.json',
                'php artisan migrate:fresh',
                'php artisan db:seed --class=DatabaseProductionSeeder',
                'php artisan shield:generate --all --panel=admin',
                'php artisan shield:super-admin --user=1',
                'git clean -f && git reset --hard',
            ],
            default => [
                'git clean -f && git reset --hard && git pull',
                'rm -rf ./storage/debugbar/*.json',
                'php artisan migrate:fresh --seed',
                'php artisan shield:generate --all --panel=admin',
                'php artisan shield:super-admin --user=1',
                'git clean -f && git reset --hard',
            ],
        };

        foreach ($commands as $cmd) {
            $this->info("Menjalankan: $cmd");

            $process = Process::fromShellCommandline($cmd);
            $process->setTimeout(300);
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });

            if (!$process->isSuccessful()) {
                $this->error("Gagal menjalankan: $cmd");
                return Command::FAILURE;
            }
        }

        $this->info("✅ Setup untuk environment '$env' selesai.");
        return Command::SUCCESS;
    }
}