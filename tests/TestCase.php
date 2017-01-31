<?php
declare(strict_types=1);

namespace Test;

use Carbon\Carbon;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

/**
 * Class TestCase
 */
class TestCase extends BaseTestCase
{
    use PHPMatcherAssertions;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        Carbon::setTestNow(Carbon::instance(new \DateTime()));
        parent::setUp();
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /**
     * @param string $json Expected json.
     *
     * @return $this
     */
    protected function seeJsonString($json)
    {
        return $this->seeJsonEquals(json_decode($json, true));
    }


    /**
     * @param string $queueName
     */
    protected function queueWork(string $queueName)
    {
        $hasException = false;
        \Queue::exceptionOccurred(
            function (JobExceptionOccurred $event) use (&$hasException) {
                $hasException = $event->exception->getMessage() . "\n" . $event->exception->getTraceAsString();
            }
        );

        $this->artisan('queue:work', ['--queue' => $queueName]);

        static::assertFalse($hasException, 'Should not have any exceptions');
    }
}
