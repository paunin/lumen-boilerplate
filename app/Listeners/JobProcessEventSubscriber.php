<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

use Intouch\Newrelic\Newrelic;

/**
 * Subscribe to job processing events to manage NewRelic transactions.
 */
class JobProcessEventSubscriber
{
    /**
     * @var Newrelic
     */
    private $newrelic;

    /**
     * JobProcessEventSubscriber constructor.
     *
     * @param Newrelic $newrelic
     */
    public function __construct(Newrelic $newrelic)
    {
        $this->newrelic = $newrelic;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        if (!extension_loaded('newrelic')) {
            return;
        }

        $events->listen(JobProcessing::class, self::class . '@onJobProcessing');
        $events->listen(JobProcessed::class, self::class . '@onJobProcessed');
        $events->listen(JobExceptionOccurred::class, self::class . '@onJobExceptionOccurred');
    }

    /**
     * @param JobProcessing $event
     */
    public function onJobProcessing(JobProcessing $event)
    {
        $queueName = $event->job->getQueue();
        $this->newrelic->startTransaction(env('NEWRELIC__APPNAME'));
        $this->newrelic->backgroundJob(true);
        $this->newrelic->nameTransaction($queueName);
    }

    /**
     * @param JobProcessed $event
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function onJobProcessed(JobProcessed $event)
    {
        $this->newrelic->endTransaction();
    }

    /**
     * @param JobExceptionOccurred $event
     */
    public function onJobExceptionOccurred(JobExceptionOccurred $event)
    {
        $this->newrelic->noticeError(
            sprintf('Exception happened while processing a job "%s".', get_class($event->job)),
            $event->exception
        );
        $this->newrelic->endTransaction();
    }
}
