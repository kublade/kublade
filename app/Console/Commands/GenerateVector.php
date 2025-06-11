<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\VectorRouter;
use Illuminate\Console\Command;
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

        $vectorRouter = new VectorRouter(config('ai.prompt_routing_vectors_file'), true);

        try {
            $vectorsFromPrompt = $vectorRouter->getVectorsFromPrompt($prompt);

            $vectorRouter->publish($prompt, $action, $vectorsFromPrompt->embedding, $vectorsFromPrompt->source);

            $this->info('Vectors pushed to the database');
        } catch (Throwable $e) {
            $this->error('Failed to generate the vectors');
        }
    }
}
