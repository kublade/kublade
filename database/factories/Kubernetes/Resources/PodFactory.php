<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Resources;

use App\Models\Kubernetes\Resources\Ns;
use App\Models\Kubernetes\Resources\Pod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class PodFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<Pod>
 */
class PodFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'namespace_id'     => Ns::factory(),
            'api_version'      => 'v1',
            'name'             => $this->faker->word(),
            'resource_version' => (string) $this->faker->numberBetween(1, 1000),
            'pod_created_at'   => now(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }
}
