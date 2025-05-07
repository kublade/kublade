<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list()
    {
        return;
    }

    /**
     * Get the project.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get(string $project_id)
    {
        return;
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
        return;
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
        return;
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
        return;
    }

    /**
     * List the project invitations.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list_invitation(string $project_id)
    {
        return;
    }

    /**
     * Get the project invitation.
     *
     * @param string $project_id
     * @param string $project_invitation_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get_invitation(string $project_id, string $project_invitation_id)
    {
        return;
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
        return;
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
        return;
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
        return;
    }
}
