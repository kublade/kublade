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
 * @OA\Tag(
 *     name="Clusters",
 *     description="Endpoints for cluster management"
 * )
 *
 * @OA\Parameter(
 *     name="cluster_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the cluster",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ClusterController extends Controller
{
    /**
     * List clusters.
     *
     * @OA\Get(
     *     path="/api/projects/{project_id}/clusters",
     *     summary="List clusters",
     *     tags={"Clusters"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/cursor"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Clusters retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clusters retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="clusters", type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/Cluster")
     *                 ),
     *
     *                 @OA\Property(property="links", type="object",
     *                     @OA\Property(property="next", type="string"),
     *                     @OA\Property(property="prev", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
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
     * @OA\Get(
     *     path="/api/projects/{project_id}/clusters/{cluster_id}",
     *     summary="Get a cluster",
     *     tags={"Clusters"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/cluster_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cluster retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cluster retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cluster", ref="#/components/schemas/Cluster")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
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
     * @OA\Post(
     *     path="/api/projects/{project_id}/clusters",
     *     summary="Add a new cluster",
     *     tags={"Clusters"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="git", type="object",
     *                 @OA\Property(property="url", type="string"),
     *                 @OA\Property(property="branch", type="string"),
     *                 @OA\Property(property="credentials", type="string"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="base_path", type="string"),
     *             ),
     *             @OA\Property(property="k8s", type="object",
     *                 @OA\Property(property="api_url", type="string"),
     *                 @OA\Property(property="kubeconfig", type="string"),
     *                 @OA\Property(property="service_account_token", type="string"),
     *                 @OA\Property(property="node_prefix", type="string", nullable=true),
     *             ),
     *             @OA\Property(property="namespace", type="object",
     *                 @OA\Property(property="utility", type="string"),
     *                 @OA\Property(property="ingress", type="string"),
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Cluster created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cluster created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cluster", ref="#/components/schemas/Cluster")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
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

        return Response::generate(500, 'error', 'Cluster not created');
    }

    /**
     * Update the cluster.
     *
     * @OA\Patch(
     *     path="/api/projects/{project_id}/clusters/{cluster_id}",
     *     summary="Update a cluster",
     *     tags={"Clusters"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/cluster_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="git", type="object",
     *                 @OA\Property(property="url", type="string"),
     *                 @OA\Property(property="branch", type="string"),
     *                 @OA\Property(property="credentials", type="string"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="base_path", type="string"),
     *             ),
     *             @OA\Property(property="k8s", type="object",
     *                 @OA\Property(property="api_url", type="string"),
     *                 @OA\Property(property="kubeconfig", type="string"),
     *                 @OA\Property(property="service_account_token", type="string"),
     *                 @OA\Property(property="node_prefix", type="string", nullable=true),
     *             ),
     *             @OA\Property(property="namespace", type="object",
     *                 @OA\Property(property="utility", type="string"),
     *                 @OA\Property(property="ingress", type="string"),
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cluster updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cluster updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cluster", ref="#/components/schemas/Cluster")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
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
     * @OA\Delete(
     *     path="/api/projects/{project_id}/clusters/{cluster_id}",
     *     summary="Delete a cluster",
     *     tags={"Clusters"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/cluster_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cluster deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cluster deleted successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cluster", ref="#/components/schemas/Cluster")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationErrorResponse"),
     *     @OA\Response(response=401, ref="#/components/responses/UnauthorizedResponse"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerErrorResponse")
     * )
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
