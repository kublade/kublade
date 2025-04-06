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
 *          email="support@kublade.org"
 *      ),
 *
 *      @OA\License(
 *          name="BSL 1.1",
 *          url="https://kublade.org/docs/license/"
 *      )
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for user authentication"
 * )
 *
 * @OA\Components(
 *
 *     @OA\Response(
 *         response="ForbiddenResponse",
 *         description="Forbidden",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="success", type="boolean", example=false),
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
 *             @OA\Property(property="success", type="boolean", example=false),
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
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation Error"),
 *             @OA\Property(property="data", type="array", @OA\Items(type="string", example="The user id field is required."))
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
