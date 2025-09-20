<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SQL Injection Protection Middleware
 *
 * Advanced protection against SQL injection attacks
 */
class SqlInjectionProtectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for SQL injection patterns
        if ($this->containsSqlInjection($request)) {
            $this->logSqlInjectionAttempt($request);

            // Block the request if configured to do so
            if (config('security.sql_injection.block_requests', true)) {
                return $this->buildBlockedResponse($request);
            }
        }

        return $next($request);
    }

    /**
     * Check if request contains SQL injection patterns
     */
    protected function containsSqlInjection(Request $request): bool
    {
        $inputs = $this->getAllInputSources($request);

        foreach ($inputs as $source => $data) {
            if (is_array($data)) {
                if ($this->checkArrayForSqlInjection($data)) {
                    return true;
                }
            } elseif (is_string($data)) {
                if ($this->checkStringForSqlInjection($data)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all input sources to check
     */
    protected function getAllInputSources(Request $request): array
    {
        return [
            'query' => $request->query->all(),
            'post' => $request->request->all(),
            'headers' => $request->headers->all(),
            'uri' => $request->getRequestUri(),
            'user_agent' => $request->userAgent(),
        ];
    }

    /**
     * Recursively check array for SQL injection
     */
    protected function checkArrayForSqlInjection(array $data): bool
    {
        foreach ($data as $value) {
            if (is_array($value)) {
                if ($this->checkArrayForSqlInjection($value)) {
                    return true;
                }
            } elseif (is_string($value)) {
                if ($this->checkStringForSqlInjection($value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check string for SQL injection patterns
     */
    protected function checkStringForSqlInjection(string $input): bool
    {
        if (empty($input)) {
            return false;
        }

        // Normalize input for better detection
        $normalizedInput = $this->normalizeInput($input);

        // Check against SQL injection patterns
        $patterns = $this->getSqlInjectionPatterns();

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalizedInput)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize input for pattern matching
     */
    protected function normalizeInput(string $input): string
    {
        // Convert to lowercase
        $input = strtolower($input);

        // Remove extra whitespace
        $input = preg_replace('/\s+/', ' ', $input);

        // Remove common comment markers
        $input = preg_replace('/\/\*.*?\*\//', '', $input);
        $input = preg_replace('/--.*$/', '', $input);
        $input = preg_replace('/#.*$/', '', $input);

        // URL decode
        $input = urldecode($input);

        // HTML decode
        $input = html_entity_decode($input, ENT_QUOTES | ENT_HTML5);

        return trim($input);
    }

    /**
     * Get SQL injection detection patterns
     */
    protected function getSqlInjectionPatterns(): array
    {
        return [
            // Union-based injection
            '/\bunion\s+(all\s+)?select\b/i',
            '/\bunion\s+.*\bselect\s+.*\bfrom\b/i',

            // Boolean-based blind injection
            '/\b(and|or)\s+\d+\s*=\s*\d+/i',
            '/\b(and|or)\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i',
            '/\b(and|or)\s+\w+\s*=\s*\w+/i',

            // Time-based blind injection
            '/\bwaitfor\s+delay\b/i',
            '/\bsleep\s*\(/i',
            '/\bbenchmark\s*\(/i',
            '/\bpg_sleep\s*\(/i',

            // Error-based injection
            '/\bconvert\s*\(\s*int\s*,/i',
            '/\bcast\s*\(\s*.*\s+as\s+int\s*\)/i',
            '/\bextractvalue\s*\(/i',
            '/\bupdatexml\s*\(/i',

            // Stacked queries
            '/;\s*(insert|update|delete|drop|create|alter|exec|execute)\b/i',

            // Comment-based injection
            '/\/\*.*?\*\//s',
            '/--[\s\S]*$/m',
            '/#[\s\S]*$/m',

            // Common SQL keywords in injection context
            '/\b(select|insert|update|delete|drop|create|alter|exec|execute|declare|open|fetch|close|deallocate)\b.*\b(from|into|set|where|table|database|schema|procedure|function)\b/i',

            // Information schema queries
            '/\binformation_schema\b/i',
            '/\bsys\.(tables|columns|databases)/i',
            '/\bmysql\.(user|db)/i',

            // Hex encoding attempts
            '/0x[0-9a-f]+/i',

            // Concatenation attempts
            '/\|\|/i', // PostgreSQL concatenation
            '/\bconcat\s*\(/i',
            '/\+.*[\'"].*\+/i', // String concatenation

            // Common injection payloads
            '/\'\s*(or|and)\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?\s*(--|\#|\/\*)/i',
            '/\'\s*(or|and)\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?\s*[\'"]?$/i',
            '/\badmin[\'"]?\s*--/i',
            '/\bor\s+1\s*=\s*1/i',
            '/\band\s+1\s*=\s*1/i',

            // Database-specific functions
            '/\b(user|database|version|@@version|@@servername|host_name|db_name)\s*\(/i',
            '/\bload_file\s*\(/i',
            '/\binto\s+outfile\b/i',
            '/\binto\s+dumpfile\b/i',

            // Blind injection techniques
            '/\bsubstring\s*\(/i',
            '/\bmid\s*\(/i',
            '/\bleft\s*\(/i',
            '/\bright\s*\(/i',
            '/\bchar\s*\(/i',
            '/\bascii\s*\(/i',
            '/\bord\s*\(/i',

            // NoSQL injection patterns
            '/\$ne\s*:/i',
            '/\$gt\s*:/i',
            '/\$regex\s*:/i',
            '/\$where\s*:/i',
        ];
    }

    /**
     * Log SQL injection attempt
     */
    protected function logSqlInjectionAttempt(Request $request): void
    {
        logger()->critical('SQL injection attempt detected', [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'inputs' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Increment attack counter
        $attackKey = 'sql_injection_attempts:' . $request->ip();
        cache()->increment($attackKey, 1);
        cache()->expire($attackKey, 3600); // 1 hour

        // Auto-block IP after multiple attempts
        $attempts = cache()->get($attackKey, 0);
        if ($attempts >= 2) {
            $blockKey = 'blocked_ip:' . $request->ip();
            cache()->put($blockKey, true, 3600); // 1 hour block

            logger()->emergency('IP auto-blocked due to SQL injection attempts', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
            ]);

            // Notify security team
            $this->notifySecurityTeam($request, $attempts);
        }
    }

    /**
     * Build response for blocked requests
     */
    protected function buildBlockedResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Request blocked due to security policy.',
            ], 403);
        }

        return response()->view('errors.403', [
            'message' => 'Request blocked due to security policy.',
        ], 403);
    }

    /**
     * Notify security team about attack
     */
    protected function notifySecurityTeam(Request $request, int $attempts): void
    {
        // This could send email, Slack notification, etc.
        // For now, we'll just log it with high priority

        logger()->emergency('Multiple SQL injection attempts - Security team notification', [
            'ip' => $request->ip(),
            'attempts' => $attempts,
            'last_attempt' => [
                'uri' => $request->getRequestUri(),
                'user_agent' => $request->userAgent(),
                'inputs' => $request->all(),
            ],
            'timestamp' => now(),
        ]);

        // You could integrate with external services here:
        // - Send email to security team
        // - Post to Slack channel
        // - Create incident ticket
        // - Update firewall rules
    }
}
