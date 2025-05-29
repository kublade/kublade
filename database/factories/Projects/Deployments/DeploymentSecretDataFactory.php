<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentSecretDataFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentSecretData>
 */
class DeploymentSecretDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentSecretData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deployment_id'     => null, // Should be set when creating
            'template_field_id' => TemplateField::factory(),
            'key'               => $this->faker->word,
            'value'             => $this->faker->password,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
