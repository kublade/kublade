<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Projects\Projects\Project;
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
            'projects' => Project::paginate(10),
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
}
