<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Intouch\Newrelic\Newrelic;
use phpmock\phpunit\PHPMock;

/**
 * @covers \App\Listeners\JobProcessEventSubscriber
 */
class JobProcessEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    /**
     * @var Job|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobMock;

    /**
     * @var Newrelic|\PHPUnit_Framework_MockObject_MockObject
     */
    private $newrelicMock;

    /**
     * @var JobProcessEventSubscriber
     */
    private $subscriber;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->newrelicMock = $this->createMock(Newrelic::class);
        $this->jobMock      = $this->createMock(Job::class);
        $this->subscriber   = new JobProcessEventSubscriber($this->newrelicMock);
    }

    /**
     * Test subscribe()
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubscribe()
    {
        $extensionLoadedMock = $this->getFunctionMock('App\Listeners', 'extension_loaded');
        $extensionLoadedMock->expects(static::once())
                            ->with('newrelic')
                            ->willReturn(true);

        /** @var Dispatcher|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->createMock(Dispatcher::class);

        $eventMock->expects(static::exactly(3))
                  ->method('listen')
                  ->withConsecutive(
                      [JobProcessing::class, JobProcessEventSubscriber::class . '@onJobProcessing'],
                      [JobProcessed::class, JobProcessEventSubscriber::class . '@onJobProcessed'],
                      [JobExceptionOccurred::class, JobProcessEventSubscriber::class . '@onJobExceptionOccurred']
                  );

        $this->subscriber->subscribe($eventMock);
    }

    /**
     * Test subscribe() in case newrelic extension is not loaded
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubscribeOnExtensionNotLoaded()
    {
        $extensionLoadedMock = $this->getFunctionMock('\\App\\Listeners', 'extension_loaded');
        $extensionLoadedMock->expects(static::once())
                            ->with('newrelic')
                            ->willReturn(false);

        /** @var Dispatcher|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->createMock(Dispatcher::class);

        $eventMock->expects(static::never())
                  ->method('listen');

        $this->subscriber->subscribe($eventMock);
    }

    /**
     * Test onJobProcessing()
     */
    public function testOnJobProcessing()
    {
        $event = new JobProcessing('connection', $this->jobMock, []);

        $queueName = 'queue-name';
        $this->jobMock->expects(static::once())
                      ->method('getQueue')
                      ->willReturn($queueName);
        $this->newrelicMock->expects(static::once())
                           ->method('startTransaction')
                           ->with(env('NEWRELIC__APPNAME'));
        $this->newrelicMock->expects(static::once())
                           ->method('backgroundJob')
                           ->with(true);
        $this->newrelicMock->expects(static::once())
                           ->method('nameTransaction')
                           ->with($queueName);

        $this->subscriber->onJobProcessing($event);
    }

    /**
     * Test onJobProcessed()
     */
    public function testOnJobProcessed()
    {
        $this->newrelicMock->expects(static::once())
                           ->method('endTransaction');

        $this->subscriber->onJobProcessed($this->createMock(JobProcessed::class));
    }

    /**
     * Test onJobExceptionOccurred()
     */
    public function testOnJobExceptionOccurred()
    {
        $exception = new \Exception('test test');
        $event     = new JobExceptionOccurred('connection', $this->jobMock, [], $exception);

        $this->newrelicMock->expects(static::once())
                           ->method('noticeError')
                           ->with(
                               sprintf('Exception happened while processing a job "%s".', get_class($this->jobMock))
                           );

        $this->newrelicMock->expects(static::once())
                           ->method('endTransaction');

        $this->subscriber->onJobExceptionOccurred($event);
    }

}
