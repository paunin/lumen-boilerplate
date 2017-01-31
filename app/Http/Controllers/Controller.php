<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Laravel\Lumen\Routing\Controller as BaseController;
use Swagger\Annotations as SWG;

/**
 * @SWG\Swagger(
 *     schemes={"http", "https"},
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="PlaceItUp API",
 *         description="PlaceItUp API"
 *     ),
 *     security={
 *         {
 *             "jwt_auth": {}
 *         }
 *     },
 *     basePath="/",
 *     @SWG\SecurityScheme(
 *         name="Authorization",
 *         description="Authenticate by JWT token.",
 *         type="apiKey",
 *         in="header",
 *         securityDefinition="jwt_auth"
 *     ),
 *     @SWG\Definition(
 *          definition="ErrorResponse",
 *          @SWG\Property(property="message", type="string"),
 *          @SWG\Property(property="code", type="integer", description="Exception code"),
 *          @SWG\Property(property="status_code", type="integer", description="HTTP status code"),
 *          @SWG\Property(property="debug", type="object", format="json", description="Debug information"),
 *          @SWG\Property(
 *              property="errors",
 *              type="array",
 *              @SWG\Items(
 *                  type="array",
 *                  @SWG\Items(type="string")
 *              )
 *          )
 *     )
 * )
 */
class Controller extends BaseController
{
    use Helpers;
}
