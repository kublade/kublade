<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class DeploymentController.
 *
 * This class is the controller for the deployment actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List the deployments.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_list()
    {
        return;
    }

    /**
     * Get the deployment.
     *
     * @param string $project_id
     * @param string $deployment_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get()
    {
        return;
    }

    /**
     * Add the deployment.
     *
     * @param string  $project_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add(string $project_id, Request $request)
    {
        return;
    }

    /**
     * Update the deployment.
     *
     * @param string  $project_id
     * @param string  $deployment_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update(string $project_id, string $deployment_id, Request $request)
    {
        return;
    }

    /**
     * Delete the deployment.
     *
     * @param string $project_id
     * @param string $deployment_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete(string $project_id, string $deployment_id)
    {
        return;
    }

    /**
     * Create the network policy.
     *
     * @param string  $project_id
     * @param string  $deployment_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_put_network_policy(string $project_id, string $deployment_id, Request $request)
    {
        return;
    }

    /**
     * Delete the network policy.
     *
     * @param string $project_id
     * @param string $deployment_id
     * @param string $network_policy_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_network_policy(string $project_id, string $deployment_id, string $network_policy_id)
    {
        return;
    }

    /**
     * Revert the commit.
     *
     * @param string $project_id
     * @param string $deployment_id
     * @param string $commit_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_revert_commit(string $project_id, string $deployment_id, string $commit_id)
    {
        return;
    }
}
