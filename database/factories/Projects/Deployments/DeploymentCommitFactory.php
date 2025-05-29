<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentCommit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentCommitFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentCommit>
 */
class DeploymentCommitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentCommit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_id' => null, // Should be set when creating
            'hash'          => $this->faker->sha1,
            'message'       => $this->faker->sentence,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
