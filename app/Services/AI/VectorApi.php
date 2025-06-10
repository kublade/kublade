<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Http;

/**
 * Class VectorApi.
 *
 * This class is the API for generating embedding vectors.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class VectorApi
{
    /**
     * Get the vectors from the prompt.
     *
     * @param string $prompt
     *
     * @return array
     */
    public function getVectorsFromPrompt(string $prompt): array
    {
        if (!config('ai.remote_embedding')) {
            throw new AiException('Remote embedding is not enabled', 500);
        }

        $response = Http::withToken(config('ai.api_key'))->post(config('ai.url') . config('ai.embedding_endpoint'), [
            'model' => config('ai.embedding_model'),
            'input' => $prompt,
        ]);

        $embedding = $response['data'][0]['embedding'] ?? null;

        if (empty($embedding)) {
            return [];
        }

        return $embedding;
    }
}
