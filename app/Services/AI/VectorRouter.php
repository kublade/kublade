<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class VectorRouter.
 *
 * This class is the helper for finding the nearest vector in a database of vectors.
 * It uses the cosine similarity algorithm to find the nearest vector.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class VectorRouter
{
    protected array $vectors = [];

    protected VectorSimulator $simulator;

    protected VectorApi $api;

    /**
     * Construct the vector router.
     *
     * @param string $path
     * @param bool   $createIfNotExists
     */
    public function __construct(?string $path = null, bool $createIfNotExists = true)
    {
        $this->simulator = new VectorSimulator();
        $this->api       = new VectorApi();

        $path = $path ?? config('ai.prompt_routing_vectors_file');

        try {
            if (
                $createIfNotExists &&
                !Storage::disk('local')->exists($path)
            ) {
                Storage::disk('local')->put($path, json_encode([]));
            }

            $this->vectors = json_decode(Storage::disk('local')->get($path), false);
        } catch (Throwable $e) {
            $this->vectors = [];

            throw new AiException('Failed to load vectors from storage', 500);
        }
    }

    /**
     * Find the nearest vector in the database.
     *
     * @param array  $embedding
     * @param string $source
     * @param float  $minScore
     *
     * @return object|null
     */
    public function findNearest(array $embedding, string $source = 'api', float $minScore = 0.75): ?object
    {
        $best      = null;
        $bestScore = -1;

        foreach ($this->vectors as $entry) {
            if ($entry->source !== $source) {
                continue;
            }

            $score = $this->cosineSimilarity($embedding, $entry->embedding);

            if ($score > $bestScore) {
                $best      = (array) $entry;
                $bestScore = $score;
            }
        }

        return $bestScore >= $minScore ? (object) [
            ...$best,
            'score' => $bestScore,
        ] : null;
    }

    /**
     * Calculate the cosine similarity between two vectors.
     *
     * @param array $a
     * @param array $b
     *
     * @return float
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        $dot = $normA = $normB = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        return ($normA && $normB) ? $dot / (sqrt($normA) * sqrt($normB)) : 0.0;
    }

    /**
     * Publish the vector to the database.
     *
     * @param string $prompt
     * @param string $action
     * @param array  $embedding
     * @param string $source
     */
    public function publish(string $prompt, string $action, array $embedding, string $source): void
    {
        $vectors = array_filter($this->vectors, function ($vector) use ($prompt) {
            return $vector->prompt !== $prompt;
        });

        $vectors[] = (object) [
            'prompt'    => $prompt,
            'action'    => $action,
            'source'    => $source,
            'embedding' => array_values($embedding),
        ];

        $vectors = array_values($vectors);

        $this->vectors = $vectors;

        Storage::disk('local')->put(config('ai.prompt_routing_vectors_file'), json_encode($this->vectors));
    }

    /**
     * Get the vectors from the prompt.
     *
     * @param string $prompt
     *
     * @return array
     */
    public function getVectorsFromPrompt(string $prompt): object
    {
        if (!config('ai.remote_embedding')) {
            return (object) [
                'prompt'    => $prompt,
                'source'    => 'simulator',
                'embedding' => $this->simulator->getVectorsFromPrompt($prompt),
            ];
        }

        try {
            return (object) [
                'prompt'    => $prompt,
                'source'    => 'api',
                'embedding' => $this->api->getVectorsFromPrompt($prompt),
            ];
        } catch (Throwable $e) {
            return (object) [
                'prompt'    => $prompt,
                'source'    => 'simulator',
                'embedding' => $this->simulator->getVectorsFromPrompt($prompt),
            ];
        }
    }
}
