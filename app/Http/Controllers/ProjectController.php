<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_index()
    {
        return view('project.index');
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
            return redirect()->route('project.details', ['id' => $project->id])->with('success', __('Project added.'));
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
