<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DeploymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the deployment index page.
     *
     * @param string $project_id
     * @param string $deployment_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_index(string $project_id, string $deployment_id = null)
    {
        return view('deployment.index', [
            'deployments' => Deployment::all(),
            'deployment'  => $deployment_id ? Deployment::find($deployment_id) : null,
        ]);
    }

    /**
     * Show the deployment add page.
     *
     * @param string $project_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_add(string $project_id)
    {
        return view('deployment.add', [
            'templates' => Template::all(),
        ]);
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
        Validator::make($request->toArray(), [
            'template_id' => ['required', 'string'],
            'name'        => ['required', 'string'],
        ])->validate();

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

            Validator::make($request->toArray(), $validationRules)->validate();

            /* @var Deployment $deployment */
            if (
                $deployment = Deployment::create([
                    'user_id'      => Auth::id(),
                    'project_id'   => $project->id,
                    'namespace_id' => null,
                    'template_id'  => $template->id,
                    'name'         => $request->name,
                    'uuid'         => Str::uuid(),
                ])
            ) {
                $requestFields = (object) $request->data[$deployment->template->id];

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
                        $value = $requestFields->{$field->key};
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

                return redirect()->route('deployment.index', ['project_id' => $project_id, 'deployment_id' => $deployment->id])->with('success', __('Deployment created.'));
            }
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }

    /**
     * Show the deployment update page.
     *
     * @param string $project_id
     * @param string $deployment_id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function page_update(string $project_id, string $deployment_id)
    {
        return view('deployment.update', [
            'deployment' => Deployment::find($deployment_id),
        ]);
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
        Validator::make([
            'deployment_id' => $deployment_id,
            'name'          => $request->name,
        ], [
            'deployment_id' => ['required', 'string'],
            'name'          => ['required', 'string'],
        ])->validate();

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

            Validator::make($request->toArray(), $validationRules)->validate();

            $requestFields = (object) $request->data[$deployment->template->id];

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
                    $value = $requestFields->{$field->key};
                }

                if ($field->secret) {
                    $deployment->deploymentSecretData()->where('template_field_id', '=', $field->id)->update([
                        'value' => $value,
                    ]);
                } else {
                    $deployment->deploymentData()->where('template_field_id', '=', $field->id)->update([
                        'value' => $value,
                    ]);
                }
            });

            $deployment->update([
                'name'   => $request->name,
                'update' => true,
            ]);

            return redirect()->route('deployment.index', ['project_id' => $project_id, 'deployment_id' => $deployment->id])->with('success', __('Deployment updated.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
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
        Validator::make([
            'deployment_id' => $deployment_id,
        ], [
            'deployment_id' => ['required', 'string'],
        ])->validate();

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

            return redirect()->route('deployment.index', ['project_id' => $project_id])->with('success', __('Deployment deleted.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
    }
}
