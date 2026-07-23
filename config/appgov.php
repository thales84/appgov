<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Display timezone
    |--------------------------------------------------------------------------
    |
    | Dates stay stored in UTC. This timezone is only used when formatting
    | dates intended for citizens and public servants.
    |
    */
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Europe/Paris'),

    /*
    |--------------------------------------------------------------------------
    | Private document disk
    |--------------------------------------------------------------------------
    |
    | Local development uses a private local disk. Staging and production
    | should point this value to a private S3-compatible object store.
    |
    */
    'documents_disk' => env('APP_DOCUMENTS_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Keep this disabled while using Vite's development server. Enable it in
    | staging and production after the final external domains are validated.
    |
    */
    'csp_enabled' => env('APP_CSP_ENABLED', false),
];
