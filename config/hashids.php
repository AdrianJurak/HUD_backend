<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Hashids Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like.
    |
    */

    'connections' => [

        'main' => [
            'salt' => env('HASHIDS_SALT', 'default_salt_oos1'),
            'length' => (int) env('SALT_LENGTH'),
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],

        'themes' => [
            'salt' => env('THEMES_SALT'),
            'length' => (int) env('SALT_LENGTH'),
        ],

        'reviews' => [
            'salt' => env('REVIEWS_SALT'),
            'length' => (int) env('SALT_LENGTH'),
        ],

        'users' => [
            'salt' => env('USER_SALT'),
            'length' => (int) env('SALT_LENGTH'),
        ],
    ],

];
