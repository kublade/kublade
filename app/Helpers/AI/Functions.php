<?php

declare(strict_types=1);

use App\Helpers\AI\Context;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

if (!function_exists('processChatContent')) {
    /**
     * Process the chat content.
     *
     * @param string     $string
     * @param mixed      $mode
     * @param mixed|null $templateId
     *
     * @return string
     */
    function processChatContent($string, $mode = 'ask', $templateId = null): string
    {
        $pattern = '#<kbl-tool\b[^>]*>(.*?)</kbl-tool>|<kbl-tool\b[^>]*/>#s';

        preg_match_all($pattern, $string, $matches);

        collect($matches[0])->each(function ($xmlBlock) use ($mode, $templateId, &$string) {
            try {
                $xml = simplexml_load_string($xmlBlock);
            } catch (Exception $e) {
                $viewContent = View::make('ai.tool-action', [
                    'available' => false,
                ])->render();

                $string = Str::replace($xmlBlock, $viewContent, $string);

                return;
            }

            $attributes = collect($xml->attributes())->map(function ($val) {
                return (string) $val;
            });

            $type   = $attributes->get('type');
            $action = $attributes->get('action');

            switch ($type) {
                case 'template_file':
                    $path    = $attributes->get('path');
                    $content = Str::of($xmlBlock)
                        ->after('>')
                        ->beforeLast('</kbl-tool>')
                        ->trim();

                    $viewContent = View::make('ai.tool-action', [
                        'available'  => true,
                        'type'       => $type,
                        'action'     => $action,
                        'path'       => $path,
                        'content'    => $content,
                        'mode'       => $mode,
                        'templateId' => $templateId,
                    ])->render();

                    $string = Str::replace($xmlBlock, $viewContent, $string);

                    break;
                case 'template_folder':
                    $path        = $attributes->get('path');
                    $viewContent = View::make('ai.tool-action', [
                        'available'  => true,
                        'type'       => $type,
                        'action'     => $action,
                        'path'       => $path,
                        'mode'       => $mode,
                        'templateId' => $templateId,
                    ])->render();

                    $string = Str::replace($xmlBlock, $viewContent, $string);

                    break;
                case 'template_port':
                    $group          = $attributes->get('group');
                    $claim          = $attributes->get('claim');
                    $preferred_port = (int) $attributes->get('preferred_port');
                    $random         = (bool) $attributes->get('random');

                    $viewContent = View::make('ai.tool-action', [
                        'available'      => true,
                        'type'           => $type,
                        'action'         => $action,
                        'group'          => $group,
                        'claim'          => $claim,
                        'preferred_port' => $preferred_port,
                        'random'         => $random,
                        'mode'           => $mode,
                        'templateId'     => $templateId,
                    ])->render();

                    $string = Str::replace($xmlBlock, $viewContent, $string);

                    break;
                case 'template_field':
                    $advanced      = (bool) $attributes->get('advanced');
                    $field_type    = $attributes->get('field_type');
                    $required      = (bool) $attributes->get('required');
                    $secret        = (bool) $attributes->get('secret');
                    $label         = $attributes->get('label');
                    $key           = $attributes->get('key');
                    $value         = $attributes->get('value');
                    $min           = (int) $attributes->get('min');
                    $max           = (int) $attributes->get('max');
                    $step          = (int) $attributes->get('step');
                    $set_on_create = (bool) $attributes->get('set_on_create');
                    $set_on_update = (bool) $attributes->get('set_on_update');
                    $optionsArray  = [];

                    if (array_key_exists('kbl-field-option', (array) $xml)) {
                        $options      = $xml->{'kbl-field-option'};
                        $optionsArray = iterator_to_array($options, false);
                    }

                    $options = collect($optionsArray)->map(function ($xml) {
                        $attributes = collect($xml->attributes())->map(function ($val) {
                            return (string) $val;
                        });

                        return [
                            'label'   => $attributes->get('label'),
                            'value'   => $attributes->get('value'),
                            'default' => (bool) $attributes->get('default'),
                        ];
                    });

                    $viewContent = View::make('ai.tool-action', [
                        'available'     => true,
                        'type'          => $type,
                        'field_type'    => $field_type,
                        'action'        => $action,
                        'advanced'      => $advanced,
                        'required'      => $required,
                        'secret'        => $secret,
                        'label'         => $label,
                        'key'           => $key,
                        'value'         => $value,
                        'min'           => $min,
                        'max'           => $max,
                        'step'          => $step,
                        'set_on_create' => $set_on_create,
                        'set_on_update' => $set_on_update,
                        'options'       => $options,
                        'mode'          => $mode,
                        'templateId'    => $templateId,
                    ])->render();

                    $string = Str::replace($xmlBlock, $viewContent, $string);

                    break;
                default:
                    $viewContent = View::make('ai.tool-action', [
                        'available' => false,
                    ])->render();

                    $string = Str::replace($xmlBlock, $viewContent, $string);

                    break;
            }
        });

        return $string;
    }

    if (!function_exists('processChatContext')) {
        /**
         * Process the chat context.
         *
         * @param string $string
         *
         * @return string
         */
        function processChatContext($string): string
        {
            return str_replace("\n", '<br />', $string);
        }
    }
}

if (!function_exists('filterChatMessages')) {
    /**
     * Filter the chat messages.
     *
     * @param array $messages
     *
     * @return array
     */
    function filterChatMessages(array $messages): array
    {
        return Context::filterDuplicateContext($messages);
    }
}

if (!function_exists('ensureSpaceBeforeNewLine')) {
    /**
     * Ensure a space before a new line.
     *
     * @param string $string
     *
     * @return string
     */
    function ensureSpaceBeforeNewLine($string): string
    {
        return collect(explode("\n", $string))
            ->map(function ($line) {
                return '    ' . ltrim($line);
            })
            ->implode("\n");
    }
}
