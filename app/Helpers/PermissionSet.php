<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentLink;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFieldOption;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\Projects\Templates\TemplatePort;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Class Filesize.
 *
 * This class is the helper for handling filesizes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class PermissionSet
{
    /**
     * The object cache.
     *
     * @var array
     */
    protected static $objectCache = [
        'projects'         => [],
        'templates'        => [],
        'folders'          => [],
        'files'            => [],
        'fields'           => [],
        'options'          => [],
        'ports'            => [],
        'clusters'         => [],
        'deployments'      => [],
        'commits'          => [],
        'network-policies' => [],
    ];

    /**
     * Get the permission set for the user.
     *
     * @param string  $permission
     * @param Request $request
     *
     * @return Collection
     */
    public static function from(string $permission, Request $request): Collection
    {
        if (
            Str::contains($permission, 'projects.') &&
            $projectId = $request->route('project_id')
        ) {
            $permission = Str::replaceFirst('projects.', 'projects.' . $projectId . '.', $permission);
        }

        if (
            Str::contains($permission, 'templates.') &&
            $templateId = $request->route('template_id')
        ) {
            $permission = Str::replaceFirst('templates.', 'templates.' . $templateId . '.', $permission);
        }

        if (
            Str::contains($permission, 'folders.') &&
            $folderId = $request->route('folder_id')
        ) {
            $permission = Str::replaceFirst('folders.', 'folders.' . $folderId . '.', $permission);
        }

        if (
            Str::contains($permission, 'files.') &&
            $fileId = $request->route('file_id')
        ) {
            $permission = Str::replaceFirst('files.', 'files.' . $fileId . '.', $permission);
        }

        if (
            Str::contains($permission, 'fields.') &&
            $fieldId = $request->route('field_id')
        ) {
            $permission = Str::replaceFirst('fields.', 'fields.' . $fieldId . '.', $permission);
        }

        if (
            Str::contains($permission, 'options.') &&
            $optionId = $request->route('option_id')
        ) {
            $permission = Str::replaceFirst('options.', 'options.' . $optionId . '.', $permission);
        }

        if (
            Str::contains($permission, 'ports.') &&
            $portId = $request->route('port_id')
        ) {
            $permission = Str::replaceFirst('ports.', 'ports.' . $portId . '.', $permission);
        }

        if (
            Str::contains($permission, 'clusters.') &&
            $clusterId = $request->route('cluster_id')
        ) {
            $permission = Str::replaceFirst('clusters.', 'clusters.' . $clusterId . '.', $permission);
        }

        if (
            Str::contains($permission, 'deployments.') &&
            $deploymentId = $request->route('deployment_id')
        ) {
            $permission = Str::replaceFirst('deployments.', 'deployments.' . $deploymentId . '.', $permission);
        }

        if (
            Str::contains($permission, 'commits.') &&
            $commitId = $request->route('commit_id')
        ) {
            $permission = Str::replaceFirst('commits.', 'commits.' . $commitId . '.', $permission);
        }

        if (
            Str::contains($permission, 'network-policies.') &&
            $networkPolicyId = $request->route('network_policy_id')
        ) {
            $permission = Str::replaceFirst('network-policies.', 'network-policies.' . $networkPolicyId . '.', $permission);
        }

        $permissionSegments = explode('.', $permission);
        $permissionSet      = collect();

        foreach ($permissionSegments as $key => $permissionSegment) {
            if ($key < count($permissionSegments) - 1) {
                $permissionSet->push(implode('.', array_slice($permissionSegments, 0, $key + 1)) . '.*');
            } else {
                $permissionSet->push(implode('.', array_slice($permissionSegments, 0, $key + 1)));
            }
        }

        return $permissionSet;
    }

    /**
     * Get all permissions.
     *
     * @return Collection
     */
    public static function all(): Collection
    {
        $permissions = collect();

        collect(Route::getRoutes())->each(function (LaravelRoute $route) use ($permissions) {
            collect($route->middleware())->each(function ($middleware) use ($permissions, $route) {
                if (Str::startsWith($middleware, 'ui.permission.guard:')) {
                    $uri        = $route->uri();
                    $parameters = [
                        'project_id'        => Str::contains($uri, '{project_id}'),
                        'template_id'       => Str::contains($uri, '{template_id}'),
                        'folder_id'         => Str::contains($uri, '{folder_id}'),
                        'file_id'           => Str::contains($uri, '{file_id}'),
                        'field_id'          => Str::contains($uri, '{field_id}'),
                        'option_id'         => Str::contains($uri, '{option_id}'),
                        'port_id'           => Str::contains($uri, '{port_id}'),
                        'cluster_id'        => Str::contains($uri, '{cluster_id}'),
                        'deployment_id'     => Str::contains($uri, '{deployment_id}'),
                        'commit_id'         => Str::contains($uri, '{commit_id}'),
                        'network_policy_id' => Str::contains($uri, '{network_policy_id}'),
                    ];

                    if (Str::contains($route->getName(), 'api.')) {
                        $permission = Str::after($middleware, 'api.permission.guard:');
                    } else {
                        $permission = Str::after($middleware, 'ui.permission.guard:');
                    }

                    $permissions->push([
                        'permission' => $permission,
                        'parameters' => $parameters,
                    ]);
                }
            });
        });

        $projects = Project::all();

        $permissions->each(function ($permission, $key) use ($projects, $permissions) {
            if (Str::contains($permission['permission'], 'projects.') && $permission['parameters']['project_id']) {
                $projects->each(function ($project) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('projects.', 'projects.' . $project->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $templates = Template::all();

        $permissions->each(function ($permission, $key) use ($templates, $permissions) {
            if (Str::contains($permission['permission'], 'templates.') && $permission['parameters']['template_id']) {
                $templates->each(function ($template) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('templates.', 'templates.' . $template->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $folders = TemplateDirectory::all();

        $permissions->each(function ($permission, $key) use ($folders, $permissions) {
            if (Str::contains($permission['permission'], 'folders.') && $permission['parameters']['folder_id']) {
                $folders->each(function ($folder) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('folders.', 'folders.' . $folder->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $files = TemplateFile::all();

        $permissions->each(function ($permission, $key) use ($files, $permissions) {
            if (Str::contains($permission['permission'], 'files.') && $permission['parameters']['file_id']) {
                $files->each(function ($file) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('files.', 'files.' . $file->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $fields = TemplateField::all();

        $permissions->each(function ($permission, $key) use ($fields, $permissions) {
            if (Str::contains($permission['permission'], 'fields.') && $permission['parameters']['field_id']) {
                $fields->each(function ($field) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('fields.', 'fields.' . $field->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $options = TemplateFieldOption::all();

        $permissions->each(function ($permission, $key) use ($options, $permissions) {
            if (Str::contains($permission['permission'], 'options.') && $permission['parameters']['option_id']) {
                $options->each(function ($option) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('options.', 'options.' . $option->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $ports = TemplatePort::all();

        $permissions->each(function ($permission, $key) use ($ports, $permissions) {
            if (Str::contains($permission['permission'], 'ports.') && $permission['parameters']['port_id']) {
                $ports->each(function ($port) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('ports.', 'ports.' . $port->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $clusters = Cluster::all();

        $permissions->each(function ($permission, $key) use ($clusters, $permissions) {
            if (Str::contains($permission['permission'], 'clusters.') && $permission['parameters']['cluster_id']) {
                $clusters->each(function ($cluster) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('clusters.', 'clusters.' . $cluster->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $deployments = Deployment::all();

        $permissions->each(function ($permission, $key) use ($deployments, $permissions) {
            if (Str::contains($permission['permission'], 'deployments.') && $permission['parameters']['deployment_id']) {
                $deployments->each(function ($deployment) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('deployments.', 'deployments.' . $deployment->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $commits = DeploymentCommit::all();

        $permissions->each(function ($permission, $key) use ($commits, $permissions) {
            if (Str::contains($permission['permission'], 'commits.') && $permission['parameters']['commit_id']) {
                $commits->each(function ($commit) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('commits.', 'commits.' . $commit->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        $networkPolicies = DeploymentLink::all();

        $permissions->each(function ($permission, $key) use ($networkPolicies, $permissions) {
            if (Str::contains($permission['permission'], 'network-policies.') && $permission['parameters']['network_policy_id']) {
                $networkPolicies->each(function ($networkPolicy) use ($permission, $permissions) {
                    $permissions->push([
                        'permission' => Str::replaceFirst('network-policies.', 'network-policies.' . $networkPolicy->id . '.', $permission['permission']),
                        'parameters' => $permission['parameters'],
                    ]);
                });

                $permissions->forget($key);
            }
        });

        return $permissions->pluck('permission')
            ->unique()
            ->map(function ($permission) {
                return self::from($permission, new Request());
            })
            ->flatten()
            ->unique()
            ->push('*')
            ->sort()
            ->values();
    }

    /**
     * Get all permissions as a tree structure.
     *
     * @return array
     */
    public static function tree(): array
    {
        $permissions = self::all();
        $tree        = [
            '*' => null,
        ];

        foreach ($permissions as $permission) {
            $parts   = explode('.', $permission);
            $current = &$tree;

            for ($i = 0; $i < count($parts); $i++) {
                $part      = $parts[$i];
                $isNumeric = is_numeric($part);

                if ($isNumeric) {
                    // Get parent key without the ID
                    $parentParts = array_slice($parts, 0, $i);
                    $parentKey   = implode('.', $parentParts);

                    // Create wildcard key if it doesn't exist
                    $wildcardKey = $parentKey ? $parentKey . '.*' : '*';

                    if (!isset($current[$wildcardKey])) {
                        $current[$wildcardKey] = null;
                    }

                    // Add this permission under the wildcard
                    $remainingParts = array_slice($parts, $i + 1);

                    if (!empty($remainingParts)) {
                        $current = &$current[$wildcardKey];
                    }
                } else {
                    if (!isset($current[$part])) {
                        $current[$part] = null;
                    }
                    $current = &$current[$part];
                }
            }
        }

        // Sort keys recursively
        $sortTree = function (&$node) use (&$sortTree) {
            if (empty($node)) {
                return;
            }

            ksort($node);

            foreach ($node as &$child) {
                $sortTree($child);
            }
        };

        $sortTree($tree);

        return $tree;
    }

    /**
     * Sync the permissions.
     */
    public static function sync(): void
    {
        $permissions = self::all();

        $permissions->each(function ($permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['name' => $permission, 'guard_name' => 'web']
            );
        });

        Permission::whereNotIn('name', $permissions->pluck('permission'))->delete();
    }

    /**
     * Translate the permission.
     *
     * @param string $permission
     *
     * @return string
     */
    public static function translate(string $permission): string
    {
        if ($match = Str::match('/projects\.[0-9a-f-]{36}/i', $permission)) {
            $projectId = Str::between($match, 'projects.', '.');
            $object    = !isset(self::$objectCache['projects'][$projectId]) ? Project::firstWhere('id', $projectId) : self::$objectCache['projects'][$projectId];

            if (!isset(self::$objectCache['projects'][$projectId])) {
                self::$objectCache['projects'][$projectId] = $object;
            }

            $permission = Str::replaceFirst('projects.' . $projectId, 'projects.<span class="badge bg-secondary mx-1" title="' . $projectId . '">' . $object->name . '</span>', $permission);
        }

        if ($match = Str::match('/templates\.[0-9a-f-]{36}/i', $permission)) {
            $templateId = Str::between($match, 'templates.', '.');
            $object     = !isset(self::$objectCache['templates'][$templateId]) ? Template::firstWhere('id', $templateId) : self::$objectCache['templates'][$templateId];

            if (!isset(self::$objectCache['templates'][$templateId])) {
                self::$objectCache['templates'][$templateId] = $object;
            }

            $permission = Str::replaceFirst('templates.' . $templateId, 'templates.<span class="badge bg-secondary mx-1" title="' . $templateId . '">' . $object->name . '</span>', $permission);
        }

        if ($match = Str::match('/folders\.[0-9a-f-]{36}/i', $permission)) {
            $folderId = Str::between($match, 'folders.', '.');
            $object   = !isset(self::$objectCache['folders'][$folderId]) ? TemplateDirectory::firstWhere('id', $folderId) : self::$objectCache['folders'][$folderId];

            if (!isset(self::$objectCache['folders'][$folderId])) {
                self::$objectCache['folders'][$folderId] = $object;
            }

            $permission = Str::replaceFirst('folders.' . $folderId, 'folders.<span class="badge bg-secondary mx-1" title="' . $folderId . '">' . $object->name . '</span>', $permission);
        }

        if ($match = Str::match('/files\.[0-9a-f-]{36}/i', $permission)) {
            $fileId = Str::between($match, 'files.', '.');
            $object = !isset(self::$objectCache['files'][$fileId]) ? TemplateFile::firstWhere('id', $fileId) : self::$objectCache['files'][$fileId];

            if (!isset(self::$objectCache['files'][$fileId])) {
                self::$objectCache['files'][$fileId] = $object;
            }

            $permission = Str::replaceFirst('files.' . $fileId, 'files.<span class="badge bg-secondary mx-1" title="' . $fileId . '">' . $object->name . '</span>', $permission);
        }

        if ($match = Str::match('/fields\.[0-9a-f-]{36}/i', $permission)) {
            $fieldId = Str::between($match, 'fields.', '.');
            $object  = !isset(self::$objectCache['fields'][$fieldId]) ? TemplateField::firstWhere('id', $fieldId) : self::$objectCache['fields'][$fieldId];

            if (!isset(self::$objectCache['fields'][$fieldId])) {
                self::$objectCache['fields'][$fieldId] = $object;
            }

            $permission = Str::replaceFirst('fields.' . $fieldId, 'fields.<span class="badge bg-secondary mx-1" title="' . $fieldId . '">' . $object->label . '</span>', $permission);
        }

        if ($match = Str::match('/options\.[0-9a-f-]{36}/i', $permission)) {
            $optionId = Str::between($match, 'options.', '.');
            $object   = !isset(self::$objectCache['options'][$optionId]) ? TemplateFieldOption::firstWhere('id', $optionId) : self::$objectCache['options'][$optionId];

            if (!isset(self::$objectCache['options'][$optionId])) {
                self::$objectCache['options'][$optionId] = $object;
            }

            $permission = Str::replaceFirst('options.' . $optionId, 'options.<span class="badge bg-secondary mx-1" title="' . $optionId . '">' . $object->label . '</span>', $permission);
        }

        if ($match = Str::match('/ports\.[0-9a-f-]{36}/i', $permission)) {
            $portId = Str::between($match, 'ports.', '.');
            $object = !isset(self::$objectCache['ports'][$portId]) ? TemplatePort::firstWhere('id', $portId) : self::$objectCache['ports'][$portId];

            if (!isset(self::$objectCache['ports'][$portId])) {
                self::$objectCache['ports'][$portId] = $object;
            }

            $permission = Str::replaceFirst('ports.' . $portId, 'ports.<span class="badge bg-secondary mx-1" title="' . $portId . '">' . $object->claim . '</span>', $permission);
        }

        if ($match = Str::match('/clusters\.[0-9a-f-]{36}/i', $permission)) {
            $clusterId = Str::between($match, 'clusters.', '.');
            $object    = !isset(self::$objectCache['clusters'][$clusterId]) ? Cluster::firstWhere('id', $clusterId) : self::$objectCache['clusters'][$clusterId];

            if (!isset(self::$objectCache['clusters'][$clusterId])) {
                self::$objectCache['clusters'][$clusterId] = $object;
            }

            $permission = Str::replaceFirst('clusters.' . $clusterId, 'clusters.<span class="badge bg-secondary mx-1" title="' . $clusterId . '">' . $object->name . '</span>', $permission);
        }

        if ($match = Str::match('/deployments\.[0-9a-f-]{36}/i', $permission)) {
            $deploymentId = Str::between($match, 'deployments.', '.');
            $object       = !isset(self::$objectCache['deployments'][$deploymentId]) ? Deployment::firstWhere('id', $deploymentId) : self::$objectCache['deployments'][$deploymentId];

            if (!isset(self::$objectCache['deployments'][$deploymentId])) {
                self::$objectCache['deployments'][$deploymentId] = $object;
            }

            $permission = Str::replaceFirst('deployments.' . $deploymentId, 'deployments.<span class="badge bg-secondary mx-1" title="' . $deploymentId . '">' . $object->name . '</span>', $permission);
        }

        if ($match = Str::match('/commits\.[0-9a-f-]{36}/i', $permission)) {
            $commitId = Str::between($match, 'commits.', '.');
            $object   = !isset(self::$objectCache['commits'][$commitId]) ? DeploymentCommit::firstWhere('id', $commitId) : self::$objectCache['commits'][$commitId];

            if (!isset(self::$objectCache['commits'][$commitId])) {
                self::$objectCache['commits'][$commitId] = $object;
            }

            $permission = Str::replaceFirst('commits.' . $commitId, 'commits.<span class="badge bg-secondary mx-1" title="' . $commitId . '">' . $object->hash . '</span>', $permission);
        }

        if ($match = Str::match('/network-policies\.[0-9a-f-]{36}/i', $permission)) {
            $networkPolicyId = Str::between($match, 'network-policies.', '.');
            $object          = !isset(self::$objectCache['network-policies'][$networkPolicyId]) ? DeploymentLink::firstWhere('id', $networkPolicyId) : self::$objectCache['network-policies'][$networkPolicyId];

            if (!isset(self::$objectCache['network-policies'][$networkPolicyId])) {
                self::$objectCache['network-policies'][$networkPolicyId] = $object;
            }

            $permission = Str::replaceFirst('network-policies.' . $networkPolicyId, 'network-policies.<span class="badge bg-secondary mx-1" title="' . $networkPolicyId . '">' . ($object->source?->name ?? __('N/A')) . ' <> ' . ($object->target?->name ?? __('N/A')) . '</span>', $permission);
        }

        return $permission;
    }
}
