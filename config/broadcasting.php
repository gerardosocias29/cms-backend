<?php

return [
  'default' => env('BROADCAST_DRIVER', 'log'),

  'connections' => [
    'pusher' => [
      'driver' => 'pusher',
      'key' => env('PUSHER_APP_KEY'),
      'secret' => env('PUSHER_APP_SECRET'),
      'app_id' => env('PUSHER_APP_ID'),
      'options' => [
        'host' => env('PUSHER_HOST', '127.0.0.1'),
        'port' => env('PUSHER_PORT', 6001),
        'scheme' => env('PUSHER_SCHEME', 'http'),
        'encrypted' => env('PUSHER_ENCRYPTED', false),
        'useTLS' => env('PUSHER_SCHEME', 'http') === 'https',
      ],
    ],

    'redis' => [
      'driver' => 'redis',
      'connection' => 'default',
    ],

    'log' => [
      'driver' => 'log',
    ],

    'null' => [
      'driver' => 'null',
    ],
  ],
];
