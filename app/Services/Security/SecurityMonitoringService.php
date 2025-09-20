<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Security Monitoring Service
 *
 * Monitors security events and triggers alerts
 */
class SecurityMonitoringService
{
    protected array $alertThresholds;
    protected array $alertChannels;

    public function __construct()
    {
        $this->alertThresholds = config('security.monitoring.alert_thresholds', []);
        $this->alertChannels = config('security.monitoring.alert_channels', ['log']);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $type, array $data): void
    {
        $event = [
            'type' => $type,
            'timestamp' => now(),
            'data' => $data,
        ];

        // Log the event
        Log::channel('security')->info("Security event: {$type}", $event);

        // Update counters
        $this->updateEventCounter($type);

        // Check if alerts should be triggered
        $this->checkAlertThresholds($type);
    }

    /**
     * Update event counter
     */
    protected function updateEventCounter(string $type): void
    {
        $hourKey = "security_events:{$type}:" . now()->format('Y-m-d-H');
        $dayKey = "security_events:{$type}:" . now()->format('Y-m-d');

        Cache::increment($hourKey, 1);
        Cache::expire($hourKey, 3600); // 1 hour

        Cache::increment($dayKey, 1);
        Cache::expire($dayKey, 86400); // 24 hours
    }

    /**
     * Check alert thresholds
     */
    protected function checkAlertThresholds(string $type): void
    {
        $thresholdKey = $this->getThresholdKey($type);

        if (!isset($this->alertThresholds[$thresholdKey])) {
            return;
        }

        $threshold = $this->alertThresholds[$thresholdKey];
        $hourKey = "security_events:{$type}:" . now()->format('Y-m-d-H');
        $currentCount = Cache::get($hourKey, 0);

        if ($currentCount >= $threshold) {
            $this->triggerAlert($type, $currentCount, $threshold);
        }
    }

    /**
     * Get threshold key for event type
     */
    protected function getThresholdKey(string $type): string
    {
        $mapping = [
            'failed_login' => 'failed_logins_per_hour',
            'sql_injection' => 'sql_injection_attempts_per_hour',
            'malicious_request' => 'malicious_requests_per_hour',
            'rate_limit_exceeded' => 'rate_limit_violations_per_hour',
        ];

        return $mapping[$type] ?? $type . '_per_hour';
    }

    /**
     * Trigger security alert
     */
    protected function triggerAlert(string $type, int $currentCount, int $threshold): void
    {
        $alertKey = "alert_sent:{$type}:" . now()->format('Y-m-d-H');

        // Only send one alert per hour per type
        if (Cache::has($alertKey)) {
            return;
        }

        $alert = [
            'type' => $type,
            'current_count' => $currentCount,
            'threshold' => $threshold,
            'hour' => now()->format('Y-m-d H:00'),
            'timestamp' => now(),
        ];

        foreach ($this->alertChannels as $channel) {
            $this->sendAlert($channel, $alert);
        }

        // Mark alert as sent
        Cache::put($alertKey, true, 3600);
    }

    /**
     * Send alert via specified channel
     */
    protected function sendAlert(string $channel, array $alert): void
    {
        switch ($channel) {
            case 'log':
                $this->sendLogAlert($alert);
                break;
            case 'mail':
                $this->sendMailAlert($alert);
                break;
            case 'slack':
                $this->sendSlackAlert($alert);
                break;
        }
    }

    /**
     * Send log alert
     */
    protected function sendLogAlert(array $alert): void
    {
        Log::channel('security')->warning('Security alert triggered', $alert);
    }

    /**
     * Send mail alert
     */
    protected function sendMailAlert(array $alert): void
    {
        // Implement email alert
        // You could use Laravel's mail system here
        Log::info('Would send email alert', $alert);
    }

    /**
     * Send Slack alert
     */
    protected function sendSlackAlert(array $alert): void
    {
        // Implement Slack alert
        // You could use Slack webhooks here
        Log::info('Would send Slack alert', $alert);
    }

    /**
     * Get security statistics
     */
    public function getSecurityStats(string $period = 'hour'): array
    {
        $format = $period === 'hour' ? 'Y-m-d-H' : 'Y-m-d';
        $key = now()->format($format);

        $eventTypes = [
            'failed_login',
            'sql_injection',
            'malicious_request',
            'rate_limit_exceeded',
            'blocked_ip',
        ];

        $stats = [];
        foreach ($eventTypes as $type) {
            $cacheKey = "security_events:{$type}:{$key}";
            $stats[$type] = Cache::get($cacheKey, 0);
        }

        return $stats;
    }

    /**
     * Get security trends
     */
    public function getSecurityTrends(int $hours = 24): array
    {
        $trends = [];
        $now = now();

        for ($i = 0; $i < $hours; $i++) {
            $hour = $now->copy()->subHours($i);
            $key = $hour->format('Y-m-d-H');

            $hourStats = [];
            $eventTypes = ['failed_login', 'sql_injection', 'malicious_request', 'rate_limit_exceeded'];

            foreach ($eventTypes as $type) {
                $cacheKey = "security_events:{$type}:{$key}";
                $hourStats[$type] = Cache::get($cacheKey, 0);
            }

            $trends[$key] = $hourStats;
        }

        return array_reverse($trends, true);
    }

    /**
     * Get blocked IPs
     */
    public function getBlockedIps(): array
    {
        $blockedIps = [];

        // This is a simplified approach - in production you might store this differently
        $keys = Cache::getRedis()->keys('*blocked_ip:*');

        foreach ($keys as $key) {
            if (Cache::has($key)) {
                $ip = str_replace('blocked_ip:', '', $key);
                $blockedIps[] = [
                    'ip' => $ip,
                    'blocked_at' => now(), // You might want to store this timestamp
                    'reason' => 'Automatic block due to suspicious activity',
                ];
            }
        }

        return $blockedIps;
    }

    /**
     * Unblock IP address
     */
    public function unblockIp(string $ip): bool
    {
        $keys = [
            "blocked_ip:{$ip}",
            "malicious_activity:{$ip}",
            "sql_injection_attempts:{$ip}",
            "suspicious_activity:{$ip}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Log::info('IP address unblocked', ['ip' => $ip]);

        return true;
    }

    /**
     * Block IP address manually
     */
    public function blockIp(string $ip, string $reason = 'Manual block', int $duration = 3600): bool
    {
        $blockKey = "blocked_ip:{$ip}";
        Cache::put($blockKey, true, $duration);

        $this->logSecurityEvent('manual_ip_block', [
            'ip' => $ip,
            'reason' => $reason,
            'duration' => $duration,
        ]);

        return true;
    }

    /**
     * Check if IP is blocked
     */
    public function isIpBlocked(string $ip): bool
    {
        return Cache::has("blocked_ip:{$ip}");
    }

    /**
     * Get suspicious IPs
     */
    public function getSuspiciousIps(int $threshold = 5): array
    {
        $suspicious = [];
        $eventTypes = ['malicious_request', 'sql_injection', 'rate_limit_exceeded'];
        $hourKey = now()->format('Y-m-d-H');

        // This is simplified - in production you might want a more sophisticated approach
        foreach ($eventTypes as $type) {
            $keys = Cache::getRedis()->keys("*{$type}:*");

            foreach ($keys as $key) {
                if (strpos($key, $hourKey) !== false) {
                    $count = Cache::get($key, 0);
                    if ($count >= $threshold) {
                        $ip = $this->extractIpFromKey($key);
                        if ($ip) {
                            $suspicious[$ip] = ($suspicious[$ip] ?? 0) + $count;
                        }
                    }
                }
            }
        }

        // Sort by activity level
        arsort($suspicious);

        return array_map(function ($count, $ip) {
            return [
                'ip' => $ip,
                'activity_count' => $count,
                'risk_level' => $this->calculateRiskLevel($count),
            ];
        }, $suspicious, array_keys($suspicious));
    }

    /**
     * Extract IP from cache key
     */
    protected function extractIpFromKey(string $key): ?string
    {
        // Extract IP from keys like "security_events:type:ip:timestamp"
        $parts = explode(':', $key);
        if (count($parts) >= 3) {
            return $parts[2];
        }
        return null;
    }

    /**
     * Calculate risk level based on activity count
     */
    protected function calculateRiskLevel(int $count): string
    {
        if ($count >= 20) {
            return 'critical';
        } elseif ($count >= 10) {
            return 'high';
        } elseif ($count >= 5) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Generate security report
     */
    public function generateSecurityReport(string $period = 'day'): array
    {
        return [
            'period' => $period,
            'generated_at' => now(),
            'stats' => $this->getSecurityStats($period),
            'trends' => $this->getSecurityTrends($period === 'day' ? 24 : 168), // 24h or 1 week
            'blocked_ips' => $this->getBlockedIps(),
            'suspicious_ips' => $this->getSuspiciousIps(),
            'alerts_triggered' => $this->getTriggeredAlerts($period),
        ];
    }

    /**
     * Get triggered alerts for period
     */
    protected function getTriggeredAlerts(string $period): array
    {
        // This would typically come from a database or log analysis
        // For now, return empty array
        return [];
    }
}
