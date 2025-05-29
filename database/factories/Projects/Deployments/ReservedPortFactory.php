<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\ReservedPort;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ReservedPortFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<ReservedPort>
 */
class ReservedPortFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReservedPort::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_id' => null, // Should be set when creating
            'group'         => $this->faker->randomElement(['services', 'ingress']),
            'claim'         => $this->faker->optional()->word,
            'port'          => $this->faker->unique()->numberBetween(1024, 65535),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
