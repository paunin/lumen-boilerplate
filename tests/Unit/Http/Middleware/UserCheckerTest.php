<?php

namespace Test\Unit\Http\Middleware;

use App\Http\Middleware\UserChecker;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Test\TestCase;
use Tymon\JWTAuth\JWTAuth;

/**
 * @covers \App\Http\Middleware\UserChecker
 */
class UserCheckerTest extends TestCase
{

    /**
     * @return array
     */
    public function providerHandle(): array
    {
        return [
            'active user'   => [true],
            'inactive user' => [false]
        ];
    }

    /**
     * @param bool $active
     *
     * @dataProvider providerHandle
     */
    public function testHandle(bool $active)
    {
        $user     = new User(['active' => $active]);
        $authMock = $this
            ->getMockBuilder(JWTAuth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authMock
            ->expects($this->once())
            ->method('user')
            ->willReturn($user);
        $middleware = new UserChecker($authMock);

        if (!$active) {
            $this->expectException(UnauthorizedHttpException::class);
        }

        $middleware->handle(
            new Request(),
            function () {
            }
        );
    }
}
