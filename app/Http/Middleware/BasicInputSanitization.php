<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic Input Sanitization Middleware
 * Simple protection for internal company app
 */
class BasicInputSanitization
{
    /**
     * Dangerous patterns to block
     */
    protected array $dangerousPatterns = [
        // Basic XSS patterns
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
        '/javascript\s*:/i',
        '/on\w+\s*=/i',

        // Basic SQL injection patterns
        '/\bunion\s+select\b/i',
        '/\bselect\b.*\bfrom\b.*\bwhere\b/i',
        '/\bdrop\s+table\b/i',
        '/\binsert\s+into\b/i',
        '/\bdelete\s+from\b/i',
        '/\bupdate\b.*\bset\b/i',

        // Other dangerous patterns
        '/\beval\s*\(/i',
        '/\bexec\s*\(/i',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->sanitizeInput($request);

        return $next($request);
    }

    /**
     * Sanitize request input
     */
    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        $sanitized = $this->sanitizeArray($input);

        // Replace the input
        $request->replace($sanitized);
    }

    /**
     * Recursively sanitize array
     */
    protected function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
        }

        return $data;
    }

    /**
     * Sanitize individual string
     */
    protected function sanitizeString(string $value): string
    {
        // Check for dangerous patterns
        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                // Log the attempt
                logger()->warning('Dangerous input detected', [
                    'pattern' => $pattern,
                    'value' => $value,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Remove the dangerous content
                $value = preg_replace($pattern, '', $value);
            }
        }

        // Basic HTML entity encoding for display safety
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
