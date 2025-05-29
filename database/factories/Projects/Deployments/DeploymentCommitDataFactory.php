<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommitData;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentCommitDataFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentCommitData>
 */
class DeploymentCommitDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentCommitData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deployment     = Deployment::factory()->create();
        $templateField  = TemplateField::factory()->create();
        $deploymentData = DeploymentData::factory()->create([
            'deployment_id'     => $deployment->id,
            'template_field_id' => $templateField->id,
        ]);

        return [
            'deployment_commit_id' => null, // Should be set when creating
            'deployment_data_id'   => $deploymentData->id,
            'key'                  => $this->faker->word,
            'value'                => $this->faker->word,
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }
}
