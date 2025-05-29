<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Clusters;

use App\Models\Kubernetes\Clusters\ClusterK8sCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ClusterK8sCredentialFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<ClusterK8sCredential>
 */
class ClusterK8sCredentialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClusterK8sCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cluster_id'            => null, // Should be set when creating
            'kubeconfig'            => '{}',
            'api_url'               => 'https://kubernetes.default.svc.cluster.local',
            'service_account_token' => 'token',
            'node_prefix'           => $this->faker->optional()->word,
            'created_at'            => now(),
            'updated_at'            => now(),
        ];
    }
}
