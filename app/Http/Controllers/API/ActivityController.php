<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;

/**
 * Class ActivityController.
 *
 * This class is the controller for the activity actions.
 *
 * @OA\Tag(
 *     name="Activities",
 *     description="Endpoints for activity management"
 * )
 *
 * @OA\Parameter(
 *     name="activity_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the activity",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ActivityController extends Controller
{
    /**
     * List the activities.
     *
     * @OA\Get(
     *     path="/api/activities",
     *     summary="List activities",
     *     tags={"Activities"},
     *
     *     @OA\Parameter(ref="#/components/parameters/cursor"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Activities retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="activities", type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/Activity")
     *                 ),
     *
     *                 @OA\Property(property="links", type="object",
     *                     @OA\Property(property="next", type="string"),
     *                     @OA\Property(property="prev", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        $activities = ActivityLog::query()
            ->orderByDesc('created_at')
            ->cursorPaginate(10);

        return Response::generate(200, 'success', 'Activities retrieved successfully', [
            'activities' => collect($activities->items())->map(function ($item) {
                return $item->toArray();
            }),
            'links' => [
                'next' => $activities->nextCursor()?->encode(),
                'prev' => $activities->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get the role.
     *
     * @OA\Get(
     *     path="/api/activities/{activity_id}",
     *     summary="Get an activity",
     *     tags={"Activities"},
     *
     *     @OA\Parameter(ref="#/components/parameters/activity_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Activity retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Activity retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="activity", ref="#/components/schemas/Activity")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string $activity_id
     * @param string $role_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get(string $activity_id)
    {
        $validator = Validator::make([
            'activity_id' => $activity_id,
        ], [
            'activity_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($activity = ActivityLog::where('id', $activity_id)->first()) {
            return Response::generate(200, 'success', 'Activity retrieved successfully', [
                'activity' => $activity->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Activity not found');
    }
}
