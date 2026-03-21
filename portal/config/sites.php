<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sites Path
    |--------------------------------------------------------------------------
    |
    | The filesystem path where customer sites are stored. Each site gets a
    | subdirectory named after its domain. This is NOT inside the portal repo
    | — sites live on the server's filesystem independently.
    |
    */
    'path' => env('SITES_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Templates Path
    |--------------------------------------------------------------------------
    |
    | Path to site templates used when scaffolding new sites.
    |
    */
    'templates_path' => env('TEMPLATES_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Preview Domain
    |--------------------------------------------------------------------------
    |
    | The base domain for preview URLs. Sites are accessible at
    | {slug}.{preview_domain} before a custom domain is connected.
    |
    */
    'preview_domain' => env('PREVIEW_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Server IP
    |--------------------------------------------------------------------------
    |
    | The server's public IP address, shown in DNS instructions when
    | customers connect their custom domain.
    |
    */
    'server_ip' => env('SERVER_IP'),

    /*
    |--------------------------------------------------------------------------
    | Max Releases
    |--------------------------------------------------------------------------
    |
    | Maximum number of releases to keep per site. Older releases are
    | pruned during cleanup.
    |
    */
    'max_releases' => env('MAX_RELEASES'),
];
