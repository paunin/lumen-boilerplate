<?php

return [
    'nodeSetName'      => env('SENTINEL_NODE_SET_NAME', 'mymaster'),
    'cluster'          => false,
    // Sentinel nodes
    'masters'          => sentinels(env('SENTINELS', '')),
    'password'         => env('REDIS_PASSWORD', null),
    'port'             => env('REDIS_PORT', 26379),
    'database'         => env('REDIS_DATABASE', 0),

    /** how long to wait and try again if we fail to connect to master */
    'backoff-strategy' => [
        'max-attempts' => env('SENTINEL_MAX_ATTEMPTS', 10),
        // the maximum-number of attempt possible to find master
        'wait-time'    => env('SENTINEL_WAIT_TIME', 500),
        // miliseconds to wait for the next attempt
        'increment'    => env('SENTINEL_INCREMENT', 1.5),
        // multiplier used to increment the back off time on each try
    ],
];
