<?php

return [

    'default' => env('BROADCAST_CONNECTION', 'null'),

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            // Broadcasts run inside the user's request, so an unresponsive
            // Pusher must never hold a page hostage. Bound the round trip
            // hard; BroadcastsChangesSafely swallows the resulting failure.
            'client_options' => [
                'connect_timeout' => env('PUSHER_CONNECT_TIMEOUT', 2),
                'timeout' => env('PUSHER_TIMEOUT', 4),
            ],
        ],

        'log' => ['driver' => 'log'],

        'null' => ['driver' => 'null'],

    ],

];
