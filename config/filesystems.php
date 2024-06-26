<?php

declare(strict_types=1);

return [

    'disks' => [
        'items' => [
            'driver' => 'local',
            'root' => storage_path('app/public/products'),
            'url' => env('APP_URL').'/storage/products',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],

];
