<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentCommitData;
use App\Models\Projects\Deployments\DeploymentCommitSecretData;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection as SupportCollection;
use Tests\TestCase;

/**
 * Class DeploymentCommitTest.
 *
 * Unit tests for the DeploymentCommit model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentCommitTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentCommit $commit;

    private Deployment $deployment;

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

        // Create test commit
        $this->commit = DeploymentCommit::factory()->create([
            'deployment_id' => $this->deployment->id,
            'hash'          => 'abc123',
            'message'       => 'Test commit message',
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployment_commits', $this->commit->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->commit->getGuarded());
    }

    /**
     * @test
     */
    public function itCanHaveDeployment(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->commit->deployment());
        $this->assertInstanceOf(Deployment::class, $this->commit->deployment);
        $this->assertEquals($this->deployment->id, $this->commit->deployment->id);
        $this->assertEquals($this->deployment->name, $this->commit->deployment->name);
    }

    /**
     * @test
     */
    public function itCanHaveCommitData(): void
    {
        // Create template field
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'label'       => 'Test Field',
        ]);

        // Create deployment data
        $deploymentData = DeploymentData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $field->id,
            'key'               => 'test-key',
            'value'             => encrypt('test-value'),
        ]);

        // Create commit data
        $commitData = DeploymentCommitData::factory()->count(3)->create([
            'deployment_commit_id' => $this->commit->id,
            'deployment_data_id'   => $deploymentData->id,
            'key'                  => 'test-key',
            'value'                => encrypt('test-value'),
        ]);

        // Test the relationship
        $this->assertInstanceOf(HasMany::class, $this->commit->commitData());
        $this->assertInstanceOf(Collection::class, $this->commit->commitData);
        $this->assertCount(3, $this->commit->commitData);
        $this->assertInstanceOf(DeploymentCommitData::class, $this->commit->commitData->first());
        $this->assertEquals($commitData->pluck('id')->toArray(), $this->commit->commitData->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->commit->commitData()->exists());
        $this->assertEquals(3, $this->commit->commitData()->count());
    }

    /**
     * @test
     */
    public function itCanHaveCommitSecretData(): void
    {
        // Create template field
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'label'       => 'Test Secret Field',
        ]);

        // Create deployment secret data
        $deploymentSecretData = DeploymentSecretData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $field->id,
            'key'               => 'test-secret-key',
            'value'             => encrypt('test-secret-value'),
        ]);

        // Create commit secret data
        $commitSecretData = DeploymentCommitSecretData::factory()->count(3)->create([
            'deployment_commit_id'      => $this->commit->id,
            'deployment_secret_data_id' => $deploymentSecretData->id,
            'key'                       => 'test-secret-key',
            'value'                     => encrypt('test-secret-value'),
        ]);

        // Test the relationship
        $this->assertInstanceOf(HasMany::class, $this->commit->commitSecretData());
        $this->assertInstanceOf(Collection::class, $this->commit->commitSecretData);
        $this->assertCount(3, $this->commit->commitSecretData);
        $this->assertInstanceOf(DeploymentCommitSecretData::class, $this->commit->commitSecretData->first());
        $this->assertEquals($commitSecretData->pluck('id')->toArray(), $this->commit->commitSecretData->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->commit->commitSecretData()->exists());
        $this->assertEquals(3, $this->commit->commitSecretData()->count());
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->commit->deleted_at);
        $this->commit->delete();
        $this->assertNotNull($this->commit->deleted_at);
        $this->assertSoftDeleted($this->commit);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->commit->delete();
        $this->assertSoftDeleted($this->commit);
        $this->commit->restore();
        $this->assertNull($this->commit->deleted_at);
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->commit->id);
        $this->assertEquals(36, strlen($this->commit->id));
    }

    /**
     * @test
     */
    public function itCanGetDiff(): void
    {
        // Create template field
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'label'       => 'Test Field',
        ]);

        // Create deployment data
        $deploymentData = DeploymentData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $field->id,
            'key'               => 'test-key',
            'value'             => encrypt('new-value'),
        ]);

        // Create deployment secret data
        $deploymentSecretData = DeploymentSecretData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $field->id,
            'key'               => 'test-secret-key',
            'value'             => encrypt('new-secret-value'),
        ]);

        // Create commit data
        DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $this->commit->id,
            'deployment_data_id'   => $deploymentData->id,
            'key'                  => 'test-key',
            'value'                => encrypt('old-value'),
        ]);

        // Create commit secret data
        DeploymentCommitSecretData::factory()->create([
            'deployment_commit_id'      => $this->commit->id,
            'deployment_secret_data_id' => $deploymentSecretData->id,
            'key'                       => 'test-secret-key',
            'value'                     => encrypt('old-secret-value'),
        ]);

        // Test diff
        $diff = $this->commit->diff;
        $this->assertInstanceOf(SupportCollection::class, $diff);
        $this->assertCount(2, $diff);

        // Test plain data diff
        $plainDiff = $diff->where('type', 'plain')->first();
        $this->assertNotNull($plainDiff);
        $this->assertEquals('Test Field', $plainDiff['label']);
        $this->assertEquals('new-value', $plainDiff['current']);
        $this->assertEquals('old-value', $plainDiff['previous']);
        $this->assertEquals('test-key', $plainDiff['key']);

        // Test secret data diff
        $secretDiff = $diff->where('type', 'secret')->first();
        $this->assertNotNull($secretDiff);
        $this->assertEquals('Test Field', $secretDiff['label']);
        $this->assertEquals('new-secret-value', $secretDiff['current']);
        $this->assertEquals('old-secret-value', $secretDiff['previous']);
        $this->assertEquals('test-secret-key', $secretDiff['key']);
    }

    /**
     * @test
     */
    public function itCanGetEmptyDiff(): void
    {
        // Create template field
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'label'       => 'Test Field',
        ]);

        // Create deployment data with same value as commit
        $deploymentData = DeploymentData::factory()->create([
            'deployment_id'     => $this->deployment->id,
            'template_field_id' => $field->id,
            'key'               => 'test-key',
            'value'             => encrypt('same-value'),
        ]);

        // Create commit data with same value
        DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $this->commit->id,
            'deployment_data_id'   => $deploymentData->id,
            'key'                  => 'test-key',
            'value'                => encrypt('same-value'),
        ]);

        // Test diff
        $diff = $this->commit->diff;
        $this->assertInstanceOf(SupportCollection::class, $diff);
        $this->assertCount(0, $diff);
    }
}
