<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\VectorRouter;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Class GenerateVector.
 *
 * This class is the command to generate the vector for the prompt and action.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class GenerateVector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kbl:vector:generate {prompt} {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the vector for the prompt and action';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prompt = $this->argument('prompt');

        if (empty($prompt)) {
            $prompt = $this->ask('Enter the prompt');
        }

        $action = $this->argument('action');

        if (empty($action)) {
            $action = $this->ask('Enter the action');
        }

        $vectors = new VectorRouter(config('ai.prompt_routing_vectors_file'), true);

        try {
            if (!config('ai.remote_embedding')) {
                throw new Exception('Remote embedding is not enabled');
            }

            $response = Http::withToken(config('ai.api_key'))->post(config('ai.url') . config('ai.embedding_endpoint'), [
                'model' => config('ai.embedding_model'),
                'input' => $prompt,
            ]);

            $embedding = $response['data'][0]['embedding'] ?? null;

            if (empty($embedding)) {
                $this->error('Failed to get the embedding');

                return;
            }

            $vectors->publish($prompt, $action, $embedding, 'api');

            $this->info('Vectors pushed to the database');
        } catch (Throwable $e) {
            try {
                $vector = $this->vectorFromPrompt($prompt);

                $vectors->publish($prompt, $action, $vector, 'simulation');

                $this->info('Vectors pushed to the database');
            } catch (Throwable $e) {
                $this->error('Failed to generate the vectors');
            }
        }
    }

    /**
     * Generate a pseudo-semantic embedding vector from a prompt.
     * Note: This is NOT a real embedding, just a deterministic hash-based approximation.
     *
     * @param string $prompt
     * @param int    $dimensions
     *
     * @return array
     */
    private function vectorFromPrompt(string $prompt, int $dimensions = 1536): array
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
