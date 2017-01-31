<?php

namespace Test\Behaviour\Http\Controllers;

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \App\Http\Controllers\UserController
 */
class UserControllerTest extends \Test\ApiTestCase
{
    use DatabaseTransactions;

    /** @var User */
    private $user;

    /**
     * Create user for testing
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create(
            [
                'username' => 'abigail',
                'email'    => 'abi@abi.com',
                'password' => '123456',
                'active'   => true,
            ]
        );
        $this->login('abigail', '123456');
    }

    /**
     * @covers ::me
     */
    public function testMeResource()
    {
        $this->authGet('/me');
        $this->seeJson(
            [
                'id'       => $this->user->getId(),
                'username' => $this->user->getUsername(),
                'active'   => true,
            ]
        );
        $this->dontSeeJson(['password']);
        $this->assertResponseOk();
    }
}
