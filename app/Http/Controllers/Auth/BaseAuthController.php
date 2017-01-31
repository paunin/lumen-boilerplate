<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User as UserModel;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class BaseAuthController
 *
 * @package App\Http\Controllers\Auth
 */
class BaseAuthController extends Controller
{

    /**
     * @var JWTAuth
     */
    private $auth;

    /**
     * @param JWTAuth $auth JWT service
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @SWG\Get(
     *     tags={"Security"},
     *     path="/auth/{driver}",
     *     summary="Get url to proceed with 3d party authentication",
     *     description="Get url to proceed with 3d party authentication",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="Driver for authentication (facebook, google, twitter, etc) ",
     *         in="path",
     *         name="driver",
     *         required=true,
     *         type="string",
     *         enum={"facebook"}
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Wrong driver",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse"),
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Success",
     *         @SWG\Schema(
     *             type="object",
     *             format="json",
     *             @SWG\Property(property="url", type="string")
     *         )
     *     )
     * )
     * @return array
     */
    public function redirectToProvider()
    {
        return response()->json(
            [
                'url' => \Socialite::driver(static::$driver)
                                   ->stateless()
                                   ->redirect()
                                   ->getTargetUrl(),
            ]
        );
    }

    /**
     * @SWG\Get(
     *     tags={"Security"},
     *     path="/auth/{driver}/callback",
     *     summary="Get JWT token for user",
     *     description="Get JWT token for user",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="Driver for authentication (facebook, google, twitter, etc) ",
     *         in="path",
     *         name="driver",
     *         required=true,
     *         type="string",
     *         enum={"facebook"}
     *     ),
     *     @SWG\Parameter(
     *         description="Code from callback",
     *         in="query",
     *         name="code",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Wrong driver",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse"),
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Authentication problems",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse"),
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="Credentials problems",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse"),
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Authenticated",
     *         @SWG\Schema(
     *             type="object",
     *             format="json",
     *             @SWG\Property(property="token", type="string")
     *         )
     *     )
     * )
     *
     *
     * @return array
     */
    public function handleProviderCallback()
    {
        /** @var User $oauthUser */
        try {
            $oauthUser = \Socialite::driver(static::$driver)
                                   ->stateless()
                                   ->user();
        } catch (\Exception $e) {
            $this->response->errorBadRequest('Can\'t authenticate the user!');
        }

        $user  = UserModel::getOrCreateByOauth($oauthUser);
        $token = $this->auth->fromUser($user);
        if (!$token) {
            $this->response->errorForbidden(
                'User can\'t be authenticated with this method'
            );
        }

        return response()->json(['token' => $token]);
    }


    /**
     * @SWG\Post(
     *     tags={"Security"},
     *     path="/auth",
     *     summary="Authenticate user by username or email and return access token",
     *     description="Authenticate user by username or email and return access token",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="Body content",
     *         in="body",
     *         name="message",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *             format="json",
     *             required={"username", "password"},
     *             @SWG\Property(property="username", type="string"),
     *             @SWG\Property(property="password", type="string")
     *         )
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Authenticated",
     *         @SWG\Schema(
     *             type="object",
     *             format="json",
     *             @SWG\Property(property="token", type="string")
     *         )
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="Bad credentials",
     *         @SWG\Schema(ref="#/definitions/ErrorResponse"),
     *     )
     * )
     * @param Request $request HTTP-Request object
     *
     * @return Response
     * @throws HttpException
     */
    public function authenticate(Request $request)
    {
        $login = filter_var($request->get('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $payload = [
            $login     => strtolower($request->get('username')),
            'password' => $request->get('password'),
            'active'   => true,
        ];

        $token = $this->auth->attempt($payload);
        if (!$token) {
            $this->response->errorForbidden(
                'Username or password invalid. In doubt please contact the admin.'
            );
        }

        return response()->json(compact('token'));
    }
}
