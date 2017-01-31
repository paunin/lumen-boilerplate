<?php

namespace Test\Behaviour\Models;

use App\Models\User;
use Faker\Provider\DateTime;
use Faker\Provider\Uuid;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Test\TestCase;

/**
 * @coversDefaultClass \App\Models\User
 */
class UserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::getJWTIdentifier
     */
    public function testGetJWTIdentifier()
    {
        /** @var User $user */
        $user = factory(User::class)->make();
        static::assertEquals($user->getId(), $user->getJWTIdentifier());
    }

    /**
     * @covers ::getJWTCustomClaims
     */
    public function testGetJWTCustomClaims()
    {
        $user = factory(User::class)->make();
        static::assertEquals([], $user->getJWTCustomClaims());
    }

    /**
     * @covers ::setPasswordAttribute
     */
    public function testSetPasswordAttribute()
    {
        $user = new User();

        $user->setPasswordAttribute('123456');

        static::assertTrue(Hash::check('123456', $user->getAttribute('password')));
    }

    /**
     * @covers ::setUsernameAttribute
     */
    public function testSetUsernameAttribute()
    {
        $user = new User();

        $user->setUsernameAttribute('TEST-USER');

        static::assertEquals('test-user', $user->getUsername());
    }

    /**
     * Tests user getter
     */
    public function testGetters()
    {
        $id          = Uuid::uuid();
        $createdTime = DateTime::dateTime();
        $updatedTime = DateTime::dateTime();
        $userName    = 'user1';
        $email       = 'email@domain.com';
        $roles       = ['Dummy', 'Zombie'];

        /** @var User $user */
        $user = factory(User::class)->make(
            [
                'id'         => $id,
                'username'   => $userName,
                'email'      => $email,
                'roles'      => $roles,
                'active'     => true,
                'created_at' => $createdTime,
                'updated_at' => $updatedTime,
            ]
        );

        static::assertEquals($id, $user->getId());
        static::assertEquals($userName, $user->getUsername());
        static::assertEquals($email, $user->getEmail());
        static::assertEquals($roles, $user->getRoles());
        static::assertEquals($createdTime, $user->getCreatedAt());
        static::assertEquals($updatedTime, $user->getUpdatedAt());
        static::assertTrue($user->isActive());
    }

    /**
     * @test
     */
    public function testUnsetPassword()
    {
        /** @var User $user */
        $user = factory(User::class)->make([]);

        static::assertNotEmpty($user->getAuthPassword());
        $user->unsetPassword();
        static::assertNull($user->getAuthPassword());
    }

    /**
     * @return array
     */
    public function providerGetOrCreateByOauth(): array
    {
        return [
            'normal user' => ['user1@email.com', 'user1'],
            'normal user 2' => ['user2@email.com', 'user2'],
            'No email user' => ['', 'user3'],
        ];
    }


    /**
     * @param string      $email
     * @param string|null $nickname
     *
     * @dataProvider providerGetOrCreateByOauth
     */
    public function testGetOrCreateByOauthTwice(string $email, string $nickname = null)
    {
        $oauthUser = $this->getMockBuilder(\Laravel\Socialite\Contracts\User::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $oauthUser->method('getEmail')
                  ->willReturn($email);
        $oauthUser->method('getNickname')
                  ->willReturn($nickname);

        $this->notSeeInDatabase((new User())->getTable(), ['email' => $email]);
        $user = User::getOrCreateByOauth($oauthUser);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());

        $this->seeInDatabase((new User())->getTable(), ['email' => $email]);
        $user = User::getOrCreateByOauth($oauthUser);
        $this->assertInstanceOf(User::class, $user);
    }
}
