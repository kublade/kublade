<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentCommitData;
use App\Models\Projects\Deployments\DeploymentData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeploymentCommitData>
 */
class DeploymentCommitDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentCommitData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_commit_id' => null,
            'deployment_data_id'   => DeploymentData::factory(),
            'key'                  => $this->faker->word,
            'value'                => encrypt($this->faker->word),
        ];
    }
}
