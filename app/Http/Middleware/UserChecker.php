<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Security\User\UserInterface as SecurityUserInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class UserChecker
 */
class UserChecker
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
     * @param JWTAuth $auth
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws UnauthorizedHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = $this->auth->user();
        if ($user instanceof SecurityUserInterface && (!$user->isActive())) {
            throw new UnauthorizedHttpException('User has been deactivated!');
        }

        return $next($request);
    }
}
