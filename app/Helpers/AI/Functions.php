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
            $xml = simplexml_load_string($xmlBlock);

            $attributes = collect($xml->attributes())->map(function ($val) {
                return (string) $val;
            });

            $type   = $attributes->get('type');
            $action = $attributes->get('action');
            $path   = $attributes->get('path');

            switch ($type) {
                case 'template_file':
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
                default:
                    $viewContent = View::make('ai.tool-action', [
                        'available'  => false,
                        'type'       => $type,
                        'action'     => $action,
                        'path'       => $path,
                        'mode'       => $mode,
                        'templateId' => $templateId,
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
