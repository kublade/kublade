<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateFile;
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
     * Show the template dashboard.
     *
     * @param string $template_id
     * @param string $file_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_index(string $template_id = null, string $file_id = null)
    {
        return view('template.index', [
            'templates' => Template::all(),
            'template'  => $template_id ? Template::where('id', $template_id)->first() : null,
            'file'      => $file_id ? TemplateFile::where('id', $file_id)->first() : null,
        ]);
    }

    /**
     * Show the template add page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add()
    {
        return view('template.add');
    }

    /**
     * Add a new template.
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
            $template = Template::create([
                'user_id' => Auth::id(),
                'name'    => $request->name,
            ])
        ) {
            return redirect()->route('template.details', ['template_id' => $template->id])->with('success', __('Template added.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template update page.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update(string $template_id)
    {
        if (
            $template = Template::where('id', $template_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            return view('template.update', ['template' => $template]);
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Update the template.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update(string $template_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        if (
            $template = Template::where('id', $template_id)
                ->where('user_id', '=', Auth::id())
                ->first()
        ) {
            $template->update([
                'name' => $request->name,
            ]);

            return redirect()->route('template.index')->with('success', __('Template updated.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Delete the template.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete(string $template_id)
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
