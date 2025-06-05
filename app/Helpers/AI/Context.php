<?php

declare(strict_types=1);

namespace App\Helpers\AI;

use App\Exceptions\AiException;
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
     *
     * @return string
     */
    public static function getContext(string $type = 'all', array $route = null): string
    {
        if ($type === 'all') {
            return self::getAgentRole() . "\n\n" .
                self::getProductContext() . "\n\n" .
                self::getToolContext();
        }

        if ($type === 'agent') {
            return self::getAgentRole();
        }

        if ($type === 'product') {
            return self::getProductContext();
        }

        if ($type === 'route') {
            return self::getRouteContext($route);
        }

        if ($type === 'tool') {
            return self::getToolContext();
        }

        return '';
    }

    /**
     * Get the agent role context.
     *
     * @return string
     */
    private static function getAgentRole(): string
    {
        return 'Agent context:
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
     * @return string
     */
    private static function getProductContext(): string
    {
        return 'Product context:
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
     *
     * @return string
     */
    private static function getRouteContext(?array $route): string
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

        return $editorContext . 'Route context:
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

    /**
     * Get the tool context.
     *
     * @return string
     */
    private static function getToolContext(): string
    {
        return 'Tool context:
- Tool configurations are provided as XML within the "kbl-tool" tags.
- All tools have the actions "create", "update" and "delete".
- The template_folder tool has no content.
- The template_file tool has the content of the file as its content unless the action is "delete".
- If no content is provided, the tool call uses self-closing tags.
- You have access to the following tools:
<kbl-tool type="template_file" action="create" path="path/to/file">
  content of the file
</kbl-tool>
<kbl-tool type="template_folder" action="create" path="path/to/folder" />';
    }

    /**
     * Get the token count of the messages.
     *
     * @param array $messages
     *
     * @return int
     */
    public static function getTokenCount(array $messages): int
    {
        return collect($messages)->map(function ($message) {
            return count(explode(' ', $message['content']));
        })->sum();
    }

    /**
     * Check if the token count of the messages exceeds the maximum number of tokens.
     *
     * @param array $messages
     *
     * @return bool
     */
    public static function tokenCountExceeded(array $messages): bool
    {
        return self::getTokenCount($messages) > config('ai.max_tokens');
    }

    /**
     * Ensure the payload size is within the maximum number of tokens.
     *
     * This is a recursive function that will reduce the number of messages
     * until the token count is within the maximum number of tokens.
     *
     * It will always remove the oldest messages first. Messages are always
     * removed as pairs of user prompt and assistant response to ensure a
     * meaningful context is kept.
     *
     * @param array $messages
     *
     * @return array
     */
    public static function ensureTokenCount(array $messages): array
    {
        $onlySystemMessages = collect($messages)->filter(function ($message) {
            return $message['role'] !== 'system';
        })->count() === 0;

        if ($onlySystemMessages) {
            throw new AiException('All user messages were filtered out of the context', 500);
        }

        if (self::tokenCountExceeded($messages)) {
            $firstAssistantIndex = collect($messages)
                ->keys()
                ->first(function ($key) use ($messages) {
                    return $messages[$key]['role'] === 'assistant';
                });

            $systemMessages = collect($messages)->filter(function ($message, $key) use ($firstAssistantIndex) {
                return $message['role'] === 'system' &&
                    $key <= $firstAssistantIndex &&
                    $message['protected'];
            });

            if (!empty($firstAssistantIndex)) {
                return self::ensureTokenCount(
                    collect($messages)->except(range(0, $firstAssistantIndex))
                        ->reverse()
                        ->merge($systemMessages->reverse())
                        ->reverse()
                        ->values()
                        ->toArray()
                );
            }
        }

        return array_values($messages);
    }

    /**
     * Filter duplicate context.
     *
     * @param array $messages
     *
     * @return array
     */
    public static function filterDuplicateContext(array $messages): array
    {
        collect($messages)->pluck('key')
            ->unique()
            ->filter(function ($key) {
                return $key !== null;
            })
            ->each(function ($key) use (&$messages) {
                $lastApplicableIndex = collect($messages)->keys()->last(function ($index) use ($messages, $key) {
                    return $messages[$index]['key'] === $key;
                });

                $messages = collect($messages)->filter(function ($message, $index) use ($key, $lastApplicableIndex) {
                    return $message['key'] !== $key || $index === $lastApplicableIndex;
                })->toArray();
            });

        return $messages;
    }

    /**
     * Prepare the messages for submission to the AI API.
     *
     * @param array $messages
     *
     * @return array
     */
    public static function prepareSubmission(array $messages): array
    {
        return collect($messages)->map(function ($message) {
            return [
                'role'    => $message['role'],
                'content' => $message['content'],
            ];
        })->toArray();
    }
}
