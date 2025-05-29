<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentDataFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentData>
 */
class DeploymentDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentData::class;

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
            'value'             => $this->faker->sentence,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
