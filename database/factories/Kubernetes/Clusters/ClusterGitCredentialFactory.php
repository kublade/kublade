<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Clusters;

use App\Models\Kubernetes\Clusters\ClusterGitCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ClusterGitCredentialFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<ClusterGitCredential>
 */
class ClusterGitCredentialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClusterGitCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cluster_id'  => null, // Should be set when creating
            'url'         => $this->faker->url,
            'branch'      => 'main',
            'credentials' => '{}',
            'username'    => $this->faker->userName,
            'email'       => $this->faker->email,
            'base_path'   => '/',
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
