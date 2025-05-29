<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentCommitData;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DeploymentCommitDataTest.
 *
 * Unit tests for the DeploymentCommitData model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentCommitDataTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentCommitData $deploymentCommitData;

    private DeploymentCommit $deploymentCommit;

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

        // Create test deployment commit
        $this->deploymentCommit = DeploymentCommit::factory()->create([
            'deployment_id' => $this->deployment->id,
            'hash'          => 'abc123',
            'message'       => 'Test commit',
        ]);

        // Create test deployment commit data
        $this->deploymentCommitData = DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $this->deploymentCommit->id,
            'key'                  => 'test_key',
            'value'                => 'test_value',
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployment_commit_data', $this->deploymentCommitData->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deploymentCommitData->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->deploymentCommitData->id);
        $this->assertEquals(36, strlen($this->deploymentCommitData->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->deploymentCommitData->deleted_at);
        $this->deploymentCommitData->delete();
        $this->assertNotNull($this->deploymentCommitData->deleted_at);
        $this->assertSoftDeleted($this->deploymentCommitData);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->deploymentCommitData->delete();
        $this->assertSoftDeleted($this->deploymentCommitData);
        $this->deploymentCommitData->restore();
        $this->assertNull($this->deploymentCommitData->deleted_at);
    }

    /**
     * @test
     */
    public function itHasCommitRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentCommitData->commit());
        $this->assertInstanceOf(DeploymentCommit::class, $this->deploymentCommitData->commit);
        $this->assertEquals($this->deploymentCommit->id, $this->deploymentCommitData->commit->id);
        $this->assertEquals($this->deploymentCommit->hash, $this->deploymentCommitData->commit->hash);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $deploymentCommitData = DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $this->deploymentCommit->id,
            'key'                  => 'custom_key',
            'value'                => 'custom_value',
        ]);

        $this->assertInstanceOf(DeploymentCommitData::class, $deploymentCommitData);
        $this->assertEquals($this->deploymentCommit->id, $deploymentCommitData->deployment_commit_id);
        $this->assertEquals('custom_key', $deploymentCommitData->key);
        $this->assertEquals('custom_value', $deploymentCommitData->value);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $deploymentCommitData = DeploymentCommitData::factory()->count(3)->create([
            'deployment_commit_id' => $this->deploymentCommit->id,
        ]);

        $this->assertCount(3, $deploymentCommitData);

        foreach ($deploymentCommitData as $data) {
            $this->assertInstanceOf(DeploymentCommitData::class, $data);
            $this->assertEquals($this->deploymentCommit->id, $data->deployment_commit_id);
            $this->assertIsString($data->key);
            $this->assertIsString($data->value);
        }
    }

    /**
     * @test
     */
    public function itEncryptsValueField(): void
    {
        $value                = 'sensitive_data';
        $deploymentCommitData = DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $this->deploymentCommit->id,
            'key'                  => 'secret_key',
            'value'                => $value,
        ]);

        // Value should be encrypted in the database
        $this->assertNotEquals($value, $deploymentCommitData->getRawOriginal('value'));

        // Value should be decrypted when accessed
        $this->assertEquals($value, $deploymentCommitData->value);
    }

    /**
     * @test
     */
    public function commitCanHaveMultipleDataEntries(): void
    {
        // Create 3 additional data entries
        $data = DeploymentCommitData::factory()->count(3)->create([
            'deployment_commit_id' => $this->deploymentCommit->id,
        ]);

        // Should have 4 total entries (1 from setUp + 3 new ones)
        $this->assertCount(4, $this->deploymentCommit->commitData);

        // Verify all IDs are present
        $expectedIds = collect([$this->deploymentCommitData->id])->merge($data->pluck('id'));
        $this->assertEquals($expectedIds->sort()->values(), $this->deploymentCommit->commitData->pluck('id')->sort()->values());
    }

    /**
     * @test
     */
    public function sameKeyCanBeUsedInMultipleCommits(): void
    {
        $commit2 = DeploymentCommit::factory()->create([
            'deployment_id' => $this->deployment->id,
            'hash'          => 'def456',
            'message'       => 'Test commit 2',
        ]);

        $data1 = DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $this->deploymentCommit->id,
            'key'                  => 'shared_key',
            'value'                => 'value1',
        ]);

        $data2 = DeploymentCommitData::factory()->create([
            'deployment_commit_id' => $commit2->id,
            'key'                  => 'shared_key',
            'value'                => 'value2',
        ]);

        $this->assertEquals($this->deploymentCommit->id, $data1->commit->id);
        $this->assertEquals($commit2->id, $data2->commit->id);
        $this->assertNotEquals($data1->value, $data2->value);
    }
}
