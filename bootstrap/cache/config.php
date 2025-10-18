<?php return array (
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/resources/views',
    ),
    'compiled' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/framework/views',
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => '12',
      'verify' => true,
      'limit' => NULL,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'broadcasting' => 
  array (
    'default' => 'log',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'cluster' => NULL,
          'host' => 'api-mt1.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'activitylog' => 
  array (
    'enabled' => true,
    'delete_records_older_than_days' => 365,
    'default_log_name' => 'default',
    'default_auth_driver' => NULL,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => 'Spatie\\Activitylog\\Models\\Activity',
    'table_name' => 'activity_log',
    'database_connection' => NULL,
  ),
  'api-service' => 
  array (
    'navigation' => 
    array (
      'token' => 
      array (
        'cluster' => NULL,
        'group' => 'User',
        'sort' => -1,
        'icon' => 'heroicon-o-key',
      ),
    ),
    'models' => 
    array (
      'token' => 
      array (
        'enable_policy' => true,
      ),
    ),
    'route' => 
    array (
      'panel_prefix' => false,
      'use_resource_middlewares' => false,
    ),
    'tenancy' => 
    array (
      'enabled' => false,
      'awareness' => false,
    ),
  ),
  'app' => 
  array (
    'name' => 'SI-IMUT',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://172.0.0.1:8000',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'Asia/jakarta',
    'locale' => 'id',
    'fallback_locale' => 'id',
    'faker_locale' => 'id_ID',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:HEh3OnCEBkJY1dzwFQXa3St2h0JyM+w5ImcreuUybgo=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
      'store' => 'database',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
      24 => 'App\\Providers\\AuthServiceProvider',
      25 => 'App\\Providers\\Filament\\AdminPanelProvider',
      26 => 'App\\Providers\\LaporanImutServiceProvider',
      27 => 'App\\Providers\\UnitKerjaProvider',
      28 => 'SocialiteProviders\\Manager\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Benchmark' => 'Illuminate\\Support\\Benchmark',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'Uri' => 'Illuminate\\Support\\Uri',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
    'version' => '1.0.0',
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'backup' => 
  array (
    'backup' => 
    array (
      'name' => 'SI-IMUT',
      'source' => 
      array (
        'files' => 
        array (
          'include' => 
          array (
            0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/app',
            1 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/public',
            2 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/.env',
            3 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/config',
            4 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/resources',
          ),
          'exclude' => 
          array (
            0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/vendor',
            1 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/node_modules',
            2 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/logs',
            3 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/framework',
          ),
          'follow_links' => false,
          'ignore_unreadable_directories' => false,
          'relative_path' => NULL,
        ),
        'databases' => 
        array (
          0 => 'mysql',
        ),
      ),
      'database_dump_compressor' => NULL,
      'database_dump_file_timestamp_format' => NULL,
      'database_dump_filename_base' => 'database',
      'database_dump_file_extension' => '',
      'destination' => 
      array (
        'compression_method' => -1,
        'compression_level' => 9,
        'filename_prefix' => '',
        'disks' => 
        array (
          0 => 'local',
        ),
      ),
      'temporary_directory' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/app/backup-temp',
      'password' => 'password-backup',
      'encryption' => 'default',
      'tries' => 1,
      'retry_delay' => 0,
    ),
    'notifications' => 
    array (
      'notifications' => 
      array (
        'Spatie\\Backup\\Notifications\\Notifications\\BackupHasFailedNotification' => 
        array (
          0 => 'database',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\UnhealthyBackupWasFoundNotification' => 
        array (
          0 => 'database',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\CleanupHasFailedNotification' => 
        array (
          0 => 'database',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\BackupWasSuccessfulNotification' => 
        array (
          0 => 'database',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\HealthyBackupWasFoundNotification' => 
        array (
          0 => 'database',
        ),
        'Spatie\\Backup\\Notifications\\Notifications\\CleanupWasSuccessfulNotification' => 
        array (
          0 => 'database',
        ),
      ),
      'notifiable' => 'Spatie\\Backup\\Notifications\\Notifiable',
      'mail' => 
      array (
        'to' => 'your@example.com',
        'from' => 
        array (
          'address' => 'admin@domain.com',
          'name' => 'SI-IMUT',
        ),
      ),
      'slack' => 
      array (
        'webhook_url' => '',
        'channel' => NULL,
        'username' => NULL,
        'icon' => NULL,
      ),
      'discord' => 
      array (
        'webhook_url' => '',
        'username' => '',
        'avatar_url' => '',
      ),
    ),
    'monitor_backups' => 
    array (
      0 => 
      array (
        'name' => 'SI-IMUT',
        'disks' => 
        array (
          0 => 'local',
        ),
        'health_checks' => 
        array (
          'Spatie\\Backup\\Tasks\\Monitor\\HealthChecks\\MaximumAgeInDays' => 1,
          'Spatie\\Backup\\Tasks\\Monitor\\HealthChecks\\MaximumStorageInMegabytes' => 5000,
        ),
      ),
    ),
    'cleanup' => 
    array (
      'strategy' => 'Spatie\\Backup\\Tasks\\Cleanup\\Strategies\\DefaultStrategy',
      'default_strategy' => 
      array (
        'keep_all_backups_for_days' => 7,
        'keep_daily_backups_for_days' => 16,
        'keep_weekly_backups_for_weeks' => 8,
        'keep_monthly_backups_for_months' => 4,
        'keep_yearly_backups_for_years' => 2,
        'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
      ),
      'tries' => 1,
      'retry_delay' => 0,
    ),
  ),
  'cache' => 
  array (
    'default' => 'database',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'session' => 
      array (
        'driver' => 'session',
        'key' => '_cache',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/framework/cache/data',
        'lock_path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => '',
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'siimut',
        'prefix' => '',
        'foreign_key_constraints' => true,
        'busy_timeout' => NULL,
        'journal_mode' => NULL,
        'synchronous' => NULL,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'siimut',
        'username' => 'juni',
        'password' => 'password',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => false,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'siimut',
        'username' => 'juni',
        'password' => 'password',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => false,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'siimut',
        'username' => 'juni',
        'password' => 'password',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'siimut',
        'username' => 'juni',
        'password' => 'password',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 
    array (
      'table' => 'migrations',
      'update_date_on_publish' => true,
    ),
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'si_imut_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'filament' => 
  array (
    'broadcasting' => 
    array (
    ),
    'default_filesystem_disk' => 'public',
    'assets_path' => NULL,
    'cache_path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/bootstrap/cache/filament',
    'livewire_loading_delay' => 'default',
    'system_route_prefix' => 'filament',
  ),
  'filament-activitylog' => 
  array (
    'resources' => 
    array (
      'label' => 'Activity Log',
      'plural_label' => 'Activity Logs',
      'navigation_item' => true,
      'navigation_group' => NULL,
      'navigation_icon' => 'heroicon-o-shield-check',
      'navigation_sort' => NULL,
      'default_sort_column' => 'id',
      'default_sort_direction' => 'desc',
      'navigation_count_badge' => false,
      'resource' => 'App\\Filament\\Resources\\ActivitylogResource',
    ),
    'date_format' => 'd/m/Y',
    'datetime_format' => 'd/m/Y H:i:s',
  ),
  'filament-dash-stack-theme-juniyasyos' => 
  array (
    'default-colors' => 
    array (
      'primary' => 
      array (
        50 => '232, 240, 255',
        100 => '204, 221, 255',
        200 => '153, 187, 255',
        300 => '102, 153, 255',
        400 => '77, 128, 255',
        500 => '72, 128, 255',
        600 => '64, 115, 230',
        700 => '51, 90, 179',
        800 => '38, 64, 128',
        900 => '26, 43, 85',
        950 => '13, 21, 43',
      ),
    ),
    'side-bar-collapsable-on-desktop' => true,
    'collapsible-navigation-groups' => false,
    'breadcrumbs' => true,
    'use-default-font' => true,
    'theme' => 'custom',
  ),
  'filament-media-manager' => 
  array (
    'model' => 
    array (
      'folder' => 'Juniyasyos\\FilamentMediaManager\\Models\\Folder',
      'media' => 'Juniyasyos\\FilamentMediaManager\\Models\\Media',
    ),
    'api' => 
    array (
      'active' => false,
      'middlewares' => 
      array (
        0 => 'api',
        1 => 'auth:sanctum',
      ),
      'prefix' => 'api/media-manager',
      'resources' => 
      array (
        'folders' => 'Juniyasyos\\FilamentMediaManager\\Http\\Resources\\FoldersResource',
        'folder' => 'Juniyasyos\\FilamentMediaManager\\Http\\Resources\\FolderResource',
        'media' => 'Juniyasyos\\FilamentMediaManager\\Http\\Resources\\MediaResource',
      ),
    ),
    'filament' => 
    array (
      'active' => true,
      'resources' => 
      array (
        0 => 'App\\Filament\\Resources\\FolderCustomResource',
        1 => 'App\\Filament\\Resources\\MediaCustomResource',
      ),
    ),
    'user' => 
    array (
      'column_name' => 'name',
    ),
    'allow_user_access' => true,
    'slug_folder' => 'folders',
    'navigation_sort' => 0,
    'slug_media' => 'media',
  ),
  'filament-settings-hub' => 
  array (
    'show_hint' => true,
    'upload' => 
    array (
      'disk' => 'local',
      'directory' => 'public',
    ),
    'page_show' => 
    array (
      'auth' => false,
      'location_setting' => true,
      'site_setting' => true,
      'social_menu_settiing' => true,
    ),
  ),
  'filament-shield' => 
  array (
    'shield_resource' => 
    array (
      'should_register_navigation' => true,
      'slug' => 'shield/roles',
      'navigation_sort' => -1,
      'navigation_badge' => true,
      'navigation_group' => true,
      'is_globally_searchable' => false,
      'show_model_path' => true,
      'is_scoped_to_tenant' => true,
      'cluster' => NULL,
    ),
    'tenant_model' => NULL,
    'auth_provider_model' => 
    array (
      'fqcn' => 'App\\Models\\User',
    ),
    'super_admin' => 
    array (
      'enabled' => true,
      'name' => 'super_admin',
      'define_via_gate' => false,
      'intercept_gate' => 'before',
    ),
    'panel_user' => 
    array (
      'enabled' => true,
      'name' => 'panel_user',
    ),
    'permission_prefixes' => 
    array (
      'resource' => 
      array (
        0 => 'view',
        1 => 'view_any',
        2 => 'create',
        3 => 'update',
        4 => 'restore',
        5 => 'restore_any',
        6 => 'replicate',
        7 => 'reorder',
        8 => 'delete',
        9 => 'delete_any',
        10 => 'force_delete',
        11 => 'force_delete_any',
      ),
      'page' => 'page',
      'widget' => 'widget',
    ),
    'entities' => 
    array (
      'pages' => true,
      'widgets' => true,
      'resources' => true,
      'custom_permissions' => false,
    ),
    'generator' => 
    array (
      'option' => 'policies_and_permissions',
      'policy_directory' => 'Policies',
      'policy_namespace' => 'Policies',
    ),
    'exclude' => 
    array (
      'enabled' => true,
      'pages' => 
      array (
        0 => 'Dashboard',
      ),
      'widgets' => 
      array (
        0 => 'AccountWidget',
        1 => 'FilamentInfoWidget',
      ),
      'resources' => 
      array (
        0 => 'ActivityLogResource',
      ),
    ),
    'discovery' => 
    array (
      'discover_all_resources' => false,
      'discover_all_widgets' => false,
      'discover_all_pages' => false,
    ),
    'register_role_policy' => 
    array (
      'enabled' => true,
    ),
  ),
  'filament-socialite' => 
  array (
    'middleware' => 
    array (
      0 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
      1 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
      2 => 'Illuminate\\Session\\Middleware\\StartSession',
      3 => 'Illuminate\\Session\\Middleware\\AuthenticateSession',
      4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/app/private',
        'serve' => true,
        'throw' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/app/public',
        'url' => 'http://172.0.0.1:8000/storage',
        'visibility' => 'public',
        'throw' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
      ),
      'filament-excel' => 
      array (
        'driver' => 'local',
        'root' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/app/filament-excel',
        'url' => 'http://172.0.0.1:8000/filament-excel',
      ),
    ),
    'links' => 
    array (
      '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/public/storage' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/app/public',
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'resend',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '2525',
        'encryption' => NULL,
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => '172.0.0.1',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
      ),
    ),
    'from' => 
    array (
      'address' => 'admin@domain.com',
      'name' => 'SI-IMUT',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/resources/views/vendor/mail',
      ),
    ),
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'roles',
      'permissions' => 'permissions',
      'model_has_permissions' => 'model_has_permissions',
      'model_has_roles' => 'model_has_roles',
      'role_has_permissions' => 'role_has_permissions',
    ),
    'column_names' => 
    array (
      'role_pivot_key' => NULL,
      'permission_pivot_key' => NULL,
      'model_morph_key' => 'model_id',
      'team_foreign_key' => 'team_id',
    ),
    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'events_enabled' => false,
    'teams' => false,
    'team_resolver' => 'Spatie\\Permission\\DefaultTeamResolver',
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'cache' => 
    array (
      'expiration_time' => 
      \DateInterval::__set_state(array(
         'from_string' => true,
         'date_string' => '24 hours',
      )),
      'key' => 'spatie.permission.cache',
      'store' => 'default',
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'mysql',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => 'localhost:3000',
      2 => '127.0.0.1',
      3 => '127.0.0.1:8000',
      4 => '::1',
      5 => '172.0.0.1:8000',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
      'validate_csrf_token' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidateCsrfToken',
    ),
  ),
  'scramble' => 
  array (
    'api_path' => 'api',
    'api_domain' => NULL,
    'export_path' => 'api.json',
    'info' => 
    array (
      'version' => '0.0.1',
      'description' => '',
    ),
    'ui' => 
    array (
      'title' => NULL,
      'theme' => 'light',
      'hide_try_it' => false,
      'logo' => '',
      'try_it_credentials_policy' => 'include',
    ),
    'servers' => NULL,
    'enum_cases_description_strategy' => 'description',
    'middleware' => 
    array (
      0 => 'web',
    ),
    'extensions' => 
    array (
    ),
  ),
  'secure-headers' => 
  array (
    'server' => '',
    'x-content-type-options' => 'nosniff',
    'x-dns-prefetch-control' => '',
    'x-download-options' => 'noopen',
    'x-frame-options' => 'sameorigin',
    'x-permitted-cross-domain-policies' => 'none',
    'x-powered-by' => '',
    'x-xss-protection' => '',
    'referrer-policy' => 'no-referrer',
    'cross-origin-embedder-policy' => 'unsafe-none',
    'cross-origin-opener-policy' => 'unsafe-none',
    'cross-origin-resource-policy' => 'cross-origin',
    'clear-site-data' => 
    array (
      'enable' => false,
      'all' => false,
      'cache' => true,
      'clientHints' => true,
      'cookies' => true,
      'storage' => true,
      'executionContexts' => true,
    ),
    'hsts' => 
    array (
      'enable' => false,
      'max-age' => 31536000,
      'include-sub-domains' => false,
      'preload' => false,
    ),
    'reporting' => 
    array (
    ),
    'nel' => 
    array (
      'enable' => false,
      'report-to' => '',
      'max-age' => 86400,
      'include-subdomains' => false,
      'success-fraction' => 0.0,
      'failure-fraction' => 1.0,
    ),
    'expect-ct' => 
    array (
      'enable' => false,
      'max-age' => 2147483648,
      'enforce' => false,
      'report-uri' => NULL,
    ),
    'permissions-policy' => 
    array (
      'enable' => true,
      'accelerometer' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'attribution-reporting' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'origins' => 
        array (
        ),
      ),
      'autoplay' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'bluetooth' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'browsing-topics' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'origins' => 
        array (
        ),
      ),
      'camera' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'compute-pressure' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'cross-origin-isolated' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'display-capture' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'encrypted-media' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'fullscreen' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'gamepad' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'geolocation' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'gyroscope' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'hid' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'identity-credentials-get' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'idle-detection' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'local-fonts' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'magnetometer' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'microphone' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'midi' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'otp-credentials' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'payment' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'picture-in-picture' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'origins' => 
        array (
        ),
      ),
      'publickey-credentials-create' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'publickey-credentials-get' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'screen-wake-lock' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'serial' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'storage-access' => 
      array (
        'none' => false,
        '*' => true,
        'self' => false,
        'origins' => 
        array (
        ),
      ),
      'usb' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'web-share' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'window-management' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
      'xr-spatial-tracking' => 
      array (
        'none' => false,
        '*' => false,
        'self' => true,
        'origins' => 
        array (
        ),
      ),
    ),
    'csp' => 
    array (
      'enable' => true,
      'report-only' => false,
      'report-to' => '',
      'report-uri' => 
      array (
      ),
      'block-all-mixed-content' => false,
      'upgrade-insecure-requests' => false,
      'base-uri' => 
      array (
      ),
      'child-src' => 
      array (
      ),
      'connect-src' => 
      array (
      ),
      'default-src' => 
      array (
      ),
      'fenced-frame-src' => 
      array (
      ),
      'font-src' => 
      array (
      ),
      'form-action' => 
      array (
      ),
      'frame-ancestors' => 
      array (
      ),
      'frame-src' => 
      array (
      ),
      'img-src' => 
      array (
      ),
      'manifest-src' => 
      array (
      ),
      'media-src' => 
      array (
      ),
      'object-src' => 
      array (
      ),
      'prefetch-src' => 
      array (
      ),
      'require-trusted-types-for' => 
      array (
        'script' => false,
      ),
      'sandbox' => 
      array (
        'enable' => false,
        'allow-downloads' => false,
        'allow-forms' => false,
        'allow-modals' => false,
        'allow-orientation-lock' => false,
        'allow-pointer-lock' => false,
        'allow-popups' => false,
        'allow-popups-to-escape-sandbox' => false,
        'allow-presentation' => false,
        'allow-same-origin' => false,
        'allow-scripts' => false,
        'allow-storage-access-by-user-activation' => false,
        'allow-top-navigation' => false,
        'allow-top-navigation-by-user-activation' => false,
        'allow-top-navigation-to-custom-protocols' => false,
      ),
      'script-src' => 
      array (
        'none' => false,
        'self' => false,
        'report-sample' => false,
        'allow' => 
        array (
        ),
        'schemes' => 
        array (
        ),
        'unsafe-inline' => false,
        'unsafe-eval' => false,
        'unsafe-hashes' => false,
        'strict-dynamic' => false,
        'hashes' => 
        array (
          'sha256' => 
          array (
          ),
          'sha384' => 
          array (
          ),
          'sha512' => 
          array (
          ),
        ),
      ),
      'script-src-attr' => 
      array (
      ),
      'script-src-elem' => 
      array (
      ),
      'style-src' => 
      array (
      ),
      'style-src-attr' => 
      array (
      ),
      'style-src-elem' => 
      array (
      ),
      'trusted-types' => 
      array (
        'enable' => false,
        'none' => false,
        'allow-duplicates' => false,
        'policies' => 
        array (
        ),
      ),
      'worker-src' => 
      array (
      ),
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'google' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => 'http://localhost:8000/admin/oauth/callback/google',
    ),
  ),
  'session' => 
  array (
    'driver' => 'database',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'si_imut_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'settings' => 
  array (
    'settings' => 
    array (
      0 => 'App\\Settings\\KaidoSetting',
    ),
    'setting_class_path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/app/Settings',
    'migrations_paths' => 
    array (
      0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/database/settings',
    ),
    'default_repository' => 'database',
    'repositories' => 
    array (
      'database' => 
      array (
        'type' => 'Spatie\\LaravelSettings\\SettingsRepositories\\DatabaseSettingsRepository',
        'model' => NULL,
        'table' => NULL,
        'connection' => NULL,
      ),
      'redis' => 
      array (
        'type' => 'Spatie\\LaravelSettings\\SettingsRepositories\\RedisSettingsRepository',
        'connection' => NULL,
        'prefix' => NULL,
      ),
    ),
    'encoder' => NULL,
    'decoder' => NULL,
    'cache' => 
    array (
      'enabled' => false,
      'store' => NULL,
      'prefix' => NULL,
      'ttl' => NULL,
    ),
    'global_casts' => 
    array (
      'DateTimeInterface' => 'Spatie\\LaravelSettings\\SettingsCasts\\DateTimeInterfaceCast',
      'DateTimeZone' => 'Spatie\\LaravelSettings\\SettingsCasts\\DateTimeZoneCast',
      'Spatie\\LaravelData\\Data' => 'Spatie\\LaravelSettings\\SettingsCasts\\DataCast',
    ),
    'auto_discover_settings' => 
    array (
      0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/app/Settings',
    ),
    'discovered_settings_cache_path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/bootstrap/cache',
  ),
  'themes' => 
  array (
    'mode' => 'global',
    'icon' => 'heroicon-o-swatch',
    'default' => 
    array (
      'theme' => 'default',
      'theme_color' => 'blue',
    ),
  ),
  'debugbar' => 
  array (
    'enabled' => true,
    'hide_empty_tabs' => true,
    'except' => 
    array (
      0 => 'telescope*',
      1 => 'horizon*',
    ),
    'storage' => 
    array (
      'enabled' => true,
      'open' => NULL,
      'driver' => 'file',
      'path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/debugbar',
      'connection' => NULL,
      'provider' => '',
      'hostname' => '127.0.0.1',
      'port' => 2304,
    ),
    'editor' => 'phpstorm',
    'remote_sites_path' => NULL,
    'local_sites_path' => NULL,
    'include_vendors' => true,
    'capture_ajax' => true,
    'add_ajax_timing' => false,
    'ajax_handler_auto_show' => true,
    'ajax_handler_enable_tab' => true,
    'defer_datasets' => false,
    'error_handler' => false,
    'clockwork' => false,
    'collectors' => 
    array (
      'phpinfo' => false,
      'messages' => true,
      'time' => true,
      'memory' => true,
      'exceptions' => true,
      'log' => true,
      'db' => true,
      'views' => true,
      'route' => false,
      'auth' => false,
      'gate' => true,
      'session' => false,
      'symfony_request' => true,
      'mail' => true,
      'laravel' => true,
      'events' => false,
      'default_request' => false,
      'logs' => false,
      'files' => false,
      'config' => false,
      'cache' => false,
      'models' => true,
      'livewire' => true,
      'jobs' => false,
      'pennant' => false,
    ),
    'options' => 
    array (
      'time' => 
      array (
        'memory_usage' => false,
      ),
      'messages' => 
      array (
        'trace' => true,
        'capture_dumps' => false,
      ),
      'memory' => 
      array (
        'reset_peak' => false,
        'with_baseline' => false,
        'precision' => 0,
      ),
      'auth' => 
      array (
        'show_name' => true,
        'show_guards' => true,
      ),
      'gate' => 
      array (
        'trace' => false,
      ),
      'db' => 
      array (
        'with_params' => true,
        'exclude_paths' => 
        array (
        ),
        'backtrace' => true,
        'backtrace_exclude_paths' => 
        array (
        ),
        'timeline' => false,
        'duration_background' => true,
        'explain' => 
        array (
          'enabled' => false,
        ),
        'hints' => false,
        'show_copy' => true,
        'slow_threshold' => false,
        'memory_usage' => false,
        'soft_limit' => 100,
        'hard_limit' => 500,
      ),
      'mail' => 
      array (
        'timeline' => true,
        'show_body' => true,
      ),
      'views' => 
      array (
        'timeline' => true,
        'data' => false,
        'group' => 50,
        'inertia_pages' => 'js/Pages',
        'exclude_paths' => 
        array (
          0 => 'vendor/filament',
        ),
      ),
      'route' => 
      array (
        'label' => true,
      ),
      'session' => 
      array (
        'hiddens' => 
        array (
        ),
      ),
      'symfony_request' => 
      array (
        'label' => true,
        'hiddens' => 
        array (
        ),
      ),
      'events' => 
      array (
        'data' => false,
        'excluded' => 
        array (
        ),
      ),
      'logs' => 
      array (
        'file' => NULL,
      ),
      'cache' => 
      array (
        'values' => true,
      ),
    ),
    'inject' => true,
    'route_prefix' => '_debugbar',
    'route_middleware' => 
    array (
    ),
    'route_domain' => NULL,
    'theme' => 'auto',
    'debug_backtrace_limit' => 50,
  ),
  'blade-heroicons' => 
  array (
    'prefix' => 'heroicon',
    'fallback' => '',
    'class' => '',
    'attributes' => 
    array (
    ),
  ),
  'blade-icons' => 
  array (
    'sets' => 
    array (
    ),
    'class' => '',
    'attributes' => 
    array (
    ),
    'fallback' => '',
    'components' => 
    array (
      'disabled' => false,
      'default' => 'icon',
    ),
  ),
  'filament-table-repeater' => 
  array (
  ),
  'filament-pwa' => 
  array (
    'middlewares' => 
    array (
    ),
    'allow_routes' => true,
  ),
  'laravel-impersonate' => 
  array (
    'session_key' => 'impersonated_by',
    'session_guard' => 'impersonator_guard',
    'session_guard_using' => 'impersonator_guard_using',
    'default_impersonator_guard' => 'web',
    'take_redirect_to' => '/',
    'leave_redirect_to' => '/',
  ),
  'filament-apex-charts' => 
  array (
    'chart_options' => 
    array (
      0 => 'Empty',
      1 => 'Area',
      2 => 'Bar',
      3 => 'Boxplot',
      4 => 'Bubble',
      5 => 'Candlestick',
      6 => 'Column',
      7 => 'Donut',
      8 => 'Heatmap',
      9 => 'Line',
      10 => 'Mixed-LineAndColumn',
      11 => 'Pie',
      12 => 'PolarArea',
      13 => 'Radar',
      14 => 'Radialbar',
      15 => 'RangeArea',
      16 => 'Scatter',
      17 => 'TimelineRangeBars',
      18 => 'Treemap',
      19 => 'Funnel',
    ),
  ),
  'livewire' => 
  array (
    'class_namespace' => 'App\\Livewire',
    'view_path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/resources/views/livewire',
    'layout' => 'components.layouts.app',
    'lazy_placeholder' => NULL,
    'temporary_file_upload' => 
    array (
      'disk' => NULL,
      'rules' => NULL,
      'directory' => NULL,
      'middleware' => NULL,
      'preview_mimes' => 
      array (
        0 => 'png',
        1 => 'gif',
        2 => 'bmp',
        3 => 'svg',
        4 => 'wav',
        5 => 'mp4',
        6 => 'mov',
        7 => 'avi',
        8 => 'wmv',
        9 => 'mp3',
        10 => 'm4a',
        11 => 'jpg',
        12 => 'jpeg',
        13 => 'mpga',
        14 => 'webp',
        15 => 'wma',
      ),
      'max_upload_time' => 5,
      'cleanup' => true,
    ),
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => 
    array (
      'show_progress_bar' => true,
      'progress_bar_color' => '#2299dd',
    ),
    'inject_morph_markers' => true,
    'pagination_theme' => 'tailwind',
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'pre_calculate_formulas' => false,
      'strict_null_comparison' => false,
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
        'output_encoding' => '',
        'test_auto_detect' => true,
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => NULL,
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'guess',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
      'cells' => 
      array (
        'middleware' => 
        array (
        ),
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
      'default_ttl' => 10800,
    ),
    'transactions' => 
    array (
      'handler' => 'db',
      'db' => 
      array (
        'connection' => NULL,
      ),
    ),
    'temporary_files' => 
    array (
      'local_path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/storage/framework/cache/laravel-excel',
      'local_permissions' => 
      array (
      ),
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'modules' => 
  array (
    'namespace' => 'Modules',
    'stubs' => 
    array (
      'enabled' => false,
      'path' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/vendor/nwidart/laravel-modules/src/Commands/stubs',
      'files' => 
      array (
        'routes/web' => 'routes/web.php',
        'routes/api' => 'routes/api.php',
        'views/index' => 'resources/views/index.blade.php',
        'views/master' => 'resources/views/components/layouts/master.blade.php',
        'scaffold/config' => 'config/config.php',
        'composer' => 'composer.json',
        'assets/js/app' => 'resources/assets/js/app.js',
        'assets/sass/app' => 'resources/assets/sass/app.scss',
        'vite' => 'vite.config.js',
        'package' => 'package.json',
      ),
      'replacements' => 
      array (
        'routes/web' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'PLURAL_LOWER_NAME',
          3 => 'KEBAB_NAME',
          4 => 'MODULE_NAMESPACE',
          5 => 'CONTROLLER_NAMESPACE',
        ),
        'routes/api' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'PLURAL_LOWER_NAME',
          3 => 'KEBAB_NAME',
          4 => 'MODULE_NAMESPACE',
          5 => 'CONTROLLER_NAMESPACE',
        ),
        'vite' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'KEBAB_NAME',
        ),
        'json' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'KEBAB_NAME',
          3 => 'MODULE_NAMESPACE',
          4 => 'PROVIDER_NAMESPACE',
        ),
        'views/index' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'views/master' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'KEBAB_NAME',
        ),
        'scaffold/config' => 
        array (
          0 => 'STUDLY_NAME',
        ),
        'composer' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'VENDOR',
          3 => 'AUTHOR_NAME',
          4 => 'AUTHOR_EMAIL',
          5 => 'MODULE_NAMESPACE',
          6 => 'PROVIDER_NAMESPACE',
          7 => 'APP_FOLDER_NAME',
        ),
      ),
      'gitkeep' => true,
    ),
    'paths' => 
    array (
      'modules' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/Modules',
      'assets' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/public/modules',
      'migration' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/database/migrations',
      'app_folder' => 'app/',
      'generator' => 
      array (
        'actions' => 
        array (
          'path' => 'app/Actions',
          'generate' => false,
        ),
        'casts' => 
        array (
          'path' => 'app/Casts',
          'generate' => false,
        ),
        'channels' => 
        array (
          'path' => 'app/Broadcasting',
          'generate' => false,
        ),
        'class' => 
        array (
          'path' => 'app/Classes',
          'generate' => false,
        ),
        'command' => 
        array (
          'path' => 'app/Console',
          'generate' => false,
        ),
        'component-class' => 
        array (
          'path' => 'app/View/Components',
          'generate' => false,
        ),
        'emails' => 
        array (
          'path' => 'app/Emails',
          'generate' => false,
        ),
        'event' => 
        array (
          'path' => 'app/Events',
          'generate' => false,
        ),
        'enums' => 
        array (
          'path' => 'app/Enums',
          'generate' => false,
        ),
        'exceptions' => 
        array (
          'path' => 'app/Exceptions',
          'generate' => false,
        ),
        'jobs' => 
        array (
          'path' => 'app/Jobs',
          'generate' => false,
        ),
        'helpers' => 
        array (
          'path' => 'app/Helpers',
          'generate' => false,
        ),
        'interfaces' => 
        array (
          'path' => 'app/Interfaces',
          'generate' => false,
        ),
        'listener' => 
        array (
          'path' => 'app/Listeners',
          'generate' => false,
        ),
        'model' => 
        array (
          'path' => 'app/Models',
          'generate' => false,
        ),
        'notifications' => 
        array (
          'path' => 'app/Notifications',
          'generate' => false,
        ),
        'observer' => 
        array (
          'path' => 'app/Observers',
          'generate' => false,
        ),
        'policies' => 
        array (
          'path' => 'app/Policies',
          'generate' => false,
        ),
        'provider' => 
        array (
          'path' => 'app/Providers',
          'generate' => true,
        ),
        'repository' => 
        array (
          'path' => 'app/Repositories',
          'generate' => false,
        ),
        'resource' => 
        array (
          'path' => 'app/Transformers',
          'generate' => false,
        ),
        'route-provider' => 
        array (
          'path' => 'app/Providers',
          'generate' => true,
        ),
        'rules' => 
        array (
          'path' => 'app/Rules',
          'generate' => false,
        ),
        'services' => 
        array (
          'path' => 'app/Services',
          'generate' => false,
        ),
        'scopes' => 
        array (
          'path' => 'app/Models/Scopes',
          'generate' => false,
        ),
        'traits' => 
        array (
          'path' => 'app/Traits',
          'generate' => false,
        ),
        'controller' => 
        array (
          'path' => 'app/Http/Controllers',
          'generate' => true,
        ),
        'filter' => 
        array (
          'path' => 'app/Http/Middleware',
          'generate' => false,
        ),
        'request' => 
        array (
          'path' => 'app/Http/Requests',
          'generate' => false,
        ),
        'config' => 
        array (
          'path' => 'config',
          'generate' => true,
        ),
        'factory' => 
        array (
          'path' => 'database/factories',
          'generate' => true,
        ),
        'migration' => 
        array (
          'path' => 'database/migrations',
          'generate' => true,
        ),
        'seeder' => 
        array (
          'path' => 'database/seeders',
          'generate' => true,
        ),
        'lang' => 
        array (
          'path' => 'lang',
          'generate' => false,
        ),
        'assets' => 
        array (
          'path' => 'resources/assets',
          'generate' => true,
        ),
        'component-view' => 
        array (
          'path' => 'resources/views/components',
          'generate' => false,
        ),
        'views' => 
        array (
          'path' => 'resources/views',
          'generate' => true,
        ),
        'routes' => 
        array (
          'path' => 'routes',
          'generate' => true,
        ),
        'test-feature' => 
        array (
          'path' => 'tests/Feature',
          'generate' => true,
        ),
        'test-unit' => 
        array (
          'path' => 'tests/Unit',
          'generate' => true,
        ),
      ),
    ),
    'auto-discover' => 
    array (
      'migrations' => true,
      'translations' => false,
    ),
    'commands' => 
    array (
      0 => 'Nwidart\\Modules\\Commands\\Actions\\CheckLangCommand',
      1 => 'Nwidart\\Modules\\Commands\\Actions\\DisableCommand',
      2 => 'Nwidart\\Modules\\Commands\\Actions\\DumpCommand',
      3 => 'Nwidart\\Modules\\Commands\\Actions\\EnableCommand',
      4 => 'Nwidart\\Modules\\Commands\\Actions\\InstallCommand',
      5 => 'Nwidart\\Modules\\Commands\\Actions\\ListCommand',
      6 => 'Nwidart\\Modules\\Commands\\Actions\\ListCommands',
      7 => 'Nwidart\\Modules\\Commands\\Actions\\ModelPruneCommand',
      8 => 'Nwidart\\Modules\\Commands\\Actions\\ModelShowCommand',
      9 => 'Nwidart\\Modules\\Commands\\Actions\\ModuleDeleteCommand',
      10 => 'Nwidart\\Modules\\Commands\\Actions\\UnUseCommand',
      11 => 'Nwidart\\Modules\\Commands\\Actions\\UpdateCommand',
      12 => 'Nwidart\\Modules\\Commands\\Actions\\UseCommand',
      13 => 'Nwidart\\Modules\\Commands\\Database\\MigrateCommand',
      14 => 'Nwidart\\Modules\\Commands\\Database\\MigrateRefreshCommand',
      15 => 'Nwidart\\Modules\\Commands\\Database\\MigrateResetCommand',
      16 => 'Nwidart\\Modules\\Commands\\Database\\MigrateRollbackCommand',
      17 => 'Nwidart\\Modules\\Commands\\Database\\MigrateStatusCommand',
      18 => 'Nwidart\\Modules\\Commands\\Database\\SeedCommand',
      19 => 'Nwidart\\Modules\\Commands\\Make\\ActionMakeCommand',
      20 => 'Nwidart\\Modules\\Commands\\Make\\CastMakeCommand',
      21 => 'Nwidart\\Modules\\Commands\\Make\\ChannelMakeCommand',
      22 => 'Nwidart\\Modules\\Commands\\Make\\ClassMakeCommand',
      23 => 'Nwidart\\Modules\\Commands\\Make\\CommandMakeCommand',
      24 => 'Nwidart\\Modules\\Commands\\Make\\ComponentClassMakeCommand',
      25 => 'Nwidart\\Modules\\Commands\\Make\\ComponentViewMakeCommand',
      26 => 'Nwidart\\Modules\\Commands\\Make\\ControllerMakeCommand',
      27 => 'Nwidart\\Modules\\Commands\\Make\\EventMakeCommand',
      28 => 'Nwidart\\Modules\\Commands\\Make\\EventProviderMakeCommand',
      29 => 'Nwidart\\Modules\\Commands\\Make\\EnumMakeCommand',
      30 => 'Nwidart\\Modules\\Commands\\Make\\ExceptionMakeCommand',
      31 => 'Nwidart\\Modules\\Commands\\Make\\FactoryMakeCommand',
      32 => 'Nwidart\\Modules\\Commands\\Make\\InterfaceMakeCommand',
      33 => 'Nwidart\\Modules\\Commands\\Make\\HelperMakeCommand',
      34 => 'Nwidart\\Modules\\Commands\\Make\\JobMakeCommand',
      35 => 'Nwidart\\Modules\\Commands\\Make\\ListenerMakeCommand',
      36 => 'Nwidart\\Modules\\Commands\\Make\\MailMakeCommand',
      37 => 'Nwidart\\Modules\\Commands\\Make\\MiddlewareMakeCommand',
      38 => 'Nwidart\\Modules\\Commands\\Make\\MigrationMakeCommand',
      39 => 'Nwidart\\Modules\\Commands\\Make\\ModelMakeCommand',
      40 => 'Nwidart\\Modules\\Commands\\Make\\ModuleMakeCommand',
      41 => 'Nwidart\\Modules\\Commands\\Make\\NotificationMakeCommand',
      42 => 'Nwidart\\Modules\\Commands\\Make\\ObserverMakeCommand',
      43 => 'Nwidart\\Modules\\Commands\\Make\\PolicyMakeCommand',
      44 => 'Nwidart\\Modules\\Commands\\Make\\ProviderMakeCommand',
      45 => 'Nwidart\\Modules\\Commands\\Make\\RepositoryMakeCommand',
      46 => 'Nwidart\\Modules\\Commands\\Make\\RequestMakeCommand',
      47 => 'Nwidart\\Modules\\Commands\\Make\\ResourceMakeCommand',
      48 => 'Nwidart\\Modules\\Commands\\Make\\RouteProviderMakeCommand',
      49 => 'Nwidart\\Modules\\Commands\\Make\\RuleMakeCommand',
      50 => 'Nwidart\\Modules\\Commands\\Make\\ScopeMakeCommand',
      51 => 'Nwidart\\Modules\\Commands\\Make\\SeedMakeCommand',
      52 => 'Nwidart\\Modules\\Commands\\Make\\ServiceMakeCommand',
      53 => 'Nwidart\\Modules\\Commands\\Make\\TraitMakeCommand',
      54 => 'Nwidart\\Modules\\Commands\\Make\\TestMakeCommand',
      55 => 'Nwidart\\Modules\\Commands\\Make\\ViewMakeCommand',
      56 => 'Nwidart\\Modules\\Commands\\Publish\\PublishCommand',
      57 => 'Nwidart\\Modules\\Commands\\Publish\\PublishConfigurationCommand',
      58 => 'Nwidart\\Modules\\Commands\\Publish\\PublishMigrationCommand',
      59 => 'Nwidart\\Modules\\Commands\\Publish\\PublishTranslationCommand',
      60 => 'Nwidart\\Modules\\Commands\\ComposerUpdateCommand',
      61 => 'Nwidart\\Modules\\Commands\\LaravelModulesV6Migrator',
      62 => 'Nwidart\\Modules\\Commands\\SetupCommand',
      63 => 'Nwidart\\Modules\\Commands\\UpdatePhpunitCoverage',
      64 => 'Nwidart\\Modules\\Commands\\Database\\MigrateFreshCommand',
    ),
    'scan' => 
    array (
      'enabled' => false,
      'paths' => 
      array (
        0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/vendor/*/*',
      ),
    ),
    'composer' => 
    array (
      'vendor' => 'nwidart',
      'author' => 
      array (
        'name' => 'Nicolas Widart',
        'email' => 'n.widart@gmail.com',
      ),
      'composer-output' => false,
    ),
    'register' => 
    array (
      'translations' => true,
      'files' => 'register',
    ),
    'activators' => 
    array (
      'file' => 
      array (
        'class' => 'Nwidart\\Modules\\Activators\\FileActivator',
        'statuses-file' => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/modules_statuses.json',
      ),
    ),
    'activator' => 'file',
  ),
  'blade-fontawesome' => 
  array (
    'brands' => 
    array (
      'prefix' => 'fab',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'regular' => 
    array (
      'prefix' => 'far',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'solid' => 
    array (
      'prefix' => 'fas',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'duotone' => 
    array (
      'prefix' => 'fad',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'light' => 
    array (
      'prefix' => 'fal',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'thin' => 
    array (
      'prefix' => 'fat',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'sharp-light' => 
    array (
      'prefix' => 'fal:sharp',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'sharp-regular' => 
    array (
      'prefix' => 'far:sharp',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'sharp-solid' => 
    array (
      'prefix' => 'fas:sharp',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'sharp-duotone-solid' => 
    array (
      'prefix' => 'fad:sharp',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'sharp-thin' => 
    array (
      'prefix' => 'fat:sharp',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
    'custom' => 
    array (
      'prefix' => 'fak',
      'fallback' => '',
      'class' => '',
      'attributes' => 
      array (
      ),
    ),
  ),
  'resend' => 
  array (
    'api_key' => '',
    'domain' => NULL,
    'path' => 'resend',
    'webhook' => 
    array (
      'secret' => NULL,
      'tolerance' => 300,
    ),
  ),
  'media-library' => 
  array (
    'disk_name' => 'public',
    'max_file_size' => 10485760,
    'queue_connection_name' => 'database',
    'queue_name' => '',
    'queue_conversions_by_default' => true,
    'queue_conversions_after_database_commit' => true,
    'media_model' => 'Spatie\\MediaLibrary\\MediaCollections\\Models\\Media',
    'media_observer' => 'Spatie\\MediaLibrary\\MediaCollections\\Models\\Observers\\MediaObserver',
    'use_default_collection_serialization' => false,
    'temporary_upload_model' => 'Spatie\\MediaLibraryPro\\Models\\TemporaryUpload',
    'enable_temporary_uploads_session_affinity' => true,
    'generate_thumbnails_for_temporary_uploads' => true,
    'file_namer' => 'Spatie\\MediaLibrary\\Support\\FileNamer\\DefaultFileNamer',
    'path_generator' => 'Spatie\\MediaLibrary\\Support\\PathGenerator\\DefaultPathGenerator',
    'file_remover_class' => 'Spatie\\MediaLibrary\\Support\\FileRemover\\DefaultFileRemover',
    'custom_path_generators' => 
    array (
    ),
    'url_generator' => 'Spatie\\MediaLibrary\\Support\\UrlGenerator\\DefaultUrlGenerator',
    'moves_media_on_update' => false,
    'version_urls' => false,
    'image_optimizers' => 
    array (
      'Spatie\\ImageOptimizer\\Optimizers\\Jpegoptim' => 
      array (
        0 => '-m85',
        1 => '--force',
        2 => '--strip-all',
        3 => '--all-progressive',
      ),
      'Spatie\\ImageOptimizer\\Optimizers\\Pngquant' => 
      array (
        0 => '--force',
      ),
      'Spatie\\ImageOptimizer\\Optimizers\\Optipng' => 
      array (
        0 => '-i0',
        1 => '-o2',
        2 => '-quiet',
      ),
      'Spatie\\ImageOptimizer\\Optimizers\\Svgo' => 
      array (
        0 => '--disable=cleanupIDs',
      ),
      'Spatie\\ImageOptimizer\\Optimizers\\Gifsicle' => 
      array (
        0 => '-b',
        1 => '-O3',
      ),
      'Spatie\\ImageOptimizer\\Optimizers\\Cwebp' => 
      array (
        0 => '-m 6',
        1 => '-pass 10',
        2 => '-mt',
        3 => '-q 90',
      ),
      'Spatie\\ImageOptimizer\\Optimizers\\Avifenc' => 
      array (
        0 => '-a cq-level=23',
        1 => '-j all',
        2 => '--min 0',
        3 => '--max 63',
        4 => '--minalpha 0',
        5 => '--maxalpha 63',
        6 => '-a end-usage=q',
        7 => '-a tune=ssim',
      ),
    ),
    'image_generators' => 
    array (
      0 => 'Spatie\\MediaLibrary\\Conversions\\ImageGenerators\\Image',
      1 => 'Spatie\\MediaLibrary\\Conversions\\ImageGenerators\\Webp',
      2 => 'Spatie\\MediaLibrary\\Conversions\\ImageGenerators\\Avif',
      3 => 'Spatie\\MediaLibrary\\Conversions\\ImageGenerators\\Pdf',
      4 => 'Spatie\\MediaLibrary\\Conversions\\ImageGenerators\\Svg',
      5 => 'Spatie\\MediaLibrary\\Conversions\\ImageGenerators\\Video',
    ),
    'temporary_directory_path' => NULL,
    'image_driver' => 'gd',
    'ffmpeg_path' => '/usr/bin/ffmpeg',
    'ffprobe_path' => '/usr/bin/ffprobe',
    'jobs' => 
    array (
      'perform_conversions' => 'Spatie\\MediaLibrary\\Conversions\\Jobs\\PerformConversionsJob',
      'generate_responsive_images' => 'Spatie\\MediaLibrary\\ResponsiveImages\\Jobs\\GenerateResponsiveImagesJob',
    ),
    'media_downloader' => 'Spatie\\MediaLibrary\\Downloaders\\DefaultDownloader',
    'media_downloader_ssl' => true,
    'temporary_url_default_lifetime' => 5,
    'remote' => 
    array (
      'extra_headers' => 
      array (
        'CacheControl' => 'max-age=604800',
      ),
    ),
    'responsive_images' => 
    array (
      'width_calculator' => 'Spatie\\MediaLibrary\\ResponsiveImages\\WidthCalculator\\FileSizeOptimizedWidthCalculator',
      'use_tiny_placeholders' => true,
      'tiny_placeholder_generator' => 'Spatie\\MediaLibrary\\ResponsiveImages\\TinyPlaceholderGenerator\\Blurred',
    ),
    'enable_vapor_uploads' => false,
    'default_loading_attribute_value' => NULL,
    'prefix' => '',
    'force_lazy_loading' => true,
  ),
  'sitemap' => 
  array (
    'guzzle_options' => 
    array (
      'cookies' => true,
      'connect_timeout' => 10,
      'timeout' => 10,
      'allow_redirects' => false,
    ),
    'execute_javascript' => false,
    'chrome_binary_path' => NULL,
    'crawl_profile' => 'Spatie\\Sitemap\\Crawler\\Profile',
  ),
  'filament-impersonate' => 
  array (
    'guard' => 'web',
    'redirect_to' => '/',
    'leave_middleware' => 'web',
    'banner' => 
    array (
      'render_hook' => 'panels::body.start',
      'style' => 'dark',
      'fixed' => true,
      'position' => 'top',
      'styles' => 
      array (
        'light' => 
        array (
          'text' => '#1f2937',
          'background' => '#f3f4f6',
          'border' => '#e8eaec',
        ),
        'dark' => 
        array (
          'text' => '#f3f4f6',
          'background' => '#1f2937',
          'border' => '#374151',
        ),
      ),
    ),
  ),
  'console-helpers' => 
  array (
    'yarn-path' => '/opt/homebrew/bin/yarn',
  ),
  'filament-icons' => 
  array (
    'cache' => true,
  ),
  'ide-helper' => 
  array (
    'filename' => '_ide_helper.php',
    'models_filename' => '_ide_helper_models.php',
    'meta_filename' => '.phpstorm.meta.php',
    'include_fluent' => false,
    'include_factory_builders' => false,
    'write_model_magic_where' => true,
    'write_model_external_builder_methods' => true,
    'write_model_relation_count_properties' => true,
    'write_model_relation_exists_properties' => false,
    'write_eloquent_model_mixins' => false,
    'include_helpers' => false,
    'helper_files' => 
    array (
      0 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
      1 => '/home/juni/skripsi-ahmad-ilyas/application/SI-IMUT/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php',
    ),
    'model_locations' => 
    array (
      0 => 'app',
    ),
    'ignored_models' => 
    array (
    ),
    'model_hooks' => 
    array (
    ),
    'extra' => 
    array (
      'Eloquent' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'Illuminate\\Database\\Query\\Builder',
      ),
      'Session' => 
      array (
        0 => 'Illuminate\\Session\\Store',
      ),
    ),
    'magic' => 
    array (
    ),
    'interfaces' => 
    array (
    ),
    'model_camel_case_properties' => false,
    'type_overrides' => 
    array (
      'integer' => 'int',
      'boolean' => 'bool',
    ),
    'include_class_docblocks' => false,
    'force_fqn' => false,
    'use_generics_annotations' => true,
    'macro_default_return_types' => 
    array (
      'Illuminate\\Http\\Client\\Factory' => 'Illuminate\\Http\\Client\\PendingRequest',
    ),
    'additional_relation_types' => 
    array (
    ),
    'additional_relation_return_types' => 
    array (
    ),
    'enforce_nullable_relationships' => true,
    'post_migrate' => 
    array (
    ),
  ),
  'blueprint' => 
  array (
    'namespace' => 'App',
    'models_namespace' => 'Models',
    'controllers_namespace' => 'Http\\Controllers',
    'components_namespace' => 'Livewire',
    'policy_namespace' => 'Policies',
    'app_path' => 'app',
    'generate_phpdocs' => false,
    'use_constraints' => false,
    'on_delete' => 'cascade',
    'on_update' => 'cascade',
    'fake_nullables' => true,
    'use_guarded' => false,
    'singular_routes' => false,
    'property_promotion' => false,
    'generate_resource_collection_classes' => true,
    'generators' => 
    array (
      'controller' => 'Blueprint\\Generators\\ControllerGenerator',
      'factory' => 'Blueprint\\Generators\\FactoryGenerator',
      'migration' => 'Blueprint\\Generators\\MigrationGenerator',
      'model' => 'Blueprint\\Generators\\ModelGenerator',
      'route' => 'Blueprint\\Generators\\RouteGenerator',
      'seeder' => 'Blueprint\\Generators\\SeederGenerator',
      'test' => 'Blueprint\\Generators\\PhpUnitTestGenerator',
      'event' => 'Blueprint\\Generators\\Statements\\EventGenerator',
      'form_request' => 'Blueprint\\Generators\\Statements\\FormRequestGenerator',
      'job' => 'Blueprint\\Generators\\Statements\\JobGenerator',
      'mail' => 'Blueprint\\Generators\\Statements\\MailGenerator',
      'notification' => 'Blueprint\\Generators\\Statements\\NotificationGenerator',
      'resource' => 'Blueprint\\Generators\\Statements\\ResourceGenerator',
      'view' => 'Blueprint\\Generators\\Statements\\ViewGenerator',
      'inertia_page' => 'Blueprint\\Generators\\Statements\\InertiaPageGenerator',
      'policy' => 'Blueprint\\Generators\\PolicyGenerator',
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
