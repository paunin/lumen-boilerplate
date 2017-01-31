<?php

namespace Test\Unit\Http\Middleware;

use App\Http\Middleware\Acl;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Test\TestCase;
use Tymon\JWTAuth\JWTAuth;

/**
 * @covers \App\Http\Middleware\Acl
 */
class AclTest extends TestCase
{

    /**
     * @return array
     */
    public function providerHandleAccessDenied(): array
    {
        return [
            'has no access'         => [[Role::ROLE_USER], [Role::ROLE_ADMIN], false],
            'has access'            => [[Role::ROLE_ADMIN], [Role::ROLE_ADMIN], true],
            'has access multi-role' => [[Role::ROLE_ADMIN, Role::ROLE_USER], [Role::ROLE_ADMIN, Role::ROLE_USER], true],
        ];
    }

    /**
     * @dataProvider providerHandleAccessDenied
     *
     * @param array $userRoles
     * @param array $requiredRoles
     * @param bool  $access
     */
    public function testHandleAccessDenied(array $userRoles, array $requiredRoles, bool $access)
    {
        $user          = new User();
        $user['roles'] = $userRoles;
        /** @var JWTAuth $authMock */
        $authMock = $this
            ->getMockBuilder(JWTAuth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authMock->expects($this->once())
                 ->method('user')
                 ->willReturn($user);
        $middleware = new Acl($authMock);

        if (!$access) {
            $this->expectException('\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        }
        $middleware->handle(
            new Request(),
            function () {
            },
            implode('|', $requiredRoles)
        );
    }
}
