<?php

declare(strict_types=1);

namespace Database\Factories\Kubernetes\Resources;

use App\Models\Kubernetes\Resources\Pod;
use App\Models\Kubernetes\Resources\PodLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class PodLogFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<PodLog>
 */
class PodLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PodLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pod_id'     => Pod::factory(),
            'logs'       => $this->faker->text(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
