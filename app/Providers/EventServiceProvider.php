<?php

namespace App\Providers;

use App\Listeners\JobProcessEventSubscriber;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
//    /**
//     * The event listener mappings for the application.
//     *
//     * @var array
//     */
//    protected $listen = [
//        'App\Events\SomeEvent' => [
//            'App\Listeners\EventListener',
//        ],
//    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        JobProcessEventSubscriber::class,
    ];
}
