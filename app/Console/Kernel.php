<?php

namespace App\Console;

use App\Console\Commands\CreateUserCommand;
use App\Console\Commands\CreateJwtTokenCommand;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 *
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateJwtTokenCommand::class,
        CreateUserCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
//    protected function schedule(Schedule $schedule)
//    {
//    }
}
