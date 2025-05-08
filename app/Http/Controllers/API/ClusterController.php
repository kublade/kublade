<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Kubernetes\Clusters\GitCredential;
use App\Models\Kubernetes\Clusters\K8sCredential;
use App\Models\Kubernetes\Clusters\Ns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
     * @param string $project_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_list(string $project_id)
    {
        $validator = Validator::make([
            'project_id' => $project_id,
        ], [
            'project_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $clusters = Cluster::where('project_id', $project_id)->cursorPaginate(10);

        return Response::generate(200, 'success', 'Clusters retrieved successfully', [
            'clusters' => collect($clusters->items())->map(function ($item) {
                return $item->toArray();
            }),
            'links' => [
                'next' => $clusters->nextCursor()?->encode(),
                'prev' => $clusters->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get a cluster.
     *
     * @param string $project_id
     * @param string $cluster_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_get(string $project_id, string $cluster_id)
    {
        $validator = Validator::make([
            'cluster_id' => $cluster_id,
        ], [
            'cluster_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $cluster = Cluster::where('id', $cluster_id)->first();

        if (!$cluster) {
            return Response::generate(404, 'error', 'Cluster not found');
        }

        return Response::generate(200, 'success', 'Cluster retrieved successfully', [
            'cluster' => $cluster->toArray(),
        ]);
    }

    /**
     * Add a new cluster.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                      => ['required', 'string', 'max:255'],
            'git'                       => ['required', 'array'],
            'git.url'                   => ['required', 'string', 'max:255'],
            'git.branch'                => ['required', 'string', 'max:255'],
            'git.credentials'           => ['required', 'string'],
            'git.username'              => ['required', 'string', 'max:255'],
            'git.email'                 => ['required', 'email', 'max:255'],
            'git.base_path'             => ['required', 'string', 'max:255'],
            'k8s'                       => ['required', 'array'],
            'k8s.api_url'               => ['required', 'string', 'max:255'],
            'k8s.kubeconfig'            => ['required', 'string'],
            'k8s.service_account_token' => ['required', 'string'],
            'k8s.node_prefix'           => ['nullable', 'string', 'max:255'],
            'namespace'                 => ['required', 'array'],
            'namespace.utility'         => ['required', 'string', 'max:255'],
            'namespace.ingress'         => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            $cluster = Cluster::create([
                'project_id' => $request->project_id,
                'user_id'    => Auth::user()->id,
                'name'       => $request->name,
            ])
        ) {
            GitCredential::create([
                'cluster_id'  => $cluster->id,
                'url'         => $request->git['url'],
                'branch'      => $request->git['branch'],
                'credentials' => $request->git['credentials'],
                'username'    => $request->git['username'],
                'email'       => $request->git['email'],
                'base_path'   => $request->git['base_path'],
            ]);

            K8sCredential::create([
                'cluster_id'            => $cluster->id,
                'api_url'               => $request->k8s['api_url'],
                'kubeconfig'            => $request->k8s['kubeconfig'],
                'service_account_token' => $request->k8s['service_account_token'],
                'node_prefix'           => $request->k8s['node_prefix'],
            ]);

            Ns::create([
                'cluster_id' => $cluster->id,
                'name'       => $request->namespace['utility'],
                'type'       => Ns::TYPE_UTILITY,
            ]);

            Ns::create([
                'cluster_id' => $cluster->id,
                'name'       => $request->namespace['ingress'],
                'type'       => Ns::TYPE_INGRESS,
            ]);

            return Response::generate(201, 'success', 'Cluster created successfully', [
                'cluster' => $cluster->toArray(),
            ]);
        }

        return Response::generate(500, 'error', 'Action failed');
    }

    /**
     * Update the cluster.
     *
     * @param string  $project_id
     * @param string  $cluster_id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_update(string $project_id, string $cluster_id, Request $request)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            [
                'cluster_id' => $cluster_id,
            ]
        ), [
            'cluster_id'                => ['required', 'string', 'max:255'],
            'name'                      => ['required', 'string', 'max:255'],
            'git'                       => ['required', 'array'],
            'git.url'                   => ['required', 'string', 'max:255'],
            'git.branch'                => ['required', 'string', 'max:255'],
            'git.credentials'           => ['required', 'string'],
            'git.username'              => ['required', 'string', 'max:255'],
            'git.email'                 => ['required', 'email', 'max:255'],
            'git.base_path'             => ['required', 'string', 'max:255'],
            'k8s'                       => ['required', 'array'],
            'k8s.api_url'               => ['required', 'string', 'max:255'],
            'k8s.kubeconfig'            => ['required', 'string'],
            'k8s.service_account_token' => ['required', 'string'],
            'k8s.node_prefix'           => ['nullable', 'string', 'max:255'],
            'namespace'                 => ['required', 'array'],
            'namespace.utility'         => ['required', 'string', 'max:255'],
            'namespace.ingress'         => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($cluster = Cluster::where('id', $cluster_id)->first()) {
            $cluster->update([
                'name' => $request->name,
            ]);

            if ($cluster->gitCredentials) {
                $cluster->gitCredentials->update([
                    'url'         => $request->git['url'],
                    'branch'      => $request->git['branch'],
                    'credentials' => $request->git['credentials'],
                    'username'    => $request->git['username'],
                    'email'       => $request->git['email'],
                    'base_path'   => $request->git['base_path'],
                ]);
            } else {
                GitCredential::create([
                    'cluster_id'  => $cluster->id,
                    'url'         => $request->git['url'],
                    'branch'      => $request->git['branch'],
                    'credentials' => $request->git['credentials'],
                    'username'    => $request->git['username'],
                    'email'       => $request->git['email'],
                    'base_path'   => $request->git['base_path'],
                ]);
            }

            if ($cluster->k8sCredentials) {
                $cluster->k8sCredentials->update([
                    'api_url'               => $request->k8s['api_url'],
                    'kubeconfig'            => $request->k8s['kubeconfig'],
                    'service_account_token' => $request->k8s['service_account_token'],
                    'node_prefix'           => $request->k8s['node_prefix'],
                ]);
            } else {
                K8sCredential::create([
                    'cluster_id'            => $cluster->id,
                    'api_url'               => $request->k8s['api_url'],
                    'kubeconfig'            => $request->k8s['kubeconfig'],
                    'service_account_token' => $request->k8s['service_account_token'],
                    'node_prefix'           => $request->k8s['node_prefix'],
                ]);
            }

            if ($cluster->utilityNamespace) {
                $cluster->utilityNamespace->update([
                    'name' => $request->namespace['utility'],
                ]);
            } else {
                Ns::create([
                    'cluster_id' => $cluster->id,
                    'name'       => $request->namespace['utility'],
                    'type'       => Ns::TYPE_UTILITY,
                ]);
            }

            if ($cluster->ingressNamespace) {
                $cluster->ingressNamespace->update([
                    'name' => $request->namespace['ingress'],
                ]);
            } else {
                Ns::create([
                    'cluster_id' => $cluster->id,
                    'name'       => $request->namespace['ingress'],
                    'type'       => Ns::TYPE_INGRESS,
                ]);
            }

            return Response::generate(200, 'success', 'Cluster updated successfully', [
                'cluster' => $cluster->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Cluster not found');
    }

    /**
     * Delete the cluster.
     *
     * @param string $project_id
     * @param string $cluster_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function action_delete(string $project_id, string $cluster_id)
    {
        $validator = Validator::make([
            'cluster_id' => $cluster_id,
        ], [
            'cluster_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if ($cluster = Cluster::where('id', $cluster_id)->first()) {
            $cluster->delete();

            return Response::generate(200, 'success', 'Cluster deleted successfully', [
                'cluster' => $cluster->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Cluster not found');
    }
}
