<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List the templates.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list()
    {
        return;
    }

    /**
     * Get the template.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get(string $template_id)
    {
        return;
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
        return;
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
        return;
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
        return;
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
        return;
    }

    /**
     * List the folders.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list_folder(string $template_id)
    {
        return;
    }

    /**
     * Get the folder.
     *
     * @param string $template_id
     * @param string $folder_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get_folder(string $template_id, string $folder_id)
    {
        return;
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
        return;
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
        return;
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
        return;
    }

    /**
     * List the files.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list_file(string $template_id)
    {
        return;
    }

    /**
     * Get the file.
     *
     * @param string $template_id
     * @param string $file_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get_file(string $template_id, string $file_id)
    {
        return;
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
        return;
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
        return;
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
        return;
    }

    /**
     * List the fields.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list_field(string $template_id)
    {
        return;
    }

    /**
     * Get the field.
     *
     * @param string $template_id
     * @param string $field_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get_field(string $template_id, string $field_id)
    {
        return;
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
        return;
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
        return;
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
        return;
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
        return;
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
        return;
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
        return;
    }

    /**
     * List the ports.
     *
     * @param string $template_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list_port(string $template_id)
    {
        return;
    }

    /**
     * Get the port.
     *
     * @param string $template_id
     * @param string $port_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get_port(string $template_id, string $port_id)
    {
        return;
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
        return;
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
        return;
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
        return;
    }
}
