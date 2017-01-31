<?php

namespace Test\Behaviour\Http\Controllers;

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @covers \App\Http\Controllers\Auth\BaseAuthController
 */
class AuthControllerTest extends \Test\ApiTestCase
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
    public function authenticateMissingParameterProvider()
    {
        return [
            'empty params'     => [
                'params' => [],
            ],
            'missing username' => [
                'params' => ['password' => '123456'],
            ],
            'missing password' => [
                'params' => ['username' => 'Abigail'],
            ],
            'empty password'   => [
                'params' => ['username' => 'Abigail', 'password' => ' '],
            ],
            'empty username'   => [
                'params' => ['username' => '    ', 'password' => "123"],
            ],
        ];
    }

    /**
     * @dataProvider authenticateMissingParameterProvider
     *
     * @param array $params
     */
    public function testAuthenticateWhenMissingParameter(array $params)
    {
        $this->post('/auth', $params);

        $this->assertResponseStatus(403);

        $this->seeJsonEquals(
            [
                'message'     => 'Username or password invalid. In doubt please contact the admin.',
                'status_code' => 403,
            ]
        );
    }


    /**
     * @return array
     */
    public function providerAuthenticateWithInactiveUser(): array
    {
        return [
            'user inactive' => [
                [
                    'username' => 'user2',
                    'password' => '123456',
                    'active'   => false,
                ]
            ]
        ];
    }

    /**
     * Test authenticate api with an inactive user.
     *
     * @dataProvider providerAuthenticateWithInactiveUser
     *
     * @param array $userData
     */
    public function testAuthenticateWithInactiveUser(array $userData)
    {
        factory(User::class)->create($userData);

        $this->post('/auth', $userData);

        $this->assertResponseStatus(403);

        $this->seeJsonContains(
            [
                'message'     => 'Username or password invalid. In doubt please contact the admin.',
                'status_code' => 403,
            ]
        );
    }

    /**
     * @return array
     */
    public function authenticateDataProvider()
    {
        return [
            'login by username'            => [
                'credential' => [
                    'username' => 'abigail',
                    'password' => '123456',
                ],
            ],
            'login by email'               => [
                'credential' => [
                    'username' => 'abigail@domain.com',
                    'password' => '123456',
                ],
            ],
            'login by UPPER CASE username' => [
                'credential' => [
                    'username' => 'ABIGAIL',
                    'password' => '123456',
                ],
            ],
            'login by UPPER CASE email'    => [
                'credential' => [
                    'username' => 'ABIGAIL@domain.com',
                    'password' => '123456',
                ],
            ],
        ];
    }

    /**
     * @param array $credential
     *
     * @dataProvider authenticateDataProvider
     */
    public function testAuthenticateSuccess(array $credential)
    {
        $this->post('/auth', $credential);

        $this->assertResponseOk();

        $this->seeJsonStructure(
            [
                'token',
            ]
        );
    }


    /**
     * Test authorization after user has been deactivated
     *
     * @covers \App\Http\Middleware\UserChecker
     */
    public function testAuthorizationWithDeactivatedUser()
    {
        /** @var User $user */
        $user = factory(User::class)->create(
            [
                'username' => 'user1',
                'password' => '123456',
                'active'   => true,
            ]
        );

        $this->login('user1', '123456');
        $user->update(['active' => false]);
        $this->authGet('/me');

        $this->seeInHeaders(['www-authenticate' => 'User has been deactivated!']);
        $this->seeJsonString('{"message":"401 Unauthorized","status_code":401}');
        $this->assertResponseStatus(401);
    }
}
