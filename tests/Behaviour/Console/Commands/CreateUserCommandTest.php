<?php

namespace Test\Behaviour\Eloquent\Models;

use App\Models\Role;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Test\TestCase;

/**
 * @covers \App\Console\Commands\CreateUserCommand
 */
class CreateUserCommandTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array
     */
    public function providerCreate(): array
    {
        return [
            'User'                 => ['username', 'username@email.com', 'password', [Role::ROLE_USER]],
            'User with wrong role' => [
                'username',
                'username@email.com',
                'password',
                ['wrong role'],
                '{"role.0":["Valid user roles: ' . implode(',', Role::$rules) . '"]}'
            ],
        ];
    }

    /**
     * Test create user
     *
     * @dataProvider providerCreate
     *
     * @param string $username
     * @param string $email
     * @param string $pass
     * @param array  $roles
     * @param string $exceptionMessage
     */
    public function testCreateUser(
        string $username,
        string $email,
        string $pass,
        array $roles,
        string $exceptionMessage = null
    ) {
        if ($exceptionMessage) {
            $this->expectExceptionMessage($exceptionMessage);
        }
        $this->artisan(
            'user:create',
            [
                '--username' => $username,
                '--email'    => $email,
                '--password' => $pass,
                '--role'     => $roles,
            ]
        );

        if (!$exceptionMessage) {
            /** @var User $user */
            $user = User::where('email', $email)
                        ->first();

            static::assertEquals($username, $user->getUsername());
            static::assertEquals($email, $user->getEmail());
            static::assertEquals($roles, $user->getRoles());
            static::assertTrue(\Hash::check($pass, $user->getAttribute('password')));
        }
    }
}
