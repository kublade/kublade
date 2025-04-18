<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFieldOption;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\Projects\Templates\TemplatePort;
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
        if ($template = Template::where('id', $template_id)->first()) {
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

        if ($template = Template::where('id', $template_id)->first()) {
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
        if ($template = Template::where('id', $template_id)->first()) {
            $template->delete();

            return redirect()->route('template.index')->with('success', __('Template deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template add folder page.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add_folder(string $template_id, Request $request)
    {
        return view('template.add-folder', [
            'template' => Template::where('id', $template_id)->first(),
            'folder'   => $request->folder_id ? TemplateDirectory::where('id', $request->folder_id)->first() : null,
        ]);
    }

    /**
     * Add a new folder.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add_folder(string $template_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string', 'max:255'],
        ])->validate();

        if (
            $folder = TemplateDirectory::create([
                'template_id' => $template_id,
                'parent_id'   => $request->parent_id,
                'name'        => $request->name,
            ])
        ) {
            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Folder added.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template update folder page.
     *
     * @param string $template_id
     * @param string $folder_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update_folder(string $template_id, string $folder_id)
    {
        return view('template.update-folder', [
            'template' => Template::where('id', $template_id)->first(),
            'folder'   => TemplateDirectory::where('id', $folder_id)->first(),
        ]);
    }

    /**
     * Update the folder.
     *
     * @param string  $template_id
     * @param string  $folder_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update_folder(string $template_id, string $folder_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string', 'max:255'],
        ])->validate();

        if (
            $folder = TemplateDirectory::where('id', $folder_id)
                ->where('template_id', '=', $template_id)
                ->first()
        ) {
            $folder->update([
                'name'      => $request->name,
                'parent_id' => $request->parent_id,
            ]);

            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Folder updated.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Delete the folder.
     *
     * @param string $template_id
     * @param string $folder_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_folder(string $template_id, string $folder_id)
    {
        if (
            $folder = TemplateDirectory::where('id', $folder_id)
                ->where('template_id', '=', $template_id)
                ->first()
        ) {
            $folder->delete();

            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Folder deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template add file page.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add_file(string $template_id, Request $request)
    {
        return view('template.add-file', [
            'template' => Template::where('id', $template_id)->first(),
            'folder'   => $request->folder_id ? TemplateDirectory::where('id', $request->folder_id)->first() : null,
        ]);
    }

    /**
     * Add a new file.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add_file(string $template_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name'                  => ['required', 'string', 'max:255'],
            'template_directory_id' => ['nullable', 'string', 'max:255'],
            'mime_type'             => ['required', 'string', 'max:255'],
        ])->validate();

        if (
            $file = TemplateFile::create([
                'template_id'           => $template_id,
                'template_directory_id' => $request->template_directory_id,
                'name'                  => $request->name,
                'mime_type'             => $request->mime_type,
                'content'               => '',
            ])
        ) {
            return redirect()->route('template.details_file', ['template_id' => $template_id, 'file_id' => $file->id])->with('success', __('File added.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template update file page.
     *
     * @param string $template_id
     * @param string $file_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update_file(string $template_id, string $file_id)
    {
        return view('template.update-file', [
            'template' => Template::where('id', $template_id)->first(),
            'file'     => TemplateFile::where('id', $file_id)->first(),
        ]);
    }

    /**
     * Update the file.
     *
     * @param string  $template_id
     * @param string  $file_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update_file(string $template_id, string $file_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'name'                  => ['required', 'string', 'max:255'],
            'template_directory_id' => ['nullable', 'string', 'max:255'],
            'mime_type'             => ['required', 'string', 'max:255'],
            'content'               => ['nullable', 'string'],
        ])->validate();

        if (
            $file = TemplateFile::where('id', $file_id)
                ->where('template_id', '=', $template_id)
                ->first()
        ) {
            $file->update([
                'name'                  => $request->name,
                'template_directory_id' => $request->template_directory_id,
                'mime_type'             => $request->mime_type,
                ...($request->content ? ['content' => $request->content] : []),
            ]);

            return redirect()->route('template.details_file', ['template_id' => $template_id, 'file_id' => $file->id])->with('success', __('File updated.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Delete the file.
     *
     * @param string $template_id
     * @param string $file_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_file(string $template_id, string $file_id)
    {
        if (
            $file = TemplateFile::where('id', $file_id)
                ->where('template_id', '=', $template_id)
                ->first()
        ) {
            $file->delete();

            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('File deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    // TODO: Add field methods

    /**
     * Show the template add field page.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add_field(string $template_id)
    {
        return view('template.add-field', [
            'template' => Template::where('id', $template_id)->first(),
        ]);
    }

    /**
     * Add a new field.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add_field(string $template_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'template_id' => ['required', 'string'],
            'type'        => ['required', 'string'],
            'label'       => ['required', 'string'],
            'key'         => ['required', 'string'],
            'value'       => ['nullable', 'string'],
            'required'    => ['nullable', 'boolean'],
            'secret'      => ['nullable', 'boolean'],
        ])->validate();

        switch ($request->type) {
            case 'input_number':
            case 'input_range':
                Validator::make($request->toArray(), [
                    'min'  => ['required', 'numeric'],
                    'max'  => ['required', 'numeric'],
                    'step' => ['required', 'numeric'],
                ])->validate();

                TemplateField::create([
                    'template_id' => $request->template_id,
                    'type'        => $request->type,
                    'label'       => $request->label,
                    'key'         => $request->key,
                    'value'       => $request->value,
                    'min'         => $request->min,
                    'max'         => $request->max,
                    'step'        => $request->step,
                    'required'    => ! empty($request->required),
                    'secret'      => ! empty($request->secret),
                ]);

                break;
            default:
                TemplateField::create([
                    'template_id' => $request->template_id,
                    'type'        => $request->type,
                    'label'       => $request->label,
                    'key'         => $request->key,
                    'value'       => $request->value,
                    'required'    => ! empty($request->required),
                    'secret'      => ! empty($request->secret),
                ]);

                break;
        }

        return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Field added.'));
    }

    public function page_update_field(string $template_id, string $field_id)
    {
        return view('template.update-field', [
            'template' => Template::where('id', $template_id)->first(),
            'field'    => TemplateField::where('id', $field_id)->first(),
        ]);
    }

    public function action_update_field(string $template_id, string $field_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'template_id' => ['required', 'string'],
            'type'        => ['required', 'string'],
            'label'       => ['required', 'string'],
            'key'         => ['required', 'string'],
            'value'       => ['nullable', 'string'],
            'required'    => ['nullable', 'boolean'],
            'secret'      => ['nullable', 'boolean'],
        ])->validate();

        $field = TemplateField::where('id', $field_id)
            ->where('template_id', '=', $template_id)
            ->first();

        if (empty($field)) {
            return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
        }

        switch ($request->type) {
            case 'input_number':
            case 'input_range':
                Validator::make($request->toArray(), [
                    'min'  => ['required', 'numeric'],
                    'max'  => ['required', 'numeric'],
                    'step' => ['required', 'numeric'],
                ])->validate();

                $field->update([
                    'template_id' => $request->template_id,
                    'type'        => $request->type,
                    'label'       => $request->label,
                    'key'         => $request->key,
                    'value'       => $request->value,
                    'min'         => $request->min,
                    'max'         => $request->max,
                    'step'        => $request->step,
                    'required'    => ! empty($request->required),
                    'secret'      => ! empty($request->secret),
                ]);

                break;
            default:
                $field->update([
                    'template_id' => $request->template_id,
                    'type'        => $request->type,
                    'label'       => $request->label,
                    'key'         => $request->key,
                    'value'       => $request->value,
                    'required'    => ! empty($request->required),
                    'secret'      => ! empty($request->secret),
                ]);

                break;
        }

        return redirect()->route('template.field.update', ['template_id' => $template_id, 'field_id' => $field_id])->with('success', __('Field updated.'));
    }

    /**
     * Delete the field.
     *
     * @param string $template_id
     * @param string $field_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_field(string $template_id, string $field_id)
    {
        if (
            $field = TemplateField::where('id', $field_id)
                ->where('template_id', '=', $template_id)
                ->first()
        ) {
            $field->delete();

            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Field deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template add option page.
     *
     * @param string $template_id
     * @param string $field_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add_option(string $template_id, string $field_id)
    {
        return view('template.add-option', [
            'template' => Template::where('id', $template_id)->first(),
            'field'    => TemplateField::where('id', $field_id)->first(),
        ]);
    }

    /**
     * Add a new option.
     *
     * @param string  $template_id
     * @param string  $field_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add_option(string $template_id, string $field_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'template_field_id' => ['required', 'string'],
            'label'             => ['required', 'string'],
            'value'             => ['required', 'string'],
            'default'           => ['nullable', 'boolean'],
        ])->validate();

        if ($request->default) {
            TemplateFieldOption::where('template_field_id', '=', $request->template_field_id)->update([
                'default' => false,
            ]);
        }

        TemplateFieldOption::create([
            'template_field_id' => $request->template_field_id,
            'label'             => $request->label,
            'value'             => $request->value,
            'default'           => ! empty($request->default),
        ]);

        return redirect()->route('template.field.update', ['template_id' => $template_id, 'field_id' => $field_id])->with('success', __('Option added.'));
    }

    /**
     * Show the template update option page.
     *
     * @param string $template_id
     * @param string $field_id
     * @param string $option_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update_option(string $template_id, string $field_id, string $option_id)
    {
        return view('template.update-option', [
            'template' => Template::where('id', $template_id)->first(),
            'field'    => TemplateField::where('id', $field_id)->first(),
            'option'   => TemplateFieldOption::where('id', $option_id)->first(),
        ]);
    }

    /**
     * Update the option.
     *
     * @param string  $template_id
     * @param string  $field_id
     * @param string  $option_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update_option(string $template_id, string $field_id, string $option_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'option_id' => ['required', 'string'],
            'label'     => ['required', 'string'],
            'value'     => ['required', 'string'],
            'default'   => ['nullable', 'boolean'],
        ])->validate();

        $option = TemplateFieldOption::where('id', $request->option_id)->first();

        if ($request->default) {
            TemplateFieldOption::where('template_field_id', '=', $request->template_field_id)->update([
                'default' => false,
            ]);
        }

        if (empty($option)) {
            return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
        }

        $option->update([
            'label'   => $request->label,
            'value'   => $request->value,
            'default' => ! empty($request->default),
        ]);

        return redirect()->route('template.field.update', ['template_id' => $template_id, 'field_id' => $field_id])->with('success', __('Option updated.'));
    }

    /**
     * Delete the option.
     *
     * @param string $template_id
     * @param string $field_id
     * @param string $option_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_option(string $template_id, string $field_id, string $option_id)
    {
        if (
            $option = TemplateFieldOption::where('id', $option_id)
                ->where('template_field_id', '=', $field_id)
                ->first()
        ) {
            $option->delete();

            return redirect()->route('template.field.update', ['template_id' => $template_id, 'field_id' => $field_id])->with('success', __('Option deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template add port page.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add_port(string $template_id)
    {
        return view('template.add-port', [
            'template' => Template::where('id', $template_id)->first(),
        ]);
    }

    /**
     * Add a new port.
     *
     * @param string  $template_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add_port(string $template_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'template_id'    => ['required', 'string'],
            'group'          => ['required', 'string'],
            'claim'          => ['nullable', 'string'],
            'preferred_port' => ['nullable', 'numeric'],
            'random'         => ['nullable', 'boolean'],
        ])->validate();

        TemplatePort::create([
            'template_id'    => $request->template_id,
            'group'          => $request->group,
            'claim'          => $request->claim,
            'preferred_port' => $request->preferred_port,
            'random'         => $request->random,
        ]);

        return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Port added.'));
    }

    /**
     * Show the template update port page.
     *
     * @param string $template_id
     * @param string $port_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update_port(string $template_id, string $port_id)
    {
        return view('template.update-port', [
            'template' => Template::where('id', $template_id)->first(),
            'port'     => TemplatePort::where('id', $port_id)->first(),
        ]);
    }

    /**
     * Update the port.
     *
     * @param string  $template_id
     * @param string  $port_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update_port(string $template_id, string $port_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'template_id'    => ['required', 'string'],
            'group'          => ['required', 'string'],
            'claim'          => ['nullable', 'string'],
            'preferred_port' => ['nullable', 'numeric'],
            'random'         => ['nullable', 'boolean'],
        ])->validate();

        $port = TemplatePort::where('id', $port_id)->first();

        if (empty($port)) {
            return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
        }

        $port->update([
            'template_id'    => $request->template_id,
            'group'          => $request->group,
            'claim'          => $request->claim,
            'preferred_port' => $request->preferred_port,
            'random'         => ! empty($request->random),
        ]);

        return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Port updated.'));
    }

    /**
     * Delete the port.
     *
     * @param string $template_id
     * @param string $port_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_port(string $template_id, string $port_id)
    {
        if (
            $port = TemplatePort::where('id', $port_id)->first()
        ) {
            $port->delete();

            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Port deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }
}
