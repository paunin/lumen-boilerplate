<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Transformers\UserTransformer;
use Dingo\Api\Http\Response;
use Swagger\Annotations as SWG;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * @SWG\Get(
     *      tags={"User"},
     *      path="/me",
     *      summary="User resource",
     *      description="Get current user",
     *      @SWG\Response(
     *          response="200",
     *          description="OK",
     *          @SWG\Schema(
     *              @SWG\Property(property="data", ref="#/definitions/User")
     *         )
     *      )
     * )
     *
     * @param JWTAuth $jwt Service
     *
     * @return Response
     */
    public function me(JWTAuth $jwt): Response
    {
        return $this->response->item($jwt->user(), new UserTransformer());
    }
}
