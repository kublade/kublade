<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Projects\ProjectInvitation;
use App\Models\User;
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
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List the projects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        $projects = Project::where(function ($query) {
            $query->where('user_id', '=', Auth::id())
                ->orWhereHas('invitations', function ($query) {
                    $query->where('user_id', '=', Auth::id())
                        ->where('invitation_accepted', '=', true);
                });
        })->cursorPaginate(10);

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

    /**
     * List the project invitations.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list_invitation(string $project_id)
    {
        $validator = Validator::make([
            'project_id' => $project_id,
        ], [
            'project_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $projectInvitations = ProjectInvitation::where('project_id', $project_id)->cursorPaginate(10);

        return Response::generate(200, 'success', 'Project invitations retrieved', [
            'project_invitations' => collect($projectInvitations->items())->map(function ($item) {
                return $item->toArray();
            }),
            'links' => [
                'next' => $projectInvitations->nextCursor()?->encode(),
                'prev' => $projectInvitations->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get the project invitation.
     *
     * @param string $project_id
     * @param string $project_invitation_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get_invitation(string $project_id, string $project_invitation_id)
    {
        $validator = Validator::make([
            'project_id'            => $project_id,
            'project_invitation_id' => $project_invitation_id,
        ], [
            'project_id'            => ['required', 'string', 'max:255'],
            'project_invitation_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $projectInvitation = ProjectInvitation::where('id', $project_invitation_id)
            ->where('project_id', $project_id)->where('user_id', '=', Auth::id())
            ->first();

        if (!$projectInvitation) {
            return Response::generate(404, 'error', 'Project invitation not found');
        }

        return Response::generate(200, 'success', 'Project invitation retrieved', [
            'project_invitation' => $projectInvitation->toArray(),
        ]);
    }

    /**
     * Create a new project invitation.
     *
     * @param string  $project_id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_invitation_create(string $project_id, Request $request)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            [
                'project_id' => $project_id,
            ]
        ), [
            'project_id' => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
        ]);

        if (
            $project = Project::where('id', '=', $project_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $user = User::where('email', '=', $request->email)->first();

            if (
                $user &&
                $projectInvitation = ProjectInvitation::create([
                    'project_id' => $project_id,
                    'user_id'    => $user->id,
                ])
            ) {
                return Response::generate(201, 'success', 'Invitation created');
            }
        }

        return Response::generate(404, 'error', 'Invitation not found');
    }

    /**
     * Delete a project invitation.
     *
     * @param string $project_id
     * @param string $project_invitation_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_invitation_delete(string $project_id, string $project_invitation_id)
    {
        $validator = Validator::make([
            'project_id'            => $project_id,
            'project_invitation_id' => $project_invitation_id,
        ], [
            'project_id'            => ['required', 'string', 'max:255'],
            'project_invitation_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $projectInvitation = ProjectInvitation::where('id', '=', $project_invitation_id)
                ->where('project_id', '=', $project_id)
                ->where('user_id', '=', Auth::id())
                ->where(function ($query) {
                    $query->where('invitation_accepted', '=', false)
                        ->orWhereNull('invitation_accepted')
                        ->orWhereHas('project', function ($query) {
                            $query->where('user_id', '=', Auth::id());
                        });
                })
                ->first()
        ) {
            $projectInvitation->delete();

            if ($projectInvitation->project->user_id === Auth::id()) {
                return redirect()->route('project.users', ['project_id' => $project_id])->with('success', __('Invitation deleted.'));
            }

            return Response::generate(200, 'success', 'Invitation deleted', [
                'project_invitation' => $projectInvitation->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Invitation not found');
    }

    /**
     * Accept a project invitation.
     *
     * @param string $project_id
     * @param string $project_invitation_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_invitation_accept(string $project_id, string $project_invitation_id)
    {
        $validator = Validator::make([
            'project_id'            => $project_id,
            'project_invitation_id' => $project_invitation_id,
        ], [
            'project_id'            => ['required', 'string', 'max:255'],
            'project_invitation_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $projectInvitation = ProjectInvitation::where('id', '=', $project_invitation_id)
                ->where('user_id', '=', Auth::id())
                ->where('invitation_accepted', '=', false)
                ->first()
        ) {
            $projectInvitation->update([
                'invitation_accepted' => true,
            ]);

            return Response::generate(200, 'success', 'Invitation accepted', [
                'project_invitation' => $projectInvitation->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Invitation not found');
    }
}
