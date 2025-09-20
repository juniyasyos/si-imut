<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | SI-IMUT application, including rate limiting, input validation,
    | and protection mechanisms.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        /*
        |--------------------------------------------------------------------------
        | Whitelist IPs
        |--------------------------------------------------------------------------
        |
        | IP addresses that are exempt from rate limiting.
        |
        */
        'whitelist_ips' => [
            '127.0.0.1',
            '::1',
            // Add your server/office IPs here
        ],

        /*
        |--------------------------------------------------------------------------
        | Default Limits
        |--------------------------------------------------------------------------
        |
        | Default rate limiting configuration for different request types.
        |
        */
        'limits' => [
            'api' => [
                'requests' => 100,
                'per_minutes' => 1,
            ],
            'web' => [
                'requests' => 60,
                'per_minutes' => 1,
            ],
            'auth' => [
                'requests' => 5,
                'per_minutes' => 15,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Sanitization
    |--------------------------------------------------------------------------
    */
    'input' => [
        /*
        |--------------------------------------------------------------------------
        | Maximum String Length
        |--------------------------------------------------------------------------
        |
        | Maximum allowed length for string inputs.
        |
        */
        'max_string_length' => 65535,

        /*
        |--------------------------------------------------------------------------
        | Block Malicious Requests
        |--------------------------------------------------------------------------
        |
        | Whether to automatically block requests with malicious patterns.
        |
        */
        'block_malicious_requests' => true,

        /*
        |--------------------------------------------------------------------------
        | Allowed HTML Tags
        |--------------------------------------------------------------------------
        |
        | HTML tags that are allowed in rich text content.
        |
        */
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'b', 'i', 'ul', 'ol', 'li',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'span', 'div',
            'a', 'img'
        ],

        /*
        |--------------------------------------------------------------------------
        | Allowed HTML Attributes
        |--------------------------------------------------------------------------
        |
        | HTML attributes that are allowed in rich text content.
        |
        */
        'allowed_html_attributes' => [
            'class', 'id', 'href', 'src', 'alt', 'title', 'target'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SQL Injection Protection
    |--------------------------------------------------------------------------
    */
    'sql_injection' => [
        /*
        |--------------------------------------------------------------------------
        | Block Requests
        |--------------------------------------------------------------------------
        |
        | Whether to automatically block requests with SQL injection patterns.
        |
        */
        'block_requests' => true,

        /*
        |--------------------------------------------------------------------------
        | Auto Block Threshold
        |--------------------------------------------------------------------------
        |
        | Number of attempts before auto-blocking an IP address.
        |
        */
        'auto_block_threshold' => 2,

        /*
        |--------------------------------------------------------------------------
        | Block Duration
        |--------------------------------------------------------------------------
        |
        | Duration (in seconds) to block an IP address.
        |
        */
        'block_duration' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        /*
        |--------------------------------------------------------------------------
        | Allowed MIME Types
        |--------------------------------------------------------------------------
        |
        | MIME types that are allowed for file uploads.
        |
        */
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'text/plain',
        ],

        /*
        |--------------------------------------------------------------------------
        | Blocked Extensions
        |--------------------------------------------------------------------------
        |
        | File extensions that are explicitly blocked.
        |
        */
        'blocked_extensions' => [
            'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps',
            'jsp', 'asp', 'aspx', 'exe', 'bat', 'cmd', 'com', 'scr',
            'vbs', 'js', 'jar', 'pl', 'py', 'sh', 'cgi'
        ],

        /*
        |--------------------------------------------------------------------------
        | Maximum File Size
        |--------------------------------------------------------------------------
        |
        | Maximum file size in bytes (default: 10MB).
        |
        */
        'max_file_size' => 10485760, // 10MB

        /*
        |--------------------------------------------------------------------------
        | Scan Files for Viruses
        |--------------------------------------------------------------------------
        |
        | Whether to scan uploaded files for viruses (requires ClamAV).
        |
        */
        'virus_scan' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        /*
        |--------------------------------------------------------------------------
        | Session Timeout
        |--------------------------------------------------------------------------
        |
        | Session timeout in minutes.
        |
        */
        'timeout' => 120, // 2 hours

        /*
        |--------------------------------------------------------------------------
        | Regenerate Session ID
        |--------------------------------------------------------------------------
        |
        | Whether to regenerate session ID on login.
        |
        */
        'regenerate_on_login' => true,

        /*
        |--------------------------------------------------------------------------
        | IP Validation
        |--------------------------------------------------------------------------
        |
        | Whether to validate that session IP matches current IP.
        |
        */
        'validate_ip' => true,

        /*
        |--------------------------------------------------------------------------
        | User Agent Validation
        |--------------------------------------------------------------------------
        |
        | Whether to validate that session user agent matches current user agent.
        |
        */
        'validate_user_agent' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    */
    'api' => [
        /*
        |--------------------------------------------------------------------------
        | API Key Length
        |--------------------------------------------------------------------------
        |
        | Length of generated API keys.
        |
        */
        'key_length' => 32,

        /*
        |--------------------------------------------------------------------------
        | Rate Limiting
        |--------------------------------------------------------------------------
        |
        | API-specific rate limiting configuration.
        |
        */
        'rate_limiting' => [
            'requests_per_minute' => 100,
            'requests_per_hour' => 5000,
            'requests_per_day' => 100000,
        ],

        /*
        |--------------------------------------------------------------------------
        | CORS Settings
        |--------------------------------------------------------------------------
        |
        | Cross-Origin Resource Sharing configuration.
        |
        */
        'cors' => [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'max_age' => 86400, // 24 hours
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    'csp' => [
        /*
        |--------------------------------------------------------------------------
        | Default Source
        |--------------------------------------------------------------------------
        |
        | Default source for CSP directives.
        |
        */
        'default_src' => "'self'",

        /*
        |--------------------------------------------------------------------------
        | Script Source
        |--------------------------------------------------------------------------
        |
        | Allowed sources for JavaScript.
        |
        */
        'script_src' => [
            "'self'",
            "'unsafe-inline'", // Required for Livewire/Alpine.js
            "'unsafe-eval'",   // Required for some JavaScript frameworks
            'cdn.jsdelivr.net',
            'unpkg.com',
        ],

        /*
        |--------------------------------------------------------------------------
        | Style Source
        |--------------------------------------------------------------------------
        |
        | Allowed sources for CSS.
        |
        */
        'style_src' => [
            "'self'",
            "'unsafe-inline'", // Required for inline styles
            'fonts.googleapis.com',
            'cdn.jsdelivr.net',
        ],

        /*
        |--------------------------------------------------------------------------
        | Image Source
        |--------------------------------------------------------------------------
        |
        | Allowed sources for images.
        |
        */
        'img_src' => [
            "'self'",
            'data:',
            'blob:',
            'https:',
        ],

        /*
        |--------------------------------------------------------------------------
        | Font Source
        |--------------------------------------------------------------------------
        |
        | Allowed sources for fonts.
        |
        */
        'font_src' => [
            "'self'",
            'fonts.gstatic.com',
            'data:',
        ],

        /*
        |--------------------------------------------------------------------------
        | Connect Source
        |--------------------------------------------------------------------------
        |
        | Allowed sources for AJAX, WebSocket, and EventSource connections.
        |
        */
        'connect_src' => [
            "'self'",
        ],

        /*
        |--------------------------------------------------------------------------
        | Frame Ancestors
        |--------------------------------------------------------------------------
        |
        | Allowed sources that can embed this page in a frame.
        |
        */
        'frame_ancestors' => [
            "'none'",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        /*
        |--------------------------------------------------------------------------
        | HSTS (HTTP Strict Transport Security)
        |--------------------------------------------------------------------------
        |
        | Force HTTPS connections.
        |
        */
        'hsts' => [
            'enabled' => false, // Enable in production with HTTPS
            'max_age' => 31536000, // 1 year
            'include_subdomains' => true,
            'preload' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Expect-CT
        |--------------------------------------------------------------------------
        |
        | Certificate Transparency monitoring.
        |
        */
        'expect_ct' => [
            'enabled' => false,
            'max_age' => 86400, // 24 hours
            'enforce' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerting
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Alert Thresholds
        |--------------------------------------------------------------------------
        |
        | Thresholds for triggering security alerts.
        |
        */
        'alert_thresholds' => [
            'failed_logins_per_hour' => 10,
            'sql_injection_attempts_per_hour' => 1,
            'malicious_requests_per_hour' => 5,
            'rate_limit_violations_per_hour' => 50,
        ],

        /*
        |--------------------------------------------------------------------------
        | Alert Channels
        |--------------------------------------------------------------------------
        |
        | Channels to send security alerts to.
        |
        */
        'alert_channels' => [
            'log',
            // 'mail',
            // 'slack',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        /*
        |--------------------------------------------------------------------------
        | Additional Encryption Key
        |--------------------------------------------------------------------------
        |
        | Additional encryption key for sensitive data.
        |
        */
        'additional_key' => env('ADDITIONAL_ENCRYPTION_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Encrypted Fields
        |--------------------------------------------------------------------------
        |
        | Database fields that should be encrypted.
        |
        */
        'encrypted_fields' => [
            'users.email',
            'users.phone',
            // Add other sensitive fields
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Security Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for security event monitoring and alerting system
    |
    */
    'monitoring' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),

        // Alert thresholds (events per hour)
        'alert_thresholds' => [
            'failed_logins_per_hour' => env('SECURITY_ALERT_FAILED_LOGINS', 50),
            'sql_injection_attempts_per_hour' => env('SECURITY_ALERT_SQL_INJECTION', 5),
            'malicious_requests_per_hour' => env('SECURITY_ALERT_MALICIOUS_REQUESTS', 20),
            'rate_limit_violations_per_hour' => env('SECURITY_ALERT_RATE_LIMIT', 100),
            'blocked_ips_per_hour' => env('SECURITY_ALERT_BLOCKED_IPS', 10),
        ],

        // Alert channels
        'alert_channels' => [
            'log', // Always log security alerts
            // 'mail', // Uncomment to enable email alerts
            // 'slack', // Uncomment to enable Slack alerts
        ],

        // Security event retention (days)
        'retention' => [
            'security_events' => env('SECURITY_RETENTION_EVENTS', 30),
            'audit_logs' => env('SECURITY_RETENTION_AUDIT', 90),
            'blocked_ips' => env('SECURITY_RETENTION_BLOCKED_IPS', 7),
        ],

        // Automatic blocking settings
        'auto_blocking' => [
            'enabled' => env('SECURITY_AUTO_BLOCKING_ENABLED', true),
            'sql_injection_attempts' => 3, // Block after X attempts
            'malicious_requests' => 5,
            'block_duration' => 3600, // 1 hour in seconds
        ],
    ],

];
