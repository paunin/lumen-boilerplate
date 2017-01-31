<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class Acl
 */
class Acl
{
    /**
     * The authentication guard factory instance.
     *
     * @var JWTAuth
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param JWTAuth $auth Auth service
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request  $request Http request object
     * @param \Closure $next    next middleware
     * @param string   $role    set of roles
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $roles = explode('|', $role);
        /** @var User $user */
        $user = $this->auth->user();

        if ($user) {
            foreach ($user->getRoles() as $userRole) {
                if (in_array($userRole, $roles)) {
                    return $next($request);
                }
            }
        }

        throw new AccessDeniedHttpException('User doesn\'t have access');
    }
}
