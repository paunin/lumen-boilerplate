<?php

namespace Test\Unit\Transformers;

use App\Models\User;
use App\Transformers\UserTransformer;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Test\TestCase;

/**
 * Class UserTransformerTest
 *
 * @covers \App\Transformers\UserTransformer
 */
class UserTransformerTest extends TestCase
{
    use DatabaseTransactions;


    /**
     * @return array
     */
    public function providerTransform(): array
    {
        return [
            'normal user' => [
                [
                    'username' => 'transform',
                    'password' => '123456',
                    'email'    => 'transform@transform.vn',
                    'active'   => true,
                    'roles'    => ['Tranform'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerTransform
     *
     * @param array $userData
     */
    public function testTransform(array $userData)
    {
        $transform = new UserTransformer();

        $user = factory(User::class)->create(
            $userData
        );

        $transformResult = $transform->transform($user);
        static::assertTrue($transformResult['active']);
        static::assertArrayHasKey('createdAt', $transformResult);
        static::assertArrayHasKey('updatedAt', $transformResult);
        static::assertEquals($user->getRoles(), $transformResult['roles']);
        static::assertEquals($user->getId(), $transformResult['id']);
        static::assertEquals($user->getEmail(), $transformResult['email']);
        static::assertEquals($user->getUsername(), $transformResult['username']);
    }
}
