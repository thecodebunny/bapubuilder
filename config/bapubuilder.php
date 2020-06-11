<?php
return [
    /*
     |--------------------------------------------------------------------------
     | General settings
     |--------------------------------------------------------------------------
     |
     | General settings for configuring the Bapubuilder.
     |
     */
    'general' => [
        'base_url' => env('APP_URL'),
        'language' => 'en',
        'assets_url' => '/assets',
        'uploads_url' => '/uploads'
    ],

    /*
     |--------------------------------------------------------------------------
     | Storage settings
     |--------------------------------------------------------------------------
     |
     | Database and file storage settings.
     |
     */
    'storage' => [
        'use_database' => true,
        'database' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST'),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
        'uploads_folder' => public_path('bapuimages/pages')
    ],

    /*
     |--------------------------------------------------------------------------
     | Website settings
     |--------------------------------------------------------------------------
     |
     | By default a setting class is provided for accessing website settings.
     |
     */
    'setting' => [
        'class' => Thecodebunny\Bapubuilder\Setting::class
    ],

    'bapubuilder' => [
        'class' => Thecodebunny\Bapubuilder\Modules\GrapesJS\PageBuilder::class,
        'url' => '/editor/pages/build',
        'actions' => [
            'back' => '/editor/pages'
        ]
    ],

    'productbuilder' => [
        'class' => Thecodebunny\Bapubuilder\Modules\GrapesJS\ProductBuilder::class,
        'url' => '/editor/pages/build',
        'actions' => [
            'back' => '/editor/pages'
        ]
    ]
];