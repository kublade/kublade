<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Helpers\API\Response;
use App\Http\Controllers\Controller;
use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Deployments\DeploymentLink;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class DeploymentController.
 *
 * This class is the controller for the deployment actions.
 *
 * @OA\Tag(
 *     name="Deployments",
 *     description="Endpoints for deployment management"
 * )
 *
 * @OA\Parameter(
 *     name="deployment_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the deployment",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @OA\Parameter(
 *     name="network_policy_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the network policy",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @OA\Parameter(
 *     name="commit_id",
 *     in="path",
 *     required=true,
 *     description="The ID of the commit",
 *
 *     @OA\Schema(type="string")
 * )
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentController extends Controller
{
    /**
     * List the deployments.
     *
     * @OA\Get(
     *     path="/projects/{project_id}/deployments",
     *     summary="List deployments for a project",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/cursor"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Deployments retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deployments retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deployments", type="array",
     *
     *                     @OA\Items(type="object")
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
     * @return \Illuminate\Contracts\Support\Renderable
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

        $deployments = Deployment::cursorPaginate(10);

        return Response::generate(200, 'success', 'Deployments retrieved successfully', [
            'deployments' => $deployments->items(),
            'links'       => [
                'next' => $deployments->nextCursor()?->encode(),
                'prev' => $deployments->previousCursor()?->encode(),
            ],
        ]);
    }

    /**
     * Get the deployment.
     *
     * @OA\Get(
     *     path="/projects/{project_id}/deployments/{deployment_id}",
     *     summary="Get a deployment by ID",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/deployment_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Deployment details",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deployment details"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deployment", type="object")
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
     * @param string $deployment_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_get(string $project_id, string $deployment_id)
    {
        $validator = Validator::make([
            'project_id'    => $project_id,
            'deployment_id' => $deployment_id,
        ], [
            'project_id'    => ['required', 'string', 'max:255'],
            'deployment_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $deployment = Deployment::where('id', '=', $deployment_id)->first();

        if (empty($deployment)) {
            return Response::generate(404, 'error', 'Deployment not found');
        }

        return Response::generate(200, 'success', 'Deployment retrieved', [
            'deployment' => $deployment->toArray(),
        ]);
    }

    /**
     * Add the deployment.
     *
     * @OA\Post(
     *     path="/projects/{project_id}/deployments",
     *     summary="Add a new deployment",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="template_id", type="string"),
     *             @OA\Property(property="cluster_id", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Deployment created",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deployment created"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deployment", type="object")
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
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_add(string $project_id, Request $request)
    {
        $validator = Validator::make($request->toArray(), [
            'template_id' => ['required', 'string'],
            'cluster_id'  => ['required', 'string'],
            'name'        => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        /**
         * @var Template $template
         * @var Project  $project
         */
        if (
            ! empty(
                $project = Project::where('id', '=', $project_id)
                    ->first()
            ) &&
            ! empty(
                $template = Template::where('id', '=', $request->template_id)
                    ->first()
            ) &&
            ! empty(
                $cluster = Cluster::where('id', '=', $request->cluster_id)
                    ->first()
            )
        ) {
            $validationRules = [];

            $template->fields->each(function (TemplateField $field) use ($template, &$validationRules) {
                if (! $field->set_on_create) {
                    return;
                }

                $rules = [];

                if ($field->required) {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }

                switch ($field->type) {
                    case 'input_number':
                    case 'input_range':
                        $rules[] = 'numeric';

                        if (! empty($field->min)) {
                            $rules[] = 'min:' . $field->min;
                        }

                        if (! empty($field->max)) {
                            $rules[] = 'max:' . $field->max;
                        }

                        if (! empty($field->step)) {
                            $rules[] = 'multiple_of:' . $field->step;
                        }

                        break;
                    case 'input_radio':
                    case 'input_radio_image':
                    case 'select':
                        $availableOptions = $field->options
                            ->pluck('value')
                            ->toArray();

                        if (! empty($field->value)) {
                            $availableOptions[] = $field->value;
                        }

                        $rules[] = Rule::in($availableOptions);

                        break;
                    case 'input_text':
                    case 'textarea':
                    default:
                        $rules[] = 'string';

                        break;
                }

                $validationRules['data.' . $template->id . '.' . $field->key] = $rules;
            });

            $validator = Validator::make($request->toArray(), $validationRules);

            if ($validator->fails()) {
                return Response::generate(400, 'error', 'Validation failed', $validator->errors());
            }

            /* @var Deployment $deployment */
            if (
                $deployment = Deployment::create([
                    'user_id'      => Auth::id(),
                    'project_id'   => $project->id,
                    'namespace_id' => null,
                    'cluster_id'   => $cluster->id,
                    'template_id'  => $template->id,
                    'name'         => $request->name,
                    'uuid'         => Str::uuid(),
                ])
            ) {
                $requestFields = (object) (array_key_exists($deployment->template->id, $request->data) ? $request->data[$deployment->template->id] : []);

                $template->fields->each(function (TemplateField $field) use ($requestFields, $deployment) {
                    if (! $field->set_on_create) {
                        return;
                    }

                    if ($field->type === 'input_radio' || $field->type === 'input_radio_image') {
                        $option = $field->options
                            ->where('value', '=', $requestFields->{$field->key})
                            ->first();

                        if (empty($option)) {
                            $option = $field->options
                                ->where('default', '=', true)
                                ->first();
                        }

                        if (! empty($option)) {
                            $value = $option->value;
                        }

                        if (empty($value)) {
                            $value = $requestFields->{$field->key};
                        }
                    } else {
                        $value = $requestFields->{$field->key} ?? '';
                    }

                    if ($field->secret) {
                        DeploymentSecretData::create([
                            'deployment_id'     => $deployment->id,
                            'template_field_id' => $field->id,
                            'key'               => $field->key,
                            'value'             => $value,
                        ]);
                    } else {
                        DeploymentData::create([
                            'deployment_id'     => $deployment->id,
                            'template_field_id' => $field->id,
                            'key'               => $field->key,
                            'value'             => $value,
                        ]);
                    }
                });

                return Response::generate(200, 'success', 'Deployment created', [
                    'deployment' => $deployment->toArray(),
                ]);
            }
        }

        return Response::generate(500, 'error', 'Deployment not created');
    }

    /**
     * Update the deployment.
     *
     * @OA\Patch(
     *     path="/projects/{project_id}/deployments/{deployment_id}",
     *     summary="Update a deployment",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/deployment_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Deployment updated",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deployment updated"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deployment", type="object")
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
     * @param string  $project_id
     * @param string  $deployment_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_update(string $project_id, string $deployment_id, Request $request)
    {
        $validator = Validator::make([
            'deployment_id' => $deployment_id,
            'name'          => $request->name,
        ], [
            'deployment_id' => ['required', 'string'],
            'name'          => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        /**
         * @var Deployment $deployment
         */
        if (
            ! empty(
                $deployment = Deployment::where('id', '=', $deployment_id)
                    ->first()
            )
        ) {
            $validationRules = [];

            $deployment->template->fields->each(function (TemplateField $field) use ($deployment, &$validationRules) {
                if (! $field->set_on_update) {
                    return;
                }

                $rules = [];

                if ($field->required) {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }

                switch ($field->type) {
                    case 'input_number':
                    case 'input_range':
                        $rules[] = 'numeric';

                        if (! empty($field->min)) {
                            $rules[] = 'min:' . $field->min;
                        }

                        if (! empty($field->max)) {
                            $rules[] = 'max:' . $field->max;
                        }

                        if (! empty($field->step)) {
                            $rules[] = 'multiple_of:' . $field->step;
                        }

                        break;
                    case 'input_radio':
                    case 'input_radio_image':
                    case 'select':
                        $availableOptions = $field->options
                            ->pluck('value')
                            ->toArray();

                        if (! empty($field->value)) {
                            $availableOptions[] = $field->value;
                        }

                        $rules[] = Rule::in($availableOptions);

                        break;
                    case 'input_text':
                    case 'textarea':
                    default:
                        $rules[] = 'string';

                        break;
                }

                $validationRules['data.' . $deployment->template->id . '.' . $field->key] = $rules;
            });

            $validator = Validator::make($request->toArray(), $validationRules);

            if ($validator->fails()) {
                return Response::generate(400, 'error', 'Validation failed', $validator->errors());
            }

            $requestFields = (object) (array_key_exists($deployment->template->id, $request->data) ? $request->data[$deployment->template->id] : []);

            $deployment->template->fields->each(function (TemplateField $field) use ($requestFields, $deployment) {
                if (! $field->set_on_update) {
                    return;
                }

                if ($field->type === 'input_radio' || $field->type === 'input_radio_image') {
                    $option = $field->options
                        ->where('value', '=', $requestFields->{$field->key})
                        ->first();

                    if (empty($option)) {
                        $option = $field->options
                            ->where('default', '=', true)
                            ->first();
                    }

                    if (! empty($option)) {
                        $value = $option->value;
                    }

                    if (empty($value)) {
                        $value = $requestFields->{$field->key};
                    }
                } else {
                    $value = $requestFields->{$field->key} ?? '';
                }

                if ($field->secret) {
                    $deployment->deploymentSecretData->where('template_field_id', '=', $field->id)->each(function (DeploymentSecretData $deploymentSecretData) use ($value) {
                        $deploymentSecretData->update([
                            'value' => $value,
                        ]);
                    });
                } else {
                    $deployment->deploymentData->where('template_field_id', '=', $field->id)->each(function (DeploymentData $deploymentData) use ($value) {
                        $deploymentData->update([
                            'value' => $value,
                        ]);
                    });
                }
            });

            $deployment->update([
                'name' => $request->name,
                ...($deployment->deployed_at ? ['update' => true] : []),
            ]);

            return Response::generate(200, 'success', 'Deployment updated', [
                'deployment' => $deployment->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Deployment not found');
    }

    /**
     * Delete the deployment.
     *
     * @OA\Delete(
     *     path="/projects/{project_id}/deployments/{deployment_id}",
     *     summary="Delete a deployment",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/deployment_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Deployment deleted",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deployment deleted"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deployment", type="object")
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
     * @param string $deployment_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete(string $project_id, string $deployment_id)
    {
        $validator = Validator::make([
            'project_id'    => $project_id,
            'deployment_id' => $deployment_id,
        ], [
            'project_id'    => ['required', 'string', 'max:255'],
            'deployment_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        /**
         * @var Deployment $deployment
         */
        if (
            ! empty(
                $deployment = Deployment::where('id', '=', $deployment_id)
                    ->first()
            )
        ) {
            $deployment->update([
                'delete' => true,
            ]);

            return Response::generate(200, 'success', 'Deployment deleted', [
                'deployment' => $deployment->toArray(),
            ]);
        }

        return Response::generate(404, 'error', 'Deployment not found');
    }

    /**
     * Create the network policy.
     *
     * @OA\Put(
     *     path="/projects/{project_id}/deployments/{deployment_id}/network-policy",
     *     summary="Create a network policy",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/deployment_id"),
     *     @OA\Parameter(ref="#/components/parameters/network_policy_id"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="source_deployment_id", type="string"),
     *             @OA\Property(property="target_deployment_id", type="string"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Network policy created",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Network policy put"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="network_policy", type="object")
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
     * @param string  $project_id
     * @param string  $deployment_id
     * @param string  $network_policy_id
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_put_network_policy(string $project_id, string $deployment_id, string $network_policy_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), [
            'project_id'        => $project_id,
            'deployment_id'     => $deployment_id,
            'network_policy_id' => $network_policy_id,
        ]), [
            'project_id'           => ['required', 'string', 'max:255'],
            'deployment_id'        => ['required', 'string', 'max:255'],
            'source_deployment_id' => ['required', 'string', 'max:255'],
            'target_deployment_id' => ['required', 'string', 'max:255'],
            'network_policy_id'    => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        if (
            DeploymentLink::where('source_deployment_id', '=', $request->source_deployment_id)
                ->where('target_deployment_id', '=', $request->target_deployment_id)
                ->exists()
        ) {
            return Response::generate(400, 'error', 'Network policy already exists');
        }

        $sourceDeployment = Deployment::where('id', '=', $request->source_deployment_id)->first();

        if (empty($sourceDeployment)) {
            return Response::generate(404, 'error', 'Source deployment not found');
        }

        $targetDeployment = Deployment::where('id', '=', $request->target_deployment_id)->first();

        if (empty($targetDeployment) || $sourceDeployment->id === $targetDeployment->id) {
            return Response::generate(404, 'error', 'Target deployment not found');
        }

        if ($request->network_policy_id) {
            $networkPolicy = DeploymentLink::where('id', '=', $request->network_policy_id)->first();

            if (empty($networkPolicy)) {
                return Response::generate(404, 'error', 'Network policy not found');
            }

            $networkPolicy->update([
                'source_deployment_id' => $sourceDeployment->id,
                'target_deployment_id' => $targetDeployment->id,
            ]);
        } else {
            DeploymentLink::create([
                'source_deployment_id' => $sourceDeployment->id,
                'target_deployment_id' => $targetDeployment->id,
            ]);
        }

        if (
            ! empty($targetDeployment->deployed_at) &&
            ! $targetDeployment->delete
        ) {
            $targetDeployment->update([
                'update' => true,
            ]);
        }

        return Response::generate(200, 'success', 'Network policy put', [
            'network_policy' => $networkPolicy->toArray(),
        ]);
    }

    /**
     * Delete the network policy.
     *
     * @OA\Delete(
     *     path="/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}",
     *     summary="Delete a network policy",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/deployment_id"),
     *     @OA\Parameter(ref="#/components/parameters/network_policy_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Network policy deleted",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Network policy deleted"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="network_policy", type="object")
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
     * @param string $deployment_id
     * @param string $network_policy_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_delete_network_policy(string $project_id, string $deployment_id, string $network_policy_id)
    {
        $validator = Validator::make([
            'project_id'        => $project_id,
            'deployment_id'     => $deployment_id,
            'network_policy_id' => $network_policy_id,
        ], [
            'project_id'        => ['required', 'string', 'max:255'],
            'deployment_id'     => ['required', 'string', 'max:255'],
            'network_policy_id' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $networkPolicy = DeploymentLink::where('id', '=', $network_policy_id)->first();

        if (empty($networkPolicy)) {
            return Response::generate(404, 'error', 'Network policy not found');
        }

        $networkPolicy->delete();

        return Response::generate(200, 'success', 'Network policy deleted', [
            'network_policy' => $networkPolicy->toArray(),
        ]);
    }

    /**
     * Revert the commit.
     *
     * @OA\Patch(
     *     path="/projects/{project_id}/deployments/{deployment_id}/commit/{commit_id}",
     *     summary="Revert a commit",
     *     tags={"Deployments"},
     *
     *     @OA\Parameter(ref="#/components/parameters/project_id"),
     *     @OA\Parameter(ref="#/components/parameters/deployment_id"),
     *     @OA\Parameter(ref="#/components/parameters/commit_id"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Commit reverted",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Commit reverted"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="commit", type="object")
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
     * @param string $deployment_id
     * @param string $commit_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function action_revert_commit(string $project_id, string $deployment_id, string $commit_id)
    {
        $validator = Validator::make([
            'project_id'    => $project_id,
            'deployment_id' => $deployment_id,
            'commit_id'     => $commit_id,
        ], [
            'project_id'    => ['required', 'string', 'max:255'],
            'deployment_id' => ['required', 'string', 'max:255'],
            'commit_id'     => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Response::generate(400, 'error', 'Validation failed', $validator->errors());
        }

        $commit = DeploymentCommit::where('id', '=', $commit_id)->first();

        if (
            empty($commit) ||
            $commit->deployment->delete
        ) {
            return Response::generate(404, 'error', 'Commit not found');
        }

        $commit->diff->each(function ($diff) use ($commit) {
            if ($diff['type'] === 'plain') {
                $commit->deployment->deploymentData->where('key', $diff['key'])->each(function (DeploymentData $deploymentData) use ($diff) {
                    $deploymentData->update([
                        'value' => $diff['previous'],
                    ]);
                });
            } else {
                $commit->deployment->deploymentSecretData->where('key', $diff['key'])->each(function (DeploymentSecretData $secretData) use ($diff) {
                    $secretData->update([
                        'value' => $diff['previous'],
                    ]);
                });
            }

            if (!empty($commit->deployment->deployed_at)) {
                $commit->deployment->update([
                    'update' => true,
                ]);
            }
        });

        return Response::generate(200, 'success', 'Commit reverted', [
            'commit' => $commit->toArray(),
        ]);
    }
}
