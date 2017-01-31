<?php

return [
    'auth'  => [
        'jwt' => 'Dingo\Api\Auth\Provider\JWT',
    ],
    'debug' => env('API_DEBUG', false)
];
