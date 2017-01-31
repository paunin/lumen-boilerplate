<?php
/** @var \Dingo\Api\Routing\Router $api */
$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    function (Dingo\Api\Routing\Router $api) {
        $api->get('auth/facebook', App\Http\Controllers\Auth\FacebookController::class . '@redirectToProvider');
        $api->get(
            'auth/facebook/callback',
            App\Http\Controllers\Auth\FacebookController::class . '@handleProviderCallback'
        );
        $api->post('auth', \App\Http\Controllers\Auth\BaseAuthController::class . '@authenticate');

        $api->group(
            [
                'middleware' => ['api.auth', 'auth.user_checker'],
            ],
            function (\Dingo\Api\Routing\Router $api) {
                $api->get('me', \App\Http\Controllers\UserController::class . '@me');

                /*$api->group(
                    [
                        'middleware' => ['role:'.Role::ROLE_ADMIN.'|'.Role::ROLE_TPL],
                    ],
                    function (Router $api) {
                        $api->post('/carriers/{carrierSlug}/packages/statuses', PackageStatusController::class.'@post');
                    }
                );*/
            }
        );
    }
);
