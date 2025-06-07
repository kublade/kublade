<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AiException;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $pathSegments = explode('/', $request->path);
        $fileName     = array_pop($pathSegments);

        if (!empty($pathSegments)) {
            $folder = $this->ensurePathExists($template, $pathSegments, null);
        }

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

        $pathSegments = explode('/', $request->path);
        array_pop($pathSegments);

        if (!empty($pathSegments)) {
            $this->ensurePathExists($template, $pathSegments, null);
        }

        return redirect()->back()->with('success', __('Template folder applied.'))->with('from', 'ai');
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
}
