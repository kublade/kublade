<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Clusters;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Projects\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ClusterFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<Cluster>
 */
class ClusterFactory extends Factory
{
    protected $model = Cluster::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->word(),
            'user_id'    => User::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
