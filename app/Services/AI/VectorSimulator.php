<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Class VectorSimulator.
 *
 * This class is the simulator for generating pseudo-semantic embedding vectors.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class VectorSimulator
{
    /**
     * Generate a pseudo-semantic embedding vector from a prompt.
     * Note: This is NOT a real embedding, just a deterministic hash-based approximation.
     *
     * @param string $prompt
     * @param int    $dimensions
     *
     * @return array
     */
    public function getVectorsFromPrompt(string $prompt, int $dimensions = 1536): array
    {
        $words     = preg_split('/\s+/', strtolower(trim($prompt)));
        $sumVector = array_fill(0, $dimensions, 0.0);
        $count     = 0;

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $vector = $this->vectorFromWord($word, $dimensions);

            foreach ($vector as $i => $val) {
                $sumVector[$i] += $val;
            }

            $count++;
        }

        if ($count > 0) {
            foreach ($sumVector as $i => $val) {
                $sumVector[$i] /= $count;
            }
        }

        return $sumVector;
    }

    /**
     * Generate a per-word deterministic hash-based vector.
     *
     * @param string $word
     * @param int    $dimensions
     *
     * @return array
     */
    private function vectorFromWord(string $word, int $dimensions = 1536): array
    {
        $vector = [];
        $hash   = hash('sha512', $word, true);

        for ($i = 0; $i < $dimensions; $i++) {
            $byte     = ord($hash[$i % 64]);
            $vector[] = $byte / 255.0;
        }

        return $vector;
    }
}
