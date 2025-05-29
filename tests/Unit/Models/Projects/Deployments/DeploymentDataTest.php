<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DeploymentDataTest.
 *
 * Unit tests for the DeploymentData model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentDataTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentData $deploymentData;

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
        $this->deploymentData = DeploymentData::factory()->create([
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
        $this->assertEquals('deployment_data', $this->deploymentData->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deploymentData->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->deploymentData->id);
        $this->assertEquals(36, strlen($this->deploymentData->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->deploymentData->deleted_at);
        $this->deploymentData->delete();
        $this->assertNotNull($this->deploymentData->deleted_at);
        $this->assertSoftDeleted($this->deploymentData);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->deploymentData->delete();
        $this->assertSoftDeleted($this->deploymentData);
        $this->deploymentData->restore();
        $this->assertNull($this->deploymentData->deleted_at);
    }

    /**
     * @test
     */
    public function itHasDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentData->deployment());
        $this->assertInstanceOf(Deployment::class, $this->deploymentData->deployment);
        $this->assertEquals($this->deployment->id, $this->deploymentData->deployment->id);
        $this->assertEquals($this->deployment->name, $this->deploymentData->deployment->name);
    }

    /**
     * @test
     */
    public function itHasTemplateFieldRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentData->field());
        $this->assertInstanceOf(TemplateField::class, $this->deploymentData->field);
        $this->assertEquals($this->templateField->id, $this->deploymentData->field->id);
        $this->assertEquals($this->templateField->key, $this->deploymentData->field->key);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $deploymentData = DeploymentData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'custom_key',
            'value'             => 'custom_value',
        ]);

        $this->assertInstanceOf(DeploymentData::class, $deploymentData);
        $this->assertEquals($this->deployment->id, $deploymentData->deployment_id);
        $this->assertEquals($this->templateField->id, $deploymentData->template_field_id);
        $this->assertEquals('custom_key', $deploymentData->key);
        $this->assertEquals('custom_value', $deploymentData->value);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $deploymentData = DeploymentData::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        $this->assertCount(3, $deploymentData);

        foreach ($deploymentData as $data) {
            $this->assertInstanceOf(DeploymentData::class, $data);
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
        $value          = 'sensitive_data';
        $deploymentData = DeploymentData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'secret_key',
            'value'             => $value,
        ]);

        // Value should be encrypted in the database
        $this->assertNotEquals($value, $deploymentData->getRawOriginal('value'));

        // Value should be decrypted when accessed
        $this->assertEquals($value, $deploymentData->value);
    }

    /**
     * @test
     */
    public function deploymentCanHaveMultipleDataEntries(): void
    {
        // Create 3 additional data entries
        $data = DeploymentData::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Should have 4 total entries (1 from setUp + 3 new ones)
        $this->assertCount(4, $this->deployment->deploymentData);

        // Verify all IDs are present
        $expectedIds = collect([$this->deploymentData->id])->merge($data->pluck('id'));
        $this->assertEquals($expectedIds->sort()->values(), $this->deployment->deploymentData->pluck('id')->sort()->values());
    }

    /**
     * @test
     */
    public function templateFieldCanBeUsedInMultipleDeploymentData(): void
    {
        $deployment2 = Deployment::factory()->create([
            'user_id'     => $this->user->id,
            'project_id'  => $this->project->id,
            'template_id' => $this->template->id,
            'cluster_id'  => $this->cluster->id,
            'name'        => 'Test Deployment 2',
        ]);

        $data1 = DeploymentData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $this->templateField->id,
            'key'               => 'shared_key',
            'value'             => 'value1',
        ]);

        $data2 = DeploymentData::factory()->create([
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
