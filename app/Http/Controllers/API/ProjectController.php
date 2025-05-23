<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use App\Models\Projects\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Class ProjectController.
 *
 * This class is the controller for the project actions.
 *
 * @OA\Tag(
 *     name="Projects",
 *     description="Endpoints for project management"
 * )
 *
 * @OA\Parameter(
 *     name="project_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the project",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List the projects.
     *
     * @OA\Get(
     *     path="/api/projects",
     *     summary="List projects",
     *     tags={"Projects"},
     *
     *     @OA\Parameter(ref="#/components/parameters/cursor"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Projects retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Projects retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="projects", type="array",
     *
     *                     @OA\Items(type="object")
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
        $projects = Project::cursorPaginate(10);

        return Response::generate(200, 'success', 'Projects retrieved successfully', [
            'projects' => collect($projects->items())->map(function ($item) {
                return $item->toArray();
            }),
            'links' => [
                'next' => $projects->nextCursor()?->encode(),
                'prev' => $projects->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get the project.
     *
     * @OA\Get(
     *     path="/api/projects/{project_id}",
     *     summary="Get a project",
     *     tags={"Projects"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Project retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
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
     * @param string $project_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get(string $project_id)
    {
        $validator = Validator::make([
            'project_id' => $project_id,
        ], [
            'project_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $project = Project::where('id', $project_id)->first();

        if (!$project) {
            return Response::generate(404, 'error', 'Project not found');
        }

        return Response::generate(200, 'success', 'Project retrieved successfully', [
            'project' => $project->toArray(),
        ]);
    }

    /**
     * Add a new project.
     *
     * @OA\Post(
     *     path="/api/projects",
     *     summary="Add a new project",
     *     tags={"Projects"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Project added successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project added successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $project = Project::create([
                'user_id' => Auth::id(),
                'name'    => $request->name,
            ])
        ) {
            return Response::generate(201, 'success', 'Project added successfully', [
                'project' => $project->toArray(),
            ]);
        }

        return Response::generate(500, 'error', 'Project not created');
    }

    /**
     * Update the project.
     *
     * @OA\Patch(
     *     path="/api/projects/{project_id}",
     *     summary="Update a project",
     *     tags={"Projects"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Project updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string  $project_id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_update(string $project_id, Request $request)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            [
                'project_id' => $project_id,
            ]
        ), [
            'project_id' => ['required', 'string', 'max:255'],
            'name'       => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $project = Project::where('id', $project_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $project->update([
                'name' => $request->name,
            ]);

            return Response::generate(200, 'success', 'Project updated successfully', [
                'project' => $project->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Project not found');
    }

    /**
     * Delete the project.
     *
     * @OA\Delete(
     *     path="/api/projects/{project_id}",
     *     summary="Delete a project",
     *     tags={"Projects"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Project deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project deleted successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
     *
     * @param string $project_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_delete(string $project_id)
    {
        $validator = Validator::make([
            'project_id' => $project_id,
        ], [
            'project_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $project = Project::where('id', $project_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $project->delete();

            return Response::generate(200, 'success', 'Project deleted successfully', [
                'project' => $project->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Project not found');
    }
}
