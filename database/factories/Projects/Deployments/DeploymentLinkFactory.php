<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentLinkFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentLink>
 */
class DeploymentLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentLink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_deployment_id' => Deployment::factory(),
            'target_deployment_id' => Deployment::factory(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }
}
