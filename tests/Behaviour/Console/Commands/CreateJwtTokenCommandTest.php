<?php

namespace Test\Behaviour\Console\Commands;

use App\Console\Kernel;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Test\TestCase;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;

/**
 * @covers \App\Console\Commands\CreateJwtTokenCommand
 */
class CreateJwtTokenCommandTest extends TestCase
{
    const TEST_USER = 'test-user';
    use DatabaseTransactions;

    /**
     * @var JWTAuth
     */
    private $jwtAuth;

    /**
     * @var Kernel
     */
    private $console;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->jwtAuth = app(JWTAuth::class);
        $this->jwtAuth->unsetToken();

        $this->console = $this->app['Illuminate\Contracts\Console\Kernel'];
        factory(User::class)->create(['username' => self::TEST_USER]);
    }


    /**
     * Test in success case.
     */
    public function testSuccess()
    {
        $ttl      = rand(100, 200);
        $exitCode = $this->console->call('user:create-token', ['username' => self::TEST_USER, 'ttl' => $ttl]);
        static::assertEquals(0, $exitCode);

        $token = str_replace("\n", '', $this->console->output());

        /** @var User $user */
        $user = $this->jwtAuth->setToken($token)
                              ->authenticate();

        static::assertInstanceOf(User::class, $user);
        static::assertEquals(self::TEST_USER, $user->getUsername());

        Carbon::setTestNow(
            Carbon::now('UTC')
                  ->addSeconds($ttl * 86400 + 1)
        );

        $this->jwtAuth->unsetToken();

        $hasException = false;
        try {
            $this->jwtAuth->setToken($token)
                          ->authenticate();
        } catch (\Exception $exception) {
            $hasException = true;
            static::assertInstanceOf(TokenExpiredException::class, $exception);
        }

        static::assertTrue($hasException, 'Should have an exception.');
    }

    /**
     * @return array
     */
    public function failDataProvider()
    {
        return [
            'Invalid username and ttl' => [
                'data'          => [
                    'username' => 'not-found-username',
                    'ttl'      => 'invalid-ttl',
                ],
                'expectMessage' => "username: The selected username is invalid.\nttl: The ttl must be an integer.\n",
            ],
            'TTL is 0'                 => [
                'data'          => [
                    'username' => self::TEST_USER,
                    'ttl'      => 0,
                ],
                'expectMessage' => "ttl: The ttl must be at least 1.\n",
            ],
        ];
    }

    /**
     * Test in validation failed case.
     *
     * @param array  $data
     * @param string $expectMessage
     *
     * @dataProvider failDataProvider
     */
    public function testOnValidationFailed(array $data, string $expectMessage)
    {
        $code = $this->console->call('user:create-token', $data);

        static::assertEquals(1, $code);
        static::assertEquals(
            $expectMessage,
            $this->console->output()
        );
    }
}
