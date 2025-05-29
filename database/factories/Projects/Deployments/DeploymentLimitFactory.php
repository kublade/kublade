<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentLimitFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentLimit>
 */
class DeploymentLimitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentLimit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_id' => null, // Should be set when creating
            'is_active'     => $this->faker->boolean,
            'cpu'           => $this->faker->randomFloat(2, 0.1, 4),
            'memory'        => $this->faker->numberBetween(256, 4096), // MB
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
