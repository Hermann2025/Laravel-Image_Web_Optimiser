<?php

return [
    'driver' => env('IMAGE_DRIVER', 'gd'),

    'quality' => [
        'jpeg' => 80,
        'webp' => 75,
        'avif' => 60,
    ],

    'conversion' => [
        'enabled' => true,
        'formats' => ['webp', 'avif'],
    ],

    'resize' => [
        'max_width' => 1920,
        'max_height' => null,
    ],

    'thumbnails' => [
        'enabled' => true,
        'sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
        ],
    ],

    'cleanup' => [
        'expires_after_hours' => 1,
    ],

    'limits' => [
        'max_files' => 20,
        'max_file_size' => 50,
        'allowed_types' => ['jpeg', 'png', 'gif', 'webp', 'bmp'],
    ],
];