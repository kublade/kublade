<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Clusters;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Kubernetes\Clusters\GitCredential;
use App\Models\Kubernetes\Clusters\K8sCredential;
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

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Cluster $cluster) {
            // Create required credentials
            K8sCredential::factory()->create([
                'cluster_id'            => $cluster->id,
                'api_url'               => 'https://kubernetes.default.svc.cluster.local',
                'kubeconfig'            => '{}',
                'service_account_token' => 'token',
            ]);

            GitCredential::factory()->create([
                'cluster_id'  => $cluster->id,
                'url'         => 'https://github.com/example/repo.git',
                'branch'      => 'main',
                'credentials' => '{}',
                'username'    => 'git',
                'email'       => 'git@example.com',
                'base_path'   => '/',
            ]);
        });
    }
}
