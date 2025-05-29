<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DeploymentSecretDataTest.
 *
 * Unit tests for the DeploymentSecretData model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentSecretDataTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentSecretData $deploymentSecretData;

    private Deployment $deployment;

    private TemplateField $templateField;

    private Template $template;

    private User $user;

    private Project $project;

    private Cluster $cluster;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test project
        $this->project = Project::factory()->create([
            'name'    => 'Test Project',
            'user_id' => $this->user->id,
        ]);

        // Create test template
        $this->template = Template::factory()->create([
            'name'    => 'Test Template',
            'user_id' => $this->user->id,
        ]);

        // Create test template field
        $this->templateField = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'key'         => 'test_field',
            'label'       => 'Test Field',
            'type'        => 'input_text',
        ]);

        // Create test cluster
        $this->cluster = Cluster::factory()->create([
            'project_id' => $this->project->id,
        ]);

        // Create test deployment
        $this->deployment = Deployment::factory()->create([
            'user_id'     => $this->user->id,
            'project_id'  => $this->project->id,
            'template_id' => $this->template->id,
            'cluster_id'  => $this->cluster->id,
            'name'        => 'Test Deployment',
        ]);

        // Create test deployment data
        $this->deploymentSecretData = DeploymentSecretData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'test_key',
            'value'             => 'test_value',
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployment_secret_data', $this->deploymentSecretData->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deploymentSecretData->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->deploymentSecretData->id);
        $this->assertEquals(36, strlen($this->deploymentSecretData->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->deploymentSecretData->deleted_at);
        $this->deploymentSecretData->delete();
        $this->assertNotNull($this->deploymentSecretData->deleted_at);
        $this->assertSoftDeleted($this->deploymentSecretData);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->deploymentSecretData->delete();
        $this->assertSoftDeleted($this->deploymentSecretData);
        $this->deploymentSecretData->restore();
        $this->assertNull($this->deploymentSecretData->deleted_at);
    }

    /**
     * @test
     */
    public function itHasDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentSecretData->deployment());
        $this->assertInstanceOf(Deployment::class, $this->deploymentSecretData->deployment);
        $this->assertEquals($this->deployment->id, $this->deploymentSecretData->deployment->id);
        $this->assertEquals($this->deployment->name, $this->deploymentSecretData->deployment->name);
    }

    /**
     * @test
     */
    public function itHasTemplateFieldRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentSecretData->field());
        $this->assertInstanceOf(TemplateField::class, $this->deploymentSecretData->field);
        $this->assertEquals($this->templateField->id, $this->deploymentSecretData->field->id);
        $this->assertEquals($this->templateField->key, $this->deploymentSecretData->field->key);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $deploymentSecretData = DeploymentSecretData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'custom_key',
            'value'             => 'custom_value',
        ]);

        $this->assertInstanceOf(DeploymentSecretData::class, $deploymentSecretData);
        $this->assertEquals($this->deployment->id, $deploymentSecretData->deployment_id);
        $this->assertEquals($this->templateField->id, $deploymentSecretData->template_field_id);
        $this->assertEquals('custom_key', $deploymentSecretData->key);
        $this->assertEquals('custom_value', $deploymentSecretData->value);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $deploymentSecretData = DeploymentSecretData::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        $this->assertCount(3, $deploymentSecretData);

        foreach ($deploymentSecretData as $data) {
            $this->assertInstanceOf(DeploymentSecretData::class, $data);
            $this->assertEquals($this->deployment->id, $data->deployment_id);
            $this->assertIsString($data->key);
            $this->assertIsString($data->value);
            $this->assertNotNull($data->template_field_id);
        }
    }

    /**
     * @test
     */
    public function itEncryptsValueField(): void
    {
        $value                = 'sensitive_data';
        $deploymentSecretData = DeploymentSecretData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'secret_key',
            'value'             => $value,
        ]);

        // Value should be encrypted in the database
        $this->assertNotEquals($value, $deploymentSecretData->getRawOriginal('value'));

        // Value should be decrypted when accessed
        $this->assertEquals($value, $deploymentSecretData->value);
    }

    /**
     * @test
     */
    public function deploymentCanHaveMultipleDataEntries(): void
    {
        // Create 3 additional data entries
        $data = DeploymentSecretData::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Should have 4 total entries (1 from setUp + 3 new ones)
        $this->assertCount(4, $this->deployment->deploymentSecretData);

        // Verify all IDs are present
        $expectedIds = collect([$this->deploymentSecretData->id])->merge($data->pluck('id'));
        $this->assertEquals($expectedIds->sort()->values(), $this->deployment->deploymentSecretData->pluck('id')->sort()->values());
    }

    /**
     * @test
     */
    public function templateFieldCanBeUsedInMultipleDeploymentSecretData(): void
    {
        $deployment2 = Deployment::factory()->create([
            'user_id'     => $this->user->id,
            'project_id'  => $this->project->id,
            'template_id' => $this->template->id,
            'cluster_id'  => $this->cluster->id,
            'name'        => 'Test Deployment 2',
        ]);

        $data1 = DeploymentSecretData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'shared_key',
            'value'             => 'value1',
        ]);

        $data2 = DeploymentSecretData::factory()->create([
            'deployment_id'     => $deployment2->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'shared_key',
            'value'             => 'value2',
        ]);

        $this->assertEquals($this->templateField->id, $data1->field->id);
        $this->assertEquals($this->templateField->id, $data2->field->id);
        $this->assertNotEquals($data1->value, $data2->value);
    }
}
