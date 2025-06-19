<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Kubernetes\HelmManifests;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFieldOption;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\Projects\Templates\TemplateGitCredential;
use App\Models\Projects\Templates\TemplatePort;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Class TemplateController.
 *
 * This class is the controller for the template actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateController extends Controller
{
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
            'templates' => Template::paginate(10),
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
            'name'   => ['required', 'string', 'max:255'],
            'netpol' => ['nullable', 'boolean'],
        ])->validate();

        if (
            $template = Template::create([
                'user_id' => Auth::id(),
                'name'    => $request->name,
                'netpol'  => ! empty($request->netpol),
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
            'name'   => ['required', 'string', 'max:255'],
            'netpol' => ['nullable', 'boolean'],
            'git'    => ['nullable', 'array'],
        ])->validate();

        if ($request->git) {
            Validator::make($request->git, [
                'url'         => ['required', 'string', 'max:255'],
                'branch'      => ['required', 'string', 'max:255'],
                'credentials' => ['nullable', 'string'],
                'username'    => ['required', 'string', 'max:255'],
                'email'       => ['required', 'email', 'max:255'],
                'base_path'   => ['required', 'string', 'max:255'],
            ])->validate();
        }

        if ($template = Template::where('id', $template_id)->first()) {
            $template->update([
                'name'   => $request->name,
                'netpol' => ! empty($request->netpol),
            ]);

            if ($request->git) {
                $template->gitCredentials()->updateOrCreate([
                    'template_id' => $template->id,
                ], [
                    'url'         => $request->git['url'],
                    'branch'      => $request->git['branch'],
                    'credentials' => $request->git['credentials'],
                    'username'    => $request->git['username'],
                    'email'       => $request->git['email'],
                    'base_path'   => $request->git['base_path'],
                ]);
            } else {
                $template->gitCredentials()->delete();
            }

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
            $template->gitCredentials()->delete();
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
            Deployment::where('delete', '=', false)
                ->whereNotNull('deployed_at')
                ->whereHas('template', function ($query) use ($template_id) {
                    $query->where('id', $template_id);
                })
                ->update([
                    'update'      => true,
                    'approved_at' => null,
                ]);

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

            Deployment::where('delete', '=', false)
                ->whereNotNull('deployed_at')
                ->whereHas('template', function ($query) use ($template_id) {
                    $query->where('id', $template_id);
                })
                ->update([
                    'update'      => true,
                    'approved_at' => null,
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

            Deployment::where('delete', '=', false)
                ->whereNotNull('deployed_at')
                ->whereHas('template', function ($query) use ($template_id) {
                    $query->where('id', $template_id);
                })
                ->update([
                    'update'      => true,
                    'approved_at' => null,
                ]);

            return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('File deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

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
            'template_id'   => ['required', 'string'],
            'type'          => ['required', 'string'],
            'label'         => ['required', 'string'],
            'key'           => ['required', 'string'],
            'value'         => ['nullable', 'string'],
            'advanced'      => ['nullable', 'boolean'],
            'required'      => ['nullable', 'boolean'],
            'secret'        => ['nullable', 'boolean'],
            'set_on_create' => ['nullable', 'boolean'],
            'set_on_update' => ['nullable', 'boolean'],
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
                    'template_id'   => $request->template_id,
                    'type'          => $request->type,
                    'label'         => $request->label,
                    'key'           => $request->key,
                    'value'         => $request->value,
                    'min'           => $request->min,
                    'max'           => $request->max,
                    'step'          => $request->step,
                    'advanced'      => ! empty($request->advanced),
                    'required'      => ! empty($request->required),
                    'secret'        => ! empty($request->secret),
                    'set_on_create' => ! empty($request->set_on_create),
                    'set_on_update' => ! empty($request->set_on_update),
                ]);

                break;
            default:
                TemplateField::create([
                    'template_id'   => $request->template_id,
                    'type'          => $request->type,
                    'label'         => $request->label,
                    'key'           => $request->key,
                    'value'         => $request->value,
                    'advanced'      => ! empty($request->advanced),
                    'required'      => ! empty($request->required),
                    'secret'        => ! empty($request->secret),
                    'set_on_create' => ! empty($request->set_on_create),
                    'set_on_update' => ! empty($request->set_on_update),
                ]);

                break;
        }

        return redirect()->route('template.details', ['template_id' => $template_id])->with('success', __('Field added.'));
    }

    /**
     * Show the template update field page.
     *
     * @param string $template_id
     * @param string $field_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update_field(string $template_id, string $field_id)
    {
        $field = TemplateField::where('id', $field_id)->first();

        return view('template.update-field', [
            'template' => Template::where('id', $template_id)->first(),
            'field'    => $field,
            'options'  => $field->options()->paginate(10),
        ]);
    }

    /**
     * Update the field.
     *
     * @param string  $template_id
     * @param string  $field_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update_field(string $template_id, string $field_id, Request $request)
    {
        Validator::make($request->toArray(), [
            'template_id'   => ['required', 'string'],
            'type'          => ['required', 'string'],
            'label'         => ['required', 'string'],
            'key'           => ['required', 'string'],
            'value'         => ['nullable', 'string'],
            'advanced'      => ['nullable', 'boolean'],
            'required'      => ['nullable', 'boolean'],
            'secret'        => ['nullable', 'boolean'],
            'set_on_create' => ['nullable', 'boolean'],
            'set_on_update' => ['nullable', 'boolean'],
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
                    'template_id'   => $request->template_id,
                    'type'          => $request->type,
                    'label'         => $request->label,
                    'key'           => $request->key,
                    'value'         => $request->value,
                    'min'           => $request->min,
                    'max'           => $request->max,
                    'step'          => $request->step,
                    'advanced'      => ! empty($request->advanced),
                    'required'      => ! empty($request->required),
                    'secret'        => ! empty($request->secret),
                    'set_on_create' => ! empty($request->set_on_create),
                    'set_on_update' => ! empty($request->set_on_update),
                ]);

                break;
            default:
                $field->update([
                    'template_id'   => $request->template_id,
                    'type'          => $request->type,
                    'label'         => $request->label,
                    'key'           => $request->key,
                    'value'         => $request->value,
                    'advanced'      => ! empty($request->advanced),
                    'required'      => ! empty($request->required),
                    'secret'        => ! empty($request->secret),
                    'set_on_create' => ! empty($request->set_on_create),
                    'set_on_update' => ! empty($request->set_on_update),
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

    /**
     * Show the template helm chart import page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_import()
    {
        return view('template.import');
    }

    /**
     * Import a new template.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_import(Request $request)
    {
        Validator::make($request->toArray(), [
            'name'      => ['required', 'string', 'max:255'],
            'netpol'    => ['nullable', 'boolean'],
            'url'       => ['required', 'string'],
            'chart'     => ['required', 'string'],
            'repo'      => ['string', 'nullable'],
            'namespace' => ['string', 'nullable'],
        ])->validate();

        if (
            $template = Template::create([
                'user_id' => Auth::id(),
                'name'    => $request->name,
                'netpol'  => ! empty($request->netpol),
            ])
        ) {
            try {
                foreach (HelmManifests::generateManifests($request->url, $request->chart, $request->repo, $request->namespace) as $fileName => $fileContent) {
                    TemplateFile::create([
                        'template_id'           => $template->id,
                        'template_directory_id' => null,
                        'name'                  => $fileName,
                        'mime_type'             => 'text/yaml',
                        'content'               => $fileContent,
                    ]);
                }

                return redirect()->route('template.details', ['template_id' => $template->id])->with('success', __('Template imported.'));
            } catch (Exception $e) {
                return redirect()->route('template.details', ['template_id' => $template->id])->with('warning', __('Ooops, something went wrong.'));
            }
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the template helm chart import page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_sync()
    {
        return view('template.sync');
    }

    /**
     * Sync a template.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_sync(Request $request)
    {
        $validator = Validator::make($request->toArray(), [
            'name'            => ['required', 'string', 'max:255'],
            'netpol'          => ['nullable', 'boolean'],
            'git'             => ['required', 'array'],
            'git.url'         => ['required', 'string', 'max:255'],
            'git.branch'      => ['required', 'string', 'max:255'],
            'git.credentials' => ['nullable', 'string'],
            'git.username'    => ['required', 'string', 'max:255'],
            'git.email'       => ['required', 'email', 'max:255'],
            'git.base_path'   => ['required', 'string', 'max:255'],
        ]);

        if (
            $template = Template::create([
                'user_id' => Auth::id(),
                'name'    => $request->name,
                'netpol'  => ! empty($request->netpol),
            ])
        ) {
            try {
                TemplateGitCredential::create([
                    'template_id' => $template->id,
                    'url'         => $request->git['url'],
                    'branch'      => $request->git['branch'],
                    'credentials' => $request->git['credentials'],
                    'username'    => $request->git['username'],
                    'email'       => $request->git['email'],
                    'base_path'   => $request->git['base_path'],
                ]);

                return redirect()->route('template.details', ['template_id' => $template->id])->with('success', __('Template added. Syncing...'));
            } catch (Exception $e) {
                return redirect()->route('template.details', ['template_id' => $template->id])->with('warning', __('Ooops, something went wrong.'));
            }
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }
}
