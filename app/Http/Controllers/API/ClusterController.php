<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class ClusterController.
 *
 * This class is the controller for the cluster actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ClusterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('api.guard');
    }

    /**
     * List clusters.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list()
    {
        return;
    }

    /**
     * Get a cluster.
     *
     * @param string $cluster_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get(string $cluster_id)
    {
        return;
    }

    /**
     * Add a new cluster.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_add(Request $request)
    {
        return;
    }

    /**
     * Update the cluster.
     *
     * @param string  $project_id
     * @param string  $cluster_id
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_update(string $project_id, string $cluster_id, Request $request)
    {
        return;
    }

    /**
     * Delete the cluster.
     *
     * @param string $project_id
     * @param string $cluster_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_delete(string $project_id, string $cluster_id)
    {
        return;
    }
}
