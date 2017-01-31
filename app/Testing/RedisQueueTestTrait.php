<?php

namespace App\Testing;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Predis\Client;

/**
 * Utils for testing queues.
 */
trait RedisQueueTestTrait
{
    /**
     * @param string $queue
     */
    protected function purgeQueue(string $queue)
    {
        $queueName = $this->getRedisQueueName($queue);

        $redisClient = $this->getRedisClient();
        $redisClient->del([$queueName, $queueName.':reserved', $queueName.':delayed']);
    }

    /**
     * @param string|null   $queue
     * @param \Closure|null $callback
     * @param bool          $pushBackToQueue
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function seeOneJobInQueue(string $queue, \Closure $callback = null, $pushBackToQueue = false)
    {
        $redisClient = $this->getRedisClient();
        $queueName   = $this->getRedisQueueName($queue);

        $redisJob = $redisClient->lpop($queueName);
        \PHPUnit_Framework_Assert::assertNotNull($redisJob, 'Should have a job in queue.');

        \PHPUnit_Framework_Assert::assertNull(
            $redisClient->lpop($queueName),
            'Should not have more than one job in queue.'
        );

        if ($callback !== null) {
            $callback(json_decode($redisJob, true));
        }

        if ($pushBackToQueue) {
            $redisClient->rpush($queueName, $redisJob);
        }
    }

    /**
     * @param string $queue
     */
    protected function seeNoJobsInQueue(string $queue)
    {
        $redisClient = $this->getRedisClient();

        \PHPUnit_Framework_Assert::assertNull(
            $redisClient->lpop($this->getRedisQueueName($queue)),
            'Should not have any jobs in queue.'
        );
    }

    /**
     * @param int    $numOfJobs
     * @param string $queue
     */
    protected function seeNumberOfJobsInQueue(int $numOfJobs, string $queue)
    {
        $redisClient = $this->getRedisClient();

        \PHPUnit_Framework_Assert::assertEquals(
            $numOfJobs,
            $redisClient->llen($this->getRedisQueueName($queue)),
            sprintf('Should have %s jobs in queue.', $numOfJobs)
        );
    }

    /**
     * @param string $queue
     * @param array  $callbacks
     * @param bool   $pushBackToQueue
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function seeJobsInQueue(string $queue, array $callbacks = [], $pushBackToQueue = false)
    {
        $redisClient = $this->getRedisClient();
        $queueName   = $this->getRedisQueueName($queue);

        foreach ($callbacks as $callback) {
            $redisJob = $redisClient->lpop($queueName);
            \PHPUnit_Framework_Assert::assertNotNull($redisJob, 'Should have a job in queue.');
            $callback(json_decode($redisJob, true));

            if ($pushBackToQueue) {
                $redisClient->rpush($queueName, $redisJob);
            }
        }
    }

    /**
     * @param string               $queue
     * @param \DateTime|string|int $fromTime
     * @param \DateTime|string|int $toTime
     * @param \Closure|null        $callback
     */
    protected function seeOneJobInDelayedQueue(string $queue, $fromTime, $toTime, \Closure $callback = null)
    {
        $queueName = $this->getRedisQueueName($queue).':delayed';
        $fromTime  = $fromTime instanceof \DateTime ? $fromTime->getTimestamp() : $fromTime;
        $toTime    = $toTime instanceof \DateTime ? $toTime->getTimestamp() : $toTime;
        $jobs      = $this->getRedisClient()->zrangebyscore($queueName, $fromTime, $toTime);

        \PHPUnit_Framework_Assert::assertCount(1, $jobs, 'Should have one job in delayed queue.');

        if ($callback !== null) {
            $callback(json_decode($jobs[0], true));
        }
    }

    /**
     * @param string               $queue
     * @param \DateTime|string|int $fromTime Can be -inf or a timestamp
     * @param \DateTime|string|int $toTime   Can be +inf or a timestamp
     */
    protected function seeNoJobsInDelayedQueue(string $queue, $fromTime = '-inf', $toTime = '+inf')
    {
        $queueName = $this->getRedisQueueName($queue).':delayed';

        $fromTime = $fromTime instanceof \DateTime ? $fromTime->getTimestamp() : $fromTime;
        $toTime   = $toTime instanceof \DateTime ? $toTime->getTimestamp() : $toTime;
        $jobs     = $this->getRedisClient()->zrangebyscore($queueName, $fromTime, $toTime);

        \PHPUnit_Framework_Assert::assertCount(0, $jobs, 'Should have no jobs in delayed queue.');
    }

    /**
     * @param string $queue
     * @param string $jobPayload
     */
    protected function updateJobToBeProcessable(string $queue, string $jobPayload)
    {
        $this->getRedisClient()->zadd(
            $this->getRedisQueueName($queue).':delayed',
            'XX',
            Carbon::instance(new \DateTime())->subSecond(1)->getTimestamp(),
            $jobPayload
        );
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null $queue
     *
     * @return string
     */
    protected function getRedisQueueName($queue)
    {
        return 'queues:'.$queue;
    }

    /**
     * @return Client
     */
    protected function getRedisClient(): Client
    {
        return app(Factory::class)->connection('redis')->getRedis()->connection();
    }

    /**
     * @param string $queueName
     */
    protected function queueWork(string $queueName)
    {
        $hasException = false;
        \Queue::exceptionOccurred(
            function (JobExceptionOccurred $event) use (&$hasException) {
                $hasException = $event->exception->getMessage()."\n".$event->exception->getTraceAsString();
            }
        );

        $this->artisan('queue:work', ['--queue' => $queueName]);

        static::assertFalse($hasException, 'Should not have any exceptions');
    }
}
