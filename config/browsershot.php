<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Node Binary Path
    |--------------------------------------------------------------------------
    |
    | Path ke binary node. Default biasanya /usr/bin/node
    | Untuk Docker atau environment khusus, sesuaikan path ini
    |
    */
    'node_binary' => env('BROWSERSHOT_NODE_BINARY', '/usr/bin/node'),

    /*
    |--------------------------------------------------------------------------
    | NPM Binary Path
    |--------------------------------------------------------------------------
    |
    | Path ke binary npm. Default biasanya /usr/bin/npm
    |
    */
    'npm_binary' => env('BROWSERSHOT_NPM_BINARY', '/usr/bin/npm'),

    /*
    |--------------------------------------------------------------------------
    | Chrome/Chromium Binary Path (Optional)
    |--------------------------------------------------------------------------
    |
    | Jika ingin menggunakan Chrome/Chromium custom path
    |
    */
    'chrome_path' => env('BROWSERSHOT_CHROME_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout default untuk rendering PDF (dalam detik)
    |
    */
    'timeout' => env('BROWSERSHOT_TIMEOUT', 120),
];
