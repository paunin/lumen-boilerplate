<?php

namespace Test\Unit\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Monolog\Handler\TestHandler;
use Symfony\Component\HttpFoundation\Cookie;
use Test\ApiTestCase;

/**
 * @covers \App\Http\Middleware\TrafficLogger
 */
class TrafficLoggerTest extends ApiTestCase
{
    use DatabaseTransactions;

    /**
     * Test logging request and response.
     */
    public function testLogRequestResponse()
    {
        $this->app->get(
            '/api/test-logger',
            function () {
                /** @var Response $response */
                $response = response('test logger response body', 200);
                $response->headers->setCookie(new Cookie('Test_Cookie', 'test cookie', 0, '/abc', 'abc.test'));

                return $response;
            }
        );
        $user        = factory(User::class)->create(['active' => true]);
        $this->token = \Auth::login($user);

        $testHandler = new TestHandler();
        \Log::pushHandler($testHandler);

        $this->authGet('/api/test-logger');

        $records = $testHandler->getRecords();
        static::assertCount(1, $records);

        $context = $records[0]['context'];

        static::assertEquals('Incoming request and its response.', $records[0]['message']);
        static::assertArraySubset(
            [
                'request'  => [
                    'method'         => 'GET',
                    'uri'            => 'http://localhost/api/test-logger',
                    'serverProtocol' => 'HTTP/1.1',
                    'headers'        => [
                        'Authorization' => ['Bearer ' . $this->token],
                    ],
                    'content'        => '',
                ],
                'response' => [
                    'protocolVersion' => 'HTTP/1.0',
                    'statusCode'      => 200,
                    'statusText'      => 'OK',
                    'headers'         => [
                        'Set-Cookie' => ['Test_Cookie=test+cookie; path=/abc; domain=abc.test; httponly'],
                    ],
                    'content'         => 'test logger response body',
                ],
            ],
            $context
        );
    }
}
