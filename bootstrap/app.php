<?php

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades(
    true,
    [
        Laravel\Socialite\Facades\Socialite::class => 'Socialite',
        Illuminate\Support\Facades\Hash::class     => 'Hash',
        Dingo\Api\Facade\API::class                => 'API',
        Dingo\Api\Facade\Route::class              => 'Route',
        Tymon\JWTAuth\Facades\JWTAuth::class       => 'JWTAuth',
        Tymon\JWTAuth\Facades\JWTFactory::class    => 'JWTFactory'
    ]
);
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware(
    [
        \App\Http\Middleware\TrafficLogger::class,
        Intouch\LaravelNewrelic\LumenNewrelicMiddleware::class,
    ]
);

$app->routeMiddleware(
    [
        'auth.user_checker' => \App\Http\Middleware\UserChecker::class,
        'role'              => \App\Http\Middleware\Acl::class,
    ]
);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(Intouch\LaravelNewrelic\LumenNewrelicServiceProvider::class);
$app->register(Felixkiss\UniqueWithValidator\UniqueWithValidatorServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Irazasyed\Larasupport\Providers\ArtisanServiceProvider::class);
$app->register(LaravelPSRedis\LaravelPSRedisServiceProvider::class);
$app->register(Laravel\Socialite\SocialiteServiceProvider::class);
$app->register(Dingo\Api\Provider\LumenServiceProvider::class);
$app->register(Laswagger\Providers\LumeSwaggerServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);

// non-productions
if ($app->environment() !== 'production') {
    $app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
}

/*
|--------------------------------------------------------------------------
| Aliases
|--------------------------------------------------------------------------
*/

$app[\Dingo\Api\Exception\Handler::class]->setErrorFormat(
    [
        'message'     => ':message',
        'errors'      => ':errors',
        'code'        => ':code',
        'status_code' => ':status_code',
        'debug'       => ':debug',
    ]
);

/*
|--------------------------------------------------------------------------
| Custom configurations
|--------------------------------------------------------------------------
*/
$app->configure('services');

$app[\Dingo\Api\Exception\Handler::class]->register(
    function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(null, $e);
    }
);

Dingo\Api\Http\Response::addFormatter('json', new Dingo\Api\Http\Response\Format\Jsonp());

$app[\Dingo\Api\Transformer\Factory::class]
    ->register(\App\Models\User::class, \App\Transformers\UserTransformer::class);
/*
|--------------------------------------------------------------------------
| Load The Application Routes8e
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(
    ['namespace' => 'App\Http\Controllers'],
    function ($app) {
        require __DIR__ . '/../routes/api.php';
    }
);

$app->configureMonologUsing(
    function (Monolog\Logger $monolog) {
        $handler = app()->environment() === 'testing' ? new \Monolog\Handler\TestHandler(
        ) : new \DockerMonologHandler\DockerMonologHandler();

        return $monolog->pushHandler($handler);
    }
);

return $app;
