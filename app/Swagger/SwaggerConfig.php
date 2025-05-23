<?php

declare(strict_types=1);

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="0.0.0",
 *      title="Kublade API Documentation",
 *      description="Kublade Swagger API documentation",
 *
 *      @OA\Contact(
 *          email="hi@kublade.org"
 *      ),
 *
 *      @OA\License(
 *          name="Apache-2.0",
 *          url="https://kublade.org/docs/license/"
 *      )
 * )
 *
 * @OA\Components(
 *
 *     @OA\Parameter(
 *         name="cursor",
 *         in="query",
 *         required=false,
 *         description="Cursor for pagination",
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Response(
 *         response="ForbiddenResponse",
 *         description="Forbidden",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Forbidden")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="NotFoundResponse",
 *         description="Not found",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Not Found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="ValidationErrorResponse",
 *         description="Validation error",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Validation Error"),
 *             @OA\Property(property="data", type="array", @OA\Items(type="string", example="The user id field is required."))
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="ServerErrorResponse",
 *         description="Server error",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Server Error")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="UnauthorizedResponse",
 *         description="Unauthorized",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth",
 *     description="JWT Authorization header using the Bearer scheme. Example: 'Bearer {token}'"
 * )
 */
class SwaggerConfig
{
}
