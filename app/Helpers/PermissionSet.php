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
}
