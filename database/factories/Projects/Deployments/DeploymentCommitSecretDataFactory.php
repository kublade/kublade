<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentCommitSecretData;
use App\Models\Projects\Deployments\DeploymentSecretData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeploymentCommitSecretData>
 */
class DeploymentCommitSecretDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentCommitSecretData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_commit_id'      => null,
            'deployment_secret_data_id' => DeploymentSecretData::factory(),
            'key'                       => $this->faker->word,
            'value'                     => encrypt($this->faker->word),
        ];
    }
}
