<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Projects\Templates\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
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
        return view('template.index');
    }

    /**
     * Show the project add page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add()
    {
        return view('template.add');
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
            'name'           => ['required', 'string', 'max:255'],
            'reserved_ports' => ['required', 'numeric', 'min:0', 'max:65535'],
        ])->validate();

        if (
            $template = Template::create([
                'user_id'        => Auth::id(),
                'name'           => $request->name,
                'reserved_ports' => $request->reserved_ports,
            ])
        ) {
            return redirect()->route('template.details', ['template_id' => $template->id])->with('success', __('Template added.'));
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
        return view('template.update');
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
            'name'           => ['required', 'string', 'max:255'],
            'reserved_ports' => ['required', 'numeric', 'min:0', 'max:65535'],
        ])->validate();

        if (
            $template = Template::where('id', $template_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $template->update([
                'name'           => $request->name,
                'reserved_ports' => $request->reserved_ports,
            ]);

            return redirect()->route('template.index')->with('success', __('Template updated.'));
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
            $template = Template::where('id', $template_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $template->delete();

            return redirect()->route('template.index')->with('success', __('Template deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }
}
