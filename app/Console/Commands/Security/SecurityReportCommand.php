<?php

namespace App\Console\Commands\Security;

use Illuminate\Console\Command;
use App\Services\Security\SecurityMonitoringService;

class SecurityReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:report
                            {--period=day : Report period (hour/day/week)}
                            {--format=table : Output format (table/json)}
                            {--save : Save report to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate security report with statistics and alerts';

    /**
     * Execute the console command.
     */
    public function handle(SecurityMonitoringService $securityService): int
    {
        $period = $this->option('period');
        $format = $this->option('format');
        $save = $this->option('save');

        $this->info("Generating security report for period: {$period}");

        $report = $securityService->generateSecurityReport($period);

        if ($format === 'json') {
            $this->displayJsonReport($report);
        } else {
            $this->displayTableReport($report);
        }

        if ($save) {
            $this->saveReport($report, $period);
        }

        return Command::SUCCESS;
    }

    /**
     * Display report in table format
     */
    protected function displayTableReport(array $report): void
    {
        $this->newLine();
        $this->line('<fg=cyan>═══════════════════════════════════════════════════════════════════════════════════════════════════════</>');
        $this->line('<fg=cyan>                                    SECURITY REPORT                                                     </>');
        $this->line('<fg=cyan>═══════════════════════════════════════════════════════════════════════════════════════════════════════</>');
        $this->newLine();

        // Basic Info
        $this->info("📊 Report Period: " . ucfirst($report['period']));
        $this->info("🕐 Generated At: " . $report['generated_at']->format('Y-m-d H:i:s'));
        $this->newLine();

        // Security Statistics
        $this->line('<fg=yellow>📈 Security Statistics:</>');
        $headers = ['Event Type', 'Count', 'Status'];
        $rows = [];

        foreach ($report['stats'] as $eventType => $count) {
            $status = $this->getEventStatus($eventType, $count);
            $rows[] = [
                str_replace('_', ' ', ucwords($eventType)),
                $count,
                $status
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // Blocked IPs
        if (!empty($report['blocked_ips'])) {
            $this->line('<fg=red>🚫 Blocked IP Addresses:</>');
            $headers = ['IP Address', 'Blocked At', 'Reason'];
            $rows = [];

            foreach ($report['blocked_ips'] as $blockedIp) {
                $rows[] = [
                    $blockedIp['ip'],
                    $blockedIp['blocked_at'],
                    $blockedIp['reason']
                ];
            }

            $this->table($headers, $rows);
            $this->newLine();
        }

        // Suspicious IPs
        if (!empty($report['suspicious_ips'])) {
            $this->line('<fg=yellow>⚠️  Suspicious IP Addresses:</>');
            $headers = ['IP Address', 'Activity Count', 'Risk Level'];
            $rows = [];

            foreach (array_slice($report['suspicious_ips'], 0, 10) as $suspiciousIp) {
                $riskColor = $this->getRiskColor($suspiciousIp['risk_level']);
                $rows[] = [
                    $suspiciousIp['ip'],
                    $suspiciousIp['activity_count'],
                    "<fg={$riskColor}>" . ucfirst($suspiciousIp['risk_level']) . "</>",
                ];
            }

            $this->table($headers, $rows);
            $this->newLine();
        }

        // Security Trends (simplified)
        if (!empty($report['trends'])) {
            $this->line('<fg=cyan>📊 Security Trends (Last 12 hours):</>');
            $trendData = array_slice($report['trends'], -12, 12, true);

            foreach ($trendData as $hour => $stats) {
                $total = array_sum($stats);
                if ($total > 0) {
                    $this->line("  <fg=blue>{$hour}:</> {$total} events");
                }
            }
            $this->newLine();
        }

        // Recommendations
        $this->displayRecommendations($report);
    }

    /**
     * Display report in JSON format
     */
    protected function displayJsonReport(array $report): void
    {
        $this->line(json_encode($report, JSON_PRETTY_PRINT));
    }

    /**
     * Save report to file
     */
    protected function saveReport(array $report, string $period): void
    {
        $filename = "security_report_{$period}_" . now()->format('Y-m-d_H-i-s') . '.json';
        $path = storage_path("logs/security_reports/{$filename}");

        // Create directory if it doesn't exist
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT));

        $this->info("📁 Report saved to: {$path}");
    }

    /**
     * Get event status based on type and count
     */
    protected function getEventStatus(string $eventType, int $count): string
    {
        if ($count === 0) {
            return '<fg=green>✓ Normal</>';
        }

        $thresholds = [
            'failed_login' => 20,
            'sql_injection' => 5,
            'malicious_request' => 10,
            'rate_limit_exceeded' => 50,
            'blocked_ip' => 5,
        ];

        $threshold = $thresholds[$eventType] ?? 10;

        if ($count >= $threshold * 2) {
            return '<fg=red>⚠ Critical</>';
        } elseif ($count >= $threshold) {
            return '<fg=yellow>⚠ Warning</>';
        } else {
            return '<fg=green>✓ Normal</>';
        }
    }

    /**
     * Get risk color for display
     */
    protected function getRiskColor(string $riskLevel): string
    {
        return match ($riskLevel) {
            'critical' => 'red',
            'high' => 'yellow',
            'medium' => 'blue',
            'low' => 'green',
            default => 'white',
        };
    }

    /**
     * Display security recommendations
     */
    protected function displayRecommendations(array $report): void
    {
        $this->line('<fg=green>💡 Security Recommendations:</>');

        $recommendations = [];

        // Check for high failed login attempts
        if ($report['stats']['failed_login'] > 50) {
            $recommendations[] = "• Consider implementing CAPTCHA for login forms";
            $recommendations[] = "• Review and strengthen password policies";
        }

        // Check for SQL injection attempts
        if ($report['stats']['sql_injection'] > 0) {
            $recommendations[] = "• Review application input validation";
            $recommendations[] = "• Ensure all database queries use prepared statements";
        }

        // Check for suspicious IPs
        if (count($report['suspicious_ips']) > 10) {
            $recommendations[] = "• Consider implementing automatic IP blocking";
            $recommendations[] = "• Review firewall rules and access controls";
        }

        // Check for rate limit violations
        if ($report['stats']['rate_limit_exceeded'] > 100) {
            $recommendations[] = "• Review rate limiting thresholds";
            $recommendations[] = "• Implement progressive rate limiting";
        }

        if (empty($recommendations)) {
            $recommendations[] = "• Security posture looks good! Continue monitoring.";
        }

        foreach ($recommendations as $recommendation) {
            $this->line("  {$recommendation}");
        }

        $this->newLine();
        $this->line('<fg=cyan>═══════════════════════════════════════════════════════════════════════════════════════════════════════</>');
    }
}
