<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Input Sanitization Middleware
 *
 * Sanitizes user input to prevent XSS, SQL injection, and other attacks
 */
class SanitizeInputMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize request inputs
        $this->sanitizeRequestInputs($request);

        // Validate and clean headers
        $this->sanitizeHeaders($request);

        // Check for malicious patterns
        $this->detectMaliciousPatterns($request);

        return $next($request);
    }

    /**
     * Sanitize request inputs
     */
    protected function sanitizeRequestInputs(Request $request): void
    {
        $inputs = $request->all();
        $sanitized = $this->sanitizeArray($inputs);

        // Replace request inputs with sanitized version
        $request->replace($sanitized);
    }

    /**
     * Recursively sanitize array inputs
     */
    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $cleanKey = $this->sanitizeKey($key);

            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$cleanKey] = $this->sanitizeString($value);
            } else {
                $sanitized[$cleanKey] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize array key
     */
    protected function sanitizeKey(string $key): string
    {
        // Remove potentially dangerous characters from keys
        $key = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $key);

        // Limit key length
        return substr($key, 0, 100);
    }

    /**
     * Sanitize string value
     */
    protected function sanitizeString(string $value): string
    {
        // Detect if this is likely HTML content that should be preserved
        if ($this->isRichTextContent($value)) {
            return $this->sanitizeHtml($value);
        }

        // Basic string sanitization for regular inputs
        return $this->sanitizeBasicString($value);
    }

    /**
     * Check if content appears to be rich text/HTML
     */
    protected function isRichTextContent(string $value): bool
    {
        // Simple heuristic: if it contains common HTML tags, treat as rich text
        $htmlTags = ['<p>', '<div>', '<span>', '<strong>', '<em>', '<ul>', '<ol>', '<li>', '<a>', '<img>'];

        foreach ($htmlTags as $tag) {
            if (stripos($value, $tag) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize HTML content while preserving safe tags
     */
    protected function sanitizeHtml(string $html): string
    {
        // Define allowed tags and attributes
        $allowedTags = [
            'p', 'br', 'strong', 'em', 'u', 'b', 'i', 'ul', 'ol', 'li',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'span', 'div'
        ];

        $allowedAttributes = [
            'class', 'id', 'style' // Limited attributes
        ];

        // Remove dangerous tags and attributes
        $html = strip_tags($html, '<' . implode('><', $allowedTags) . '>');

        // Remove dangerous attributes
        $html = $this->removeDangerousAttributes($html, $allowedAttributes);

        // Remove dangerous protocols
        $html = $this->removeDangerousProtocols($html);

        return $html;
    }

    /**
     * Remove dangerous attributes from HTML
     */
    protected function removeDangerousAttributes(string $html, array $allowedAttributes): string
    {
        // Remove script handlers and dangerous attributes
        $dangerousPatterns = [
            '/\son\w+\s*=\s*["\'][^"\']*["\']/i', // onclick, onload, etc.
            '/\sjavascript\s*:/i',                // javascript: protocol
            '/\svbscript\s*:/i',                  // vbscript: protocol
            '/\sdata\s*:/i',                      // data: protocol
        ];

        foreach ($dangerousPatterns as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }

        return $html;
    }

    /**
     * Remove dangerous protocols
     */
    protected function removeDangerousProtocols(string $html): string
    {
        $dangerousProtocols = [
            'javascript:', 'vbscript:', 'data:', 'file:', 'ftp:'
        ];

        foreach ($dangerousProtocols as $protocol) {
            $html = str_ireplace($protocol, '', $html);
        }

        return $html;
    }

    /**
     * Basic string sanitization
     */
    protected function sanitizeBasicString(string $value): string
    {
        // Trim whitespace
        $value = trim($value);

        // Convert special chars to HTML entities to prevent XSS
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Limit string length (configurable)
        $maxLength = config('security.input.max_string_length', 65535);
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }

        return $value;
    }

    /**
     * Sanitize request headers
     */
    protected function sanitizeHeaders(Request $request): void
    {
        $dangerousHeaders = [
            'X-Forwarded-Host',
            'X-Original-URL',
            'X-Rewrite-URL',
        ];

        foreach ($dangerousHeaders as $header) {
            if ($request->hasHeader($header)) {
                logger()->warning('Dangerous header detected and removed', [
                    'header' => $header,
                    'value' => $request->header($header),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Remove the header
                $request->headers->remove($header);
            }
        }
    }

    /**
     * Detect malicious patterns in requests
     */
    protected function detectMaliciousPatterns(Request $request): void
    {
        $allInput = json_encode($request->all());
        $userAgent = $request->userAgent() ?? '';
        $uri = $request->getRequestUri();

        $maliciousPatterns = [
            // SQL Injection patterns
            '/(\bunion\b.*\bselect\b|\bselect\b.*\bfrom\b|\binsert\b.*\binto\b|\bdelete\b.*\bfrom\b|\bdrop\b.*\btable\b)/i',

            // XSS patterns
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/on\w+\s*=/i',

            // Path traversal
            '/\.\.[\/\\\\]/i',
            '/etc\/passwd/i',
            '/windows\/system32/i',

            // Command injection
            '/(\;|\||\&\&|\|\|).*?(cat|ls|pwd|id|whoami|uname|wget|curl|nc|netcat)/i',

            // PHP code injection
            '/<\?php/i',
            '/eval\s*\(/i',
            '/system\s*\(/i',
            '/exec\s*\(/i',

            // LDAP injection
            '/(\(|\)|\*|\||&)/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $allInput) ||
                preg_match($pattern, $userAgent) ||
                preg_match($pattern, $uri)) {

                $this->logMaliciousActivity($request, $pattern);

                // Optionally block the request
                if (config('security.input.block_malicious_requests', true)) {
                    abort(403, 'Malicious input detected');
                }

                break; // Stop checking after first match
            }
        }
    }

    /**
     * Log malicious activity
     */
    protected function logMaliciousActivity(Request $request, string $pattern): void
    {
        logger()->warning('Malicious input pattern detected', [
            'pattern' => $pattern,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'route' => $request->route()?->getName(),
            'user_agent' => $request->userAgent(),
            'inputs' => $request->all(),
            'uri' => $request->getRequestUri(),
        ]);

        // Increment malicious activity counter for this IP
        $key = 'malicious_activity:' . $request->ip();
        cache()->increment($key, 1);
        cache()->expire($key, 3600); // 1 hour

        // Auto-block IP if too many malicious attempts
        $attempts = cache()->get($key, 0);
        if ($attempts >= 3) {
            $blockKey = 'blocked_ip:' . $request->ip();
            cache()->put($blockKey, true, 1800); // 30 minutes

            logger()->error('IP auto-blocked due to repeated malicious activity', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
            ]);
        }
    }
}
