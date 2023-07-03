<?php

return [

    'default' => env('DB_CONNECTION', 'common'),

    'connections' => [

        'common' => [
            'driver' => 'mysql',
            'host' => env('DB_COMMON_HOST', '127.0.0.1'),
            'port' => env('DB_COMMON_PORT', '3306'),
            'database' => env('DB_COMMON_DATABASE', 'gojek_common'),
            'username' => env('DB_COMMON_USERNAME', 'root'),
            'password' => env('DB_COMMON_PASSWORD', 'root'),
            'strict' => false,
        ],

        'transport' => [
            'driver' => 'mysql',
            'host' => env('DB_TRANSPORT_HOST', '127.0.0.1'),
            'port' => env('DB_TRANSPORT_PORT', '3306'),
            'database' => env('DB_TRANSPORT_DATABASE', 'gojek_transport'),
            'username' => env('DB_TRANSPORT_USERNAME', 'root'),
            'password' => env('DB_TRANSPORT_PASSWORD', 'root'),
            'strict' => false,
        ],

        'order' => [
            'driver' => 'mysql',
            'host' => env('DB_ORDER_HOST', '127.0.0.1'),
            'port' => env('DB_ORDER_PORT', '3306'),
            'database' => env('DB_ORDER_DATABASE', 'gojek_order'),
            'username' => env('DB_ORDER_USERNAME', 'root'),
            'password' => env('DB_ORDER_PASSWORD', 'root'),
            'strict' => false,
        ],

        'service' => [
            'driver' => 'mysql',
            'host' => env('DB_SERVICE_HOST', '127.0.0.1'),
            'port' => env('DB_SERVICE_PORT', '3306'),
            'database' => env('DB_SERVICE_DATABASE', 'gojek_service'),
            'username' => env('DB_SERVICE_USERNAME', 'root'),
            'password' => env('DB_SERVICE_PASSWORD', 'root'),
            'strict' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],


];
