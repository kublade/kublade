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

    /**
     * Construct the vector router.
     *
     * @param string $path
     * @param bool   $createIfNotExists
     */
    public function __construct(?string $path = null, bool $createIfNotExists = false)
    {
        $path = $path ?? config('ai.prompt_routing_vectors_file');

        try {
            if (
                $createIfNotExists &&
                !Storage::disk('local')->exists($path)
            ) {
                Storage::disk('local')->put($path, json_encode([]));
            }

            $this->vectors = json_decode(Storage::disk('local')->get($path), true);
        } catch (Throwable $e) {
            $this->vectors = [];

            throw new AiException('Failed to load vectors from storage', 500);
        }
    }

    /**
     * Find the nearest vector in the database.
     *
     * @param array $embedding
     * @param float $minScore
     *
     * @return array|null
     */
    public function findNearest(array $embedding, float $minScore = 0.75): ?array
    {
        $best      = null;
        $bestScore = -1;

        foreach ($this->vectors as $entry) {
            $score = $this->cosineSimilarity($embedding, $entry['embedding']);

            if ($score > $bestScore) {
                $best      = $entry;
                $bestScore = $score;
            }
        }

        return $bestScore >= $minScore ? $best + ['score' => $bestScore] : null;
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
}
