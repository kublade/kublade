<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\TemplatePort;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class TemplatePortFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<TemplatePort>
 */
class TemplatePortFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplatePort::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id'    => null,
            'group'          => $this->faker->randomElement(['services', 'ingress']),
            'claim'          => $this->faker->optional()->word,
            'preferred_port' => $this->faker->optional()->numberBetween(1, 65535),
            'random'         => $this->faker->boolean,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }
}
