<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AiException;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class AiController.
 *
 * This class is the controller for the AI actions.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class AiController extends Controller
{
    /**
     * Apply the tool action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function action_tool_action(Request $request)
    {
        Validator::make($request->all(), [
            'type' => [
                'required',
                Rule::in([
                    'template_file',
                    'template_folder',
                    'template_port',
                    'template_field',
                ]),
            ],
        ])->validate();

        try {
            switch ($request->type) {
                case 'template_file':
                    return $this->applyTemplateFile($request);
                case 'template_folder':
                    return $this->applyTemplateFolder($request);
                case 'template_port':
                    return $this->applyTemplatePort($request);
                case 'template_field':
                    return $this->applyTemplateField($request);
                default:
                    throw new AiException('Invalid tool action', 400);
            }
        } catch (AiException $e) {
            return redirect()->back()->with('warning', __('Ooops, something went wrong.'));
        }
    }

    /**
     * Apply the template file action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function applyTemplateFile(Request $request)
    {
        Validator::make($request->all(), [
            'action' => [
                'required',
                Rule::in([
                    'create',
                    'update',
                    'delete',
                ]),
            ],
            'path'        => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'template_id' => ['required', 'string', 'max:255'],
        ])->validate();

        $template = Template::find($request->template_id);

        if (!$template) {
            throw new AiException('Template not found', 404);
        }

        $folder       = null;
        $path         = Str::startsWith($request->path, '/') ? ltrim($request->path, '/') : $request->path;
        $pathSegments = explode('/', $path);
        $fileName     = array_pop($pathSegments);

        if (!empty($pathSegments)) {
            $folder = $this->ensurePathExists($template, $pathSegments, null);
        }

        if (in_array($request->action, ['create', 'update'])) {
            try {
                $template->files()->updateOrCreate([
                    'name'                  => $fileName,
                    'template_directory_id' => $folder,
                ], [
                    'content'   => $request->content,
                    'mime_type' => str_ends_with($fileName, '.yaml') ? 'text/yaml' : 'application/octet-stream',
                ]);
            } catch (Exception $e) {
                throw new AiException('Failed to create template file', 500);
            }
        } elseif ($request->action === 'delete') {
            $query = $template->files()->where('name', $fileName);

            if (!empty($pathSegments)) {
                $query = $query->whereHas('directory', function ($query) use ($pathSegments) {
                    $this->buildRecursiveFolderQuery($query, $pathSegments);
                });
            }

            $query->delete();
        }

        return redirect()->back()->with('success', __('Template file applied.'))->with('from', 'ai');
    }

    /**
     * Apply the template folder action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function applyTemplateFolder(Request $request)
    {
        Validator::make($request->all(), [
            'action' => [
                'required',
                Rule::in([
                    'create',
                    'update',
                    'delete',
                ]),
            ],
            'path'        => ['required', 'string', 'max:255'],
            'template_id' => ['required', 'string', 'max:255'],
        ])->validate();

        $template = Template::find($request->template_id);

        if (!$template) {
            throw new AiException('Template not found', 404);
        }

        $path         = Str::startsWith($request->path, '/') ? ltrim($request->path, '/') : $request->path;
        $pathSegments = explode('/', $path);

        if (!empty($pathSegments)) {
            if (in_array($request->action, ['create', 'update'])) {
                $this->ensurePathExists($template, $pathSegments, null);
            } elseif ($request->action === 'delete') {
                if (count($pathSegments) === 1) {
                    $template->directories()
                        ->where('name', $pathSegments[0])
                        ->whereNull('parent_id')
                        ->delete();
                } elseif (count($pathSegments) > 1) {
                    $currentFolder = array_pop($pathSegments);
                    $ids           = $template->directories()
                        ->where('name', $currentFolder)
                        ->whereHas('parent', function ($query) use ($pathSegments) {
                            $this->buildRecursiveFolderQuery($query, $pathSegments);
                        })
                        ->pluck('id');

                    $template->directories()
                        ->whereIn('id', $ids)
                        ->delete();
                }
            }
        }

        return redirect()->back()->with('success', __('Template folder applied.'))->with('from', 'ai');
    }

    /**
     * Build the recursive folder query.
     * Checks if the folder exists, has the correct parent and returns the query.
     *
     * @param Builder $query
     * @param array   $pathSegments
     *
     * @return mixed
     */
    private function buildRecursiveFolderQuery(mixed $query, array $pathSegments): mixed
    {
        $currentFolder = array_pop($pathSegments);

        if (empty($pathSegments)) {
            return $query->where('name', '=', $currentFolder)
                ->whereNull('parent_id');
        }

        return $query->where('name', '=', $currentFolder)
            ->whereHas('parent', function ($query) use ($pathSegments) {
                $this->buildRecursiveFolderQuery($query, $pathSegments);
            });
    }

    /**
     * Ensure the path exists.
     *
     * @param Template               $template
     * @param array                  $pathSegments
     * @param TemplateDirectory|null $parent
     *
     * @return string
     */
    private function ensurePathExists(Template $template, array $pathSegments, ?TemplateDirectory $parent = null): string
    {
        try {
            $folderToCreate = array_shift($pathSegments);

            $folder = $template->directories()->updateOrCreate([
                'name'      => $folderToCreate,
                'parent_id' => $parent?->id,
            ]);

            if (!empty($pathSegments)) {
                return $this->ensurePathExists($template, $pathSegments, $folder);
            }

            return $folder->id;
        } catch (Exception $e) {
            throw new AiException('Failed to ensure path exists', 500);
        }
    }

    /**
     * Apply the template port action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function applyTemplatePort(Request $request)
    {
        Validator::make($request->all(), [
            'action' => [
                'required',
                Rule::in([
                    'create',
                    'update',
                    'delete',
                ]),
            ],
            'group'          => ['required', 'string', 'max:255'],
            'claim'          => ['required', 'string', 'max:255'],
            'preferred_port' => ['required', 'integer'],
            'random'         => ['required', 'boolean'],
            'template_id'    => ['required', 'string', 'max:255'],
        ])->validate();

        $template = Template::find($request->template_id);

        if (!$template) {
            throw new AiException('Template not found', 404);
        }

        if (in_array($request->action, ['create', 'update'])) {
            $template->ports()->updateOrCreate([
                'group' => $request->group,
                'claim' => $request->claim,
            ], [
                'preferred_port' => $request->preferred_port,
                'random'         => $request->random,
            ]);
        } elseif ($request->action === 'delete') {
            $template->ports()
                ->where('group', $request->group)
                ->where('claim', $request->claim)
                ->delete();
        }

        return redirect()->back()->with('success', __('Template port applied.'))->with('from', 'ai');
    }

    /**
     * Apply the template field action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function applyTemplateField(Request $request)
    {
        Validator::make($request->all(), [
            'action' => [
                'required',
                Rule::in([
                    'create',
                    'update',
                    'delete',
                ]),
            ],
            'template_id'         => ['required', 'string', 'max:255'],
            'field.type'          => ['required', 'string'],
            'field.advanced'      => ['nullable', 'boolean'],
            'field.label'         => ['required', 'string'],
            'field.key'           => ['required', 'string'],
            'field.value'         => ['nullable', 'string'],
            'field.required'      => ['nullable', 'boolean'],
            'field.secret'        => ['nullable', 'boolean'],
            'field.set_on_create' => ['nullable', 'boolean'],
            'field.set_on_update' => ['nullable', 'boolean'],
        ])->validate();

        $template = Template::find($request->template_id);

        if (!$template) {
            throw new AiException('Template not found', 404);
        }

        if (in_array($request->action, ['create', 'update'])) {
            $field = null;

            switch ($request->field['type']) {
                case 'input_number':
                case 'input_range':
                    Validator::make($request->all(), [
                        'field.min'  => ['required', 'numeric'],
                        'field.max'  => ['required', 'numeric'],
                        'field.step' => ['required', 'numeric'],
                    ])->validate();

                    $field = $template->fields()->updateOrCreate([
                        'key' => $request->field['key'],
                    ], [
                        'type'          => $request->field['type'],
                        'label'         => $request->field['label'],
                        'value'         => $request->field['value'],
                        'min'           => $request->field['min'],
                        'max'           => $request->field['max'],
                        'step'          => $request->field['step'],
                        'advanced'      => ! empty($request->field['advanced']),
                        'required'      => ! empty($request->field['required']),
                        'secret'        => ! empty($request->field['secret']),
                        'set_on_create' => ! empty($request->field['set_on_create']),
                        'set_on_update' => ! empty($request->field['set_on_update']),
                    ]);

                    break;
                default:
                    $field = $template->fields()->updateOrCreate([
                        'key' => $request->field['key'],
                    ], [
                        'type'          => $request->field['type'],
                        'label'         => $request->field['label'],
                        'key'           => $request->field['key'],
                        'value'         => $request->field['value'],
                        'advanced'      => ! empty($request->field['advanced']),
                        'required'      => ! empty($request->field['required']),
                        'secret'        => ! empty($request->field['secret']),
                        'set_on_create' => ! empty($request->field['set_on_create']),
                        'set_on_update' => ! empty($request->field['set_on_update']),
                    ]);

                    break;
            }

            if (
                $field && (
                    $request->field['type'] === 'input_radio' ||
                    $request->field['type'] === 'input_radio_image' ||
                    $request->field['type'] === 'select'
                )
            ) {
                Validator::make($request->all(), [
                    'field.options'           => ['required', 'array'],
                    'field.options.*.label'   => ['required', 'string'],
                    'field.options.*.value'   => ['required', 'string'],
                    'field.options.*.default' => ['nullable', 'boolean'],
                ])->validate();

                collect($request->field['options'])->each(function ($option) use ($field) {
                    $field->options()->updateOrCreate([
                        'value' => $option['value'],
                    ], [
                        'label'   => $option['label'],
                        'default' => $option['default'] ?? false,
                    ]);
                });
            }
        } elseif ($request->action === 'delete') {
            $template->fields()->where('key', $request->field['key'])->delete();
        }

        return redirect()->back()->with('success', __('Template field applied.'))->with('from', 'ai');
    }
}
