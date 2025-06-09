<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * Class McpHandler.
 *
 * This class is the handler for talking to MCP servers.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class McpHandler
{
    protected ?array $provider;

    /**
     * Construct the MCP handler.
     *
     * @param string $provider
     * @param string $prompt
     * @param string $context
     */
    public function __construct(string $provider, string $prompt, string $context)
    {
        $path = base_path('mcp/' . $provider . '.blade.yaml');

        if (!file_exists($path)) {
            throw new AiException('MCP provider not found', 404);
        }

        try {
            $content = file_get_contents($path);

            $yaml = Yaml::parse(
                Blade::render(
                    $content,
                    [
                        'prompt'  => $prompt,
                        'context' => $context,
                    ],
                    false
                )
            );

            if (!isset($yaml['name']) || $yaml['name'] !== $provider) {
                throw new AiException('MCP provider not found', 404);
            }

            $this->provider = $yaml;
        } catch (Throwable $e) {
            throw new AiException('MCP provider instantiation failed', 500);
        }
    }

    /**
     * Handle the MCP request.
     *
     * @param string      $prompt
     * @param string|null $context
     *
     * @return string
     */
    public function handle(string $prompt, ?string $context = null): string
    {
        if (!$this->provider) {
            throw new AiException('MCP provider not set', 400);
        }

        $method = strtolower($this->provider['method']);

        if (!empty($this->provider['headers'])) {
            $httpClient = Http::withHeaders($this->provider['headers'])
                ->{$method}(
                    $this->provider['url'],
                    !empty($this->provider['input']) ? $this->provider['input'] : []
                );
        } else {
            $httpClient = Http::{$method}(
                $this->provider['url'],
                !empty($this->provider['input']) ? $this->provider['input'] : []
            );
        }

        $response = $httpClient->body();
        $jsonData = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($this->provider['output']['path'])) {
                $path = explode('.', ltrim($this->provider['output']['path'], '$.'));

                try {
                    return $this->getJsonData($jsonData, $path);
                } catch (AiException $e) {
                    throw new AiException('Path not found in JSON data', 400);
                }
            }
        }

        return $response;
    }

    /**
     * Get the data from the JSON data based on the path.
     *
     * @param array|object $jsonData
     * @param array        $path
     *
     * @return mixed
     */
    private function getJsonData(array | object $jsonData, array $path): mixed
    {
        $pathSegment = array_shift($path);

        if (
            (
                is_array($jsonData) &&
                !array_key_exists($pathSegment, $jsonData)
            ) ||
            (
                is_object($jsonData) &&
                !property_exists($jsonData, $pathSegment)
            )
        ) {
            throw new AiException('Path not found in JSON data', 400);
        }

        $value = is_array($jsonData) ? $jsonData[$pathSegment] : $jsonData->{$pathSegment};

        if (count($path) > 0) {
            return $this->getJsonData($value, $path);
        }

        return $value;
    }
}
