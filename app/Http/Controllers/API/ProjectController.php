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
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ProjectController extends Controller
{
    /**
     * List the projects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        $projects = Project::cursorPaginate(10);

        return Response::generate(200, 'success', 'Projects retrieved', [
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

        return Response::generate(200, 'success', 'Project retrieved', [
            'project' => $project->toArray(),
        ]);
    }

    /**
     * Add a new project.
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
            return Response::generate(201, 'success', 'Project added', [
                'project' => $project->toArray(),
            ]);
        }

        return Response::generate(400, 'error', 'Project not created');
    }

    /**
     * Update the project.
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

            return Response::generate(200, 'success', 'Project updated');
        }

        return Response::generate(404, 'error', 'Project not found');
    }

    /**
     * Delete the project.
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

            return Response::generate(200, 'success', 'Project deleted');
        }

        return Response::generate(404, 'error', 'Project not found');
    }
}
