<?php

namespace App\Console\Commands\Security;

use Illuminate\Console\Command;
use App\Services\Security\SecurityMonitoringService;

class ManageBlockedIpsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:ip
                            {action : Action to perform (list/block/unblock)}
                            {ip? : IP address (required for block/unblock)}
                            {--reason= : Reason for blocking}
                            {--duration=3600 : Block duration in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage blocked IP addresses';

    /**
     * Execute the console command.
     */
    public function handle(SecurityMonitoringService $securityService): int
    {
        $action = $this->argument('action');
        $ip = $this->argument('ip');

        switch ($action) {
            case 'list':
                return $this->listBlockedIps($securityService);

            case 'block':
                return $this->blockIp($securityService, $ip);

            case 'unblock':
                return $this->unblockIp($securityService, $ip);

            default:
                $this->error("Invalid action. Use: list, block, or unblock");
                return Command::FAILURE;
        }
    }

    /**
     * List blocked IP addresses
     */
    protected function listBlockedIps(SecurityMonitoringService $securityService): int
    {
        $blockedIps = $securityService->getBlockedIps();
        $suspiciousIps = $securityService->getSuspiciousIps();

        $this->newLine();
        $this->line('<fg=red>🚫 Blocked IP Addresses:</>');

        if (empty($blockedIps)) {
            $this->info('No IP addresses are currently blocked.');
        } else {
            $headers = ['IP Address', 'Blocked At', 'Reason'];
            $rows = [];

            foreach ($blockedIps as $blockedIp) {
                $rows[] = [
                    $blockedIp['ip'],
                    $blockedIp['blocked_at'],
                    $blockedIp['reason']
                ];
            }

            $this->table($headers, $rows);
        }

        $this->newLine();
        $this->line('<fg=yellow>⚠️  Suspicious IP Addresses:</>');

        if (empty($suspiciousIps)) {
            $this->info('No suspicious IP addresses detected.');
        } else {
            $headers = ['IP Address', 'Activity Count', 'Risk Level', 'Action'];
            $rows = [];

            foreach (array_slice($suspiciousIps, 0, 20) as $suspiciousIp) {
                $action = $suspiciousIp['risk_level'] === 'critical' ?
                    '<fg=red>Consider blocking</>' :
                    '<fg=yellow>Monitor</>';

                $rows[] = [
                    $suspiciousIp['ip'],
                    $suspiciousIp['activity_count'],
                    ucfirst($suspiciousIp['risk_level']),
                    $action
                ];
            }

            $this->table($headers, $rows);
        }

        return Command::SUCCESS;
    }

    /**
     * Block an IP address
     */
    protected function blockIp(SecurityMonitoringService $securityService, ?string $ip): int
    {
        if (!$ip) {
            $this->error('IP address is required for blocking.');
            return Command::FAILURE;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error('Invalid IP address format.');
            return Command::FAILURE;
        }

        if ($securityService->isIpBlocked($ip)) {
            $this->warn("IP address {$ip} is already blocked.");
            return Command::SUCCESS;
        }

        $reason = $this->option('reason') ?: 'Manual block via command';
        $duration = (int) $this->option('duration');

        if ($this->confirm("Are you sure you want to block IP {$ip} for {$duration} seconds?")) {
            $success = $securityService->blockIp($ip, $reason, $duration);

            if ($success) {
                $this->info("✅ IP address {$ip} has been blocked for {$duration} seconds.");
                $this->info("Reason: {$reason}");
            } else {
                $this->error("❌ Failed to block IP address {$ip}.");
                return Command::FAILURE;
            }
        } else {
            $this->info('Block operation cancelled.');
        }

        return Command::SUCCESS;
    }

    /**
     * Unblock an IP address
     */
    protected function unblockIp(SecurityMonitoringService $securityService, ?string $ip): int
    {
        if (!$ip) {
            $this->error('IP address is required for unblocking.');
            return Command::FAILURE;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error('Invalid IP address format.');
            return Command::FAILURE;
        }

        if (!$securityService->isIpBlocked($ip)) {
            $this->warn("IP address {$ip} is not currently blocked.");
            return Command::SUCCESS;
        }

        if ($this->confirm("Are you sure you want to unblock IP {$ip}?")) {
            $success = $securityService->unblockIp($ip);

            if ($success) {
                $this->info("✅ IP address {$ip} has been unblocked.");
            } else {
                $this->error("❌ Failed to unblock IP address {$ip}.");
                return Command::FAILURE;
            }
        } else {
            $this->info('Unblock operation cancelled.');
        }

        return Command::SUCCESS;
    }
}
