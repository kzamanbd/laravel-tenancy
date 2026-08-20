<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * Published status pages.
         *
         * Deliberately absent from `tenancy.filesystem.disks`: that bootstrapper
         * rewrites a disk's root per tenant, and these paths are addressed
         * explicitly by tenant so the publisher behaves identically inside a
         * tenant context, in a queue worker, and from the console.
         *
         * In production this should point at object storage on a *different*
         * provider and region than the application, so a status page survives
         * the outage it is reporting.
         */
        'status_pages' => [
            'driver' => env('STATUS_PAGE_DISK_DRIVER', 'local'),
            'root' => storage_path('app/status-pages'),
            'url' => env('STATUS_PAGE_URL'),
            'visibility' => 'public',
            'key' => env('STATUS_PAGE_ACCESS_KEY_ID'),
            'secret' => env('STATUS_PAGE_SECRET_ACCESS_KEY'),
            'region' => env('STATUS_PAGE_REGION'),
            'bucket' => env('STATUS_PAGE_BUCKET'),
            'endpoint' => env('STATUS_PAGE_ENDPOINT'),
            'use_path_style_endpoint' => env('STATUS_PAGE_USE_PATH_STYLE', false),
            'throw' => true,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
