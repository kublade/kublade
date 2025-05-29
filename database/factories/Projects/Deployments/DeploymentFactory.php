<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<Deployment>
 */
class DeploymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deployment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->word(),
            'user_id'     => User::factory(),
            'project_id'  => Project::factory(),
            'template_id' => Template::factory(),
            'cluster_id'  => Cluster::factory(),
            'uuid'        => $this->faker->uuid(),
            'paused'      => false,
            'update'      => false,
            'delete'      => false,
        ];
    }
}
