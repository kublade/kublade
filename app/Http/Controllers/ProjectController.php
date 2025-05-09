<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Projects\Projects\Project;
use App\Models\Projects\Projects\ProjectInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
        $this->middleware('auth');
    }

    /**
     * Show the project dashboard.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_index(Request $request)
    {
        if (!$request->project_id) {
            $has_project_id = Session::has('project_id');

            if ($has_project_id) {
                Session::forget('project_id');

                return redirect()->route('project.index');
            }
        }

        return view('project.index', [
            'projects' => Project::where(function ($query) {
                $query->where('user_id', '=', Auth::id())
                    ->orWhereHas('invitations', function ($query) {
                        $query->where('user_id', '=', Auth::id())
                            ->where('invitation_accepted', '=', true);
                    });
            })->paginate(10),
        ]);
    }

    /**
     * Show the project add page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add()
    {
        return view('project.add');
    }

    /**
     * Add a new project.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add(Request $request)
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        if (
            $project = Project::create([
                'user_id' => Auth::id(),
                'name'    => $request->name,
            ])
        ) {
            return redirect()->route('project.details', ['project_id' => $project->id])->with('success', __('Project added.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the project update page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update()
    {
        return view('project.update');
    }

    /**
     * Update the project.
     *
     * @param string  $project_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update(string $project_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        if (
            $project = Project::where('id', $project_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $project->update([
                'name' => $request->name,
            ]);

            return redirect()->route('project.index')->with('success', __('Project updated.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Delete the project.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete(string $project_id)
    {
        if (
            $project = Project::where('id', $project_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $project->delete();

            return redirect()->route('project.index')->with('success', __('Project deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the project invitation page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_invitations()
    {
        return view('project.invitations', [
            'invitations' => ProjectInvitation::where('user_id', '=', Auth::id())->paginate(10),
        ]);
    }

    public function page_invitation_create(string $project_id)
    {
        if (
            $project = Project::where('id', '=', $project_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            return view('project.invitation_create', [
                'project' => $project,
            ]);
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Create a new project invitation.
     *
     * @param string  $project_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_invitation_create(string $project_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'email' => ['required', 'email', 'max:255'],
        ])->validate();

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
                return redirect()->route('project.invitations')->with('success', __('Invitation created.'));
            }
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Delete a project invitation.
     *
     * @param string $project_id
     * @param string $project_invitation_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_invitation_delete(string $project_id, string $project_invitation_id)
    {
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

            return redirect()->route('project.invitations')->with('success', __('Invitation rejected.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Accept a project invitation.
     *
     * @param string $project_id
     * @param string $project_invitation_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_invitation_accept(string $project_id, string $project_invitation_id)
    {
        if (
            $projectInvitation = ProjectInvitation::where('id', '=', $project_invitation_id)
                ->where('user_id', '=', Auth::id())
                ->where('invitation_accepted', '=', false)
                ->first()
        ) {
            $projectInvitation->update([
                'invitation_accepted' => true,
            ]);

            return redirect()->route('project.details', ['project_id' => $projectInvitation->project_id])->with('success', __('Invitation accepted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the project users page.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_users(string $project_id)
    {
        return view('project.users', [
            'invitations' => ProjectInvitation::where('project_id', '=', $project_id)->paginate(10),
        ]);
    }
}
