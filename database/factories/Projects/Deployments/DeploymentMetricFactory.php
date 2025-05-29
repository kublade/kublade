<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentMetricFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentMetric>
 */
class DeploymentMetricFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentMetric::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_id'  => null, // Should be set when creating
            'cpu_core_usage' => $this->faker->randomFloat(2, 0, 1),
            'memory_bytes'   => $this->faker->numberBetween(1024 * 1024, 1024 * 1024 * 1024), // 1MB to 1GB
            'storage_bytes'  => $this->faker->numberBetween(1024 * 1024, 1024 * 1024 * 1024), // 1MB to 1GB
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }
}
