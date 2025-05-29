<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Deployments;

use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommitSecretData;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class DeploymentCommitSecretDataFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<DeploymentCommitSecretData>
 */
class DeploymentCommitSecretDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeploymentCommitSecretData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deployment           = Deployment::factory()->create();
        $templateField        = TemplateField::factory()->create();
        $deploymentSecretData = DeploymentSecretData::factory()->create([
            'deployment_id'     => $deployment->id,
            'template_field_id' => $templateField->id,
        ]);

        return [
            'deployment_commit_id'      => null, // Should be set when creating
            'deployment_secret_data_id' => $deploymentSecretData->id,
            'key'                       => $this->faker->word,
            'value'                     => $this->faker->word,
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }
}
