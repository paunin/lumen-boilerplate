<?php

namespace Test\Behaviour\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\RequestException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Test\ApiTestCase;

/**
 * @covers \App\Http\Controllers\Auth\BaseAuthController
 */
class SocialNetworksAuthControllerTest extends ApiTestCase
{
    use DatabaseTransactions;

    /**
     * Create user for testing
     */
    public function setUp()
    {
        parent::setUp();

        factory(User::class)->create(
            [
                'username' => 'Abigail',
                'email'    => 'abigail@domain.com',
                'password' => 123456,
                'active'   => true,
            ]
        );
    }

    /**
     * @return array
     */
    public function providerRedirectToProvider(): array
    {
        return [
            'facebook' => ['facebook']
        ];
    }

    /**
     * @param string $driver
     *
     * @dataProvider providerRedirectToProvider
     */
    public function testRedirectToProvider(string $driver)
    {
        $this->get('/auth/' . $driver);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['url']);
    }

    /**
     * @return array
     */
    public function providerHandleProviderCallback(): array
    {
        return [
            'facebook user' => ['facebook', 'some@email.com', 'som_user']
        ];
    }

    /**
     * @dataProvider providerHandleProviderCallback
     *
     * @param string $driver
     * @param string $email
     * @param string $nickname
     */
    public function testHandleProviderCallback(string $driver, string $email, string $nickname)
    {
        $oauthUser = $this->getMockBuilder(\Laravel\Socialite\Contracts\User::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $oauthUser->method('getEmail')
                  ->willReturn($email);
        $oauthUser->method('getNickname')
                  ->willReturn($nickname);
        $providerClass = "\\Laravel\\Socialite\\Two\\" . ucfirst($driver) . 'Provider';

        $provider = $this->getMockBuilder($providerClass)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider->method('user')
                 ->willReturn($oauthUser);
        $provider->method('stateless')
                 ->willReturnSelf();

        \Socialite::shouldReceive('driver')
                  ->once()
                  ->with($driver)
                  ->andReturn($provider);

        $this->get('/auth/' . $driver . '/callback');

        $this->assertResponseStatus(200);
        $this->seeJsonStructure(['token']);
    }

    /**
     * @dataProvider providerHandleProviderCallback
     *
     * @param string $driver
     * @param string $email
     * @param string $nickname
     */
    public function testHandleProviderCallbackFail(string $driver, string $email, string $nickname)
    {
        $oauthUser = $this->getMockBuilder(\Laravel\Socialite\Contracts\User::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $oauthUser->method('getEmail')
                  ->willReturn($email);
        $oauthUser->method('getNickname')
                  ->willReturn($nickname);

        $providerClass = "\\Laravel\\Socialite\\Two\\" . ucfirst($driver) . 'Provider';

        $provider = $this->getMockBuilder($providerClass)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider->method('stateless')
                 ->willReturnSelf();

        $exception = $this->getMockBuilder(RequestException::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $provider->method('user')
                 ->willThrowException($exception);

        \Socialite::shouldReceive('driver')
                  ->once()
                  ->with($driver)
                  ->andReturn($provider);

        $this->get('/auth/' . $driver . '/callback');

        $this->assertResponseStatus(400);
        $this->seeJsonString(
            '{"message":"Can\'t authenticate the user!","status_code":400}'
        );
    }


    /**
     * @dataProvider providerHandleProviderCallback
     *
     * @param string $driver
     * @param string $email
     * @param string $nickname
     */
    public function testHandleProviderCallbackFailAuth(string $driver, string $email, string $nickname)
    {
        $oauthUser = $this->getMockBuilder(\Laravel\Socialite\Contracts\User::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $oauthUser->method('getEmail')
                  ->willReturn($email);
        $oauthUser->method('getNickname')
                  ->willReturn($nickname);

        $providerClass = "\\Laravel\\Socialite\\Two\\" . ucfirst($driver) . 'Provider';

        $provider = $this->getMockBuilder($providerClass)
                         ->disableOriginalConstructor()
                         ->getMock();
        $provider->method('stateless')
                 ->willReturnSelf();

        $provider->method('user')
                 ->willReturn($oauthUser);
        $provider->method('stateless')
                 ->willReturnSelf();

        \Socialite::shouldReceive('driver')
                  ->once()
                  ->with($driver)
                  ->andReturn($provider);

        \JWTAuth::shouldReceive('fromUser')
                ->once()
                ->andReturn(null);

        $this->get('/auth/' . $driver . '/callback');

        $this->assertResponseStatus(403);
        $this->seeJsonString(
            '{"message":"User can\'t be authenticated with this method","status_code":403}'
        );
    }
}
