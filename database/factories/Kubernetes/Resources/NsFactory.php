<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Resources;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Kubernetes\Resources\Ns;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class NsFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<Ns>
 */
class NsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ns::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cluster_id'           => Cluster::factory(),
            'uuid'                 => $this->faker->uuid(),
            'api_version'          => 'v1',
            'name'                 => $this->faker->unique()->word,
            'resource_version'     => $this->faker->numberBetween(1, 1000),
            'namespace_created_at' => now(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }
}
