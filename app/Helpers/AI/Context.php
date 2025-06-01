<?php

declare(strict_types=1);

namespace App\Helpers\AI;

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
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Class Context.
 *
 * This class is the helper for handling AI context.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Context
{
    /**
     * Get the context.
     *
     * @param string     $type
     * @param array|null $route
     * @param bool       $isChanged
     *
     * @return string
     */
    public static function getContext(string $type = 'all', array $route = null, bool $isChanged = false): string
    {
        if ($type === 'all') {
            return self::getAgentRole($isChanged) . "\n\n" . self::getProductContext($isChanged) . "\n\n" . self::getRouteContext($route, $isChanged);
        }

        if ($type === 'agent') {
            return self::getAgentRole($isChanged);
        }

        if ($type === 'product') {
            return self::getProductContext($isChanged);
        }

        if ($type === 'route') {
            return self::getRouteContext($route, $isChanged);
        }

        return '';
    }

    /**
     * Get the agent role context.
     *
     * @param bool $isChanged
     *
     * @return string
     */
    private static function getAgentRole(bool $isChanged = false): string
    {
        return 'Agent context' . ($isChanged ? ' (changed)' : '') . ':
- You are a DevOps expert with extended knowledge on Kubernetes and Helm.
- You know how to utilize GitOps tools like FluxCD and ArgoCD to deploy applications to Kubernetes.
- You are an expert on the Laravel framework and know the blade templating engine.
- You are in a professional work environment and always use the correct tone and language.
- Your preferred language is English. If prompted otherwise, use the desired language from there on out.
- Format your responses exclusively with HTML. Limit the amount of HTML to a minimum.
- If you are asked to generate a code snippet, command, configuration, script, template, documentation or report, always use the correct syntax for the language.
- Never generate plain PHP code, unless explicitly asked to do so. Default to using the Laravel Blade syntax instead.';
    }

    /**
     * Get the product context.
     *
     * @param bool $isChanged
     *
     * @return string
     */
    private static function getProductContext(bool $isChanged = false): string
    {
        return 'Product context' . ($isChanged ? ' (changed)' : '') . ':
- The product you are active in is called "Kublade".
- The product is a simple templating engine for Kubernetes manifests based on the Laravel framework.
- The homepage for the product can be found at https://kublade.org/.
- The general documentation for the product can be found at https://kublade.org/docs/intro.
- The api definition for the product can be found at https://kublade.org/api/.
- The github repository for the product can be found at https://github.com/kublade/kublade.
- The product can be used via the web interface or via the API. It does not support the usage of the CLI.
- The product supports the usage of Laravel Blade within all template files, no matter the file extension.
- If asked to generate templates for this product, you may use Laravels Blade syntax within any generated file.
- When generating templates for this product, you may make use of the following variables:
  - Variables: {{ $variable[\'key\'] }}
  - Secrets: {{ $secret[\'key\'] }}
  - Port claims: {{ $portClaims[\'key\'] }}
  - Paused: {{ $paused }}
  - Limit enabled: {{ $limits[\'enabled\'] }}
    If marked as enabled, you may also inject the following:
    - CPU: {{ $limits[\'cpu\'] }}
    - Memory: {{ $limits[\'memory\'] }}';
    }

    /**
     * Get the route context.
     *
     * @param ?array $route
     * @param bool   $isChanged
     *
     * @return string
     */
    private static function getRouteContext(?array $route, bool $isChanged = false): string
    {
        if (!$route) {
            return 'Route context:
- No route is currently active.';
        }

        $editorContext = '';

        if (
            $route['name'] === 'template.details' ||
            $route['name'] === 'template.details_file'
        ) {
            $injectableContext = [];

            $template = Template::find($route['parameters']['template_id']);

            $template->fields->each(function ($item) use (&$injectableContext) {
                if ($item->secret) {
                    $injectableContext[] = '{{ $secret[\'' . $item->key . '\'] }}';
                } else {
                    $injectableContext[] = '{{ $data[\'' . $item->key . '\'] }}';
                }
            });

            $template->ports->each(function ($item) use (&$injectableContext) {
                $injectableContext[] = '{{ $portClaims[\'' . $item->claim . '\'] }}';
            });

            $injectableContext[] = '{{ $paused }}';
            $injectableContext[] = '{{ $limits[\'enabled\'] }}';
            $injectableContext[] = '{{ $limits[\'cpu\'] }}';
            $injectableContext[] = '{{ $limits[\'memory\'] }}';

            $editorContext = 'Editor context:
- There is an editor open on the page.
- The editor can utilize all the templating features of the product.
- The following variables, secrets and port claims are available to the editor:
  ' . implode("\n  ", $injectableContext) . "\n\n";
        }

        return $editorContext . 'Route context' . ($isChanged ? ' (changed)' : '') . ':
- The current route is ' . $route['name'] . '.
- Route context parameters and resulting objects are provided to you as JSON.
- The current route parameters are: 
  ' . json_encode($route['parameters']) . '
- The related objects are:
  ' . self::getRouteObjects($route['parameters']);
    }

    /**
     * Get the route objects.
     *
     * @param array $parameters
     *
     * @return string
     */
    private static function getRouteObjects(array $parameters): string
    {
        if (empty($parameters)) {
            return 'No objects are related to the current route.';
        }

        $objects = [];

        foreach ($parameters as $key => $value) {
            /**
             * TODO: Use API response serializer instead of Eloquent model toArray() method.
             *       This is a temporary solution to get the objects into the context.
             *       A more thorough solution would be to use the API response serializer
             *       since they should an extended object representation.
             */
            switch ($key) {
                case 'project_id':
                    $objects[] = '- A project:
    ' . json_encode(Project::find($value)?->toArray());

                    break;
                case 'template_id':
                    $objects[] = '- A template:
    ' . json_encode(Template::find($value)?->toArray());

                    break;
                case 'folder_id':
                    $objects[] = '- A folder:
    ' . json_encode(TemplateDirectory::find($value)?->toArray());

                    break;
                case 'file_id':
                    $objects[] = '- A file:
    ' . json_encode(TemplateFile::find($value)?->toArray());

                    break;
                case 'field_id':
                    $objects[] = '- A field:
    ' . json_encode(TemplateField::find($value)?->toArray());

                    break;
                case 'option_id':
                    $objects[] = '- A option:
    ' . json_encode(TemplateFieldOption::find($value)?->toArray());

                    break;
                case 'port_id':
                    $objects[] = '- A port:
    ' . json_encode(TemplatePort::find($value)?->toArray());

                    break;
                case 'cluster_id':
                    $objects[] = '- A cluster:
    ' . json_encode(Cluster::find($value)?->toArray());

                    break;
                case 'deployment_id':
                    $objects[] = '- A deployment:
    ' . json_encode(Deployment::find($value)?->toArray());

                    break;
                case 'network_policy_id':
                    $objects[] = '- A network policy:
    ' . json_encode(DeploymentLink::find($value)?->toArray());

                    break;
                case 'commit_id':
                    $objects[] = '- A commit:
    ' . json_encode(DeploymentCommit::find($value)?->toArray());

                    break;
                case 'user_id':
                    $objects[] = '- A user:
    ' . json_encode(User::find($value)?->toArray());

                    break;
                case 'role_id':
                    $objects[] = '- A role:
    ' . json_encode(Role::find($value)?->toArray());

                    break;
                default:
            }
        }

        return implode("\n  ", $objects);
    }
}
