<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentLimit;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DeploymentLimitTest.
 *
 * Unit tests for the DeploymentLimit model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentLimitTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentLimit $deploymentLimit;

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

        // Create test deployment limit
        $this->deploymentLimit = DeploymentLimit::factory()->create([
            'deployment_id' => $this->deployment->id,
            'is_active'     => true,
            'cpu'           => 1.5,
            'memory'        => 1024, // 1GB
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployment_limits', $this->deploymentLimit->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deploymentLimit->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->deploymentLimit->id);
        $this->assertEquals(36, strlen($this->deploymentLimit->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->deploymentLimit->deleted_at);
        $this->deploymentLimit->delete();
        $this->assertNotNull($this->deploymentLimit->deleted_at);
        $this->assertSoftDeleted($this->deploymentLimit);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->deploymentLimit->delete();
        $this->assertSoftDeleted($this->deploymentLimit);
        $this->deploymentLimit->restore();
        $this->assertNull($this->deploymentLimit->deleted_at);
    }

    /**
     * @test
     */
    public function itHasDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentLimit->order());
        $this->assertInstanceOf(Deployment::class, $this->deploymentLimit->order);
        $this->assertEquals($this->deployment->id, $this->deploymentLimit->order->id);
        $this->assertEquals($this->deployment->name, $this->deploymentLimit->order->name);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $deploymentLimit = DeploymentLimit::factory()->create([
            'deployment_id' => $this->deployment->id,
            'is_active'     => false,
            'cpu'           => 2.0,
            'memory'        => 2048, // 2GB
        ]);

        $this->assertInstanceOf(DeploymentLimit::class, $deploymentLimit);
        $this->assertEquals($this->deployment->id, $deploymentLimit->deployment_id);
        $this->assertFalse($deploymentLimit->is_active);
        $this->assertEquals(2.0, $deploymentLimit->cpu);
        $this->assertEquals(2048, $deploymentLimit->memory);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $deploymentLimits = DeploymentLimit::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        $this->assertCount(3, $deploymentLimits);

        foreach ($deploymentLimits as $limit) {
            $this->assertInstanceOf(DeploymentLimit::class, $limit);
            $this->assertEquals($this->deployment->id, $limit->deployment_id);
            $this->assertIsBool($limit->is_active);
            $this->assertIsFloat($limit->cpu);
            $this->assertIsInt($limit->memory);
            $this->assertGreaterThanOrEqual(0.1, $limit->cpu);
            $this->assertLessThanOrEqual(4.0, $limit->cpu);
            $this->assertGreaterThanOrEqual(256, $limit->memory);
            $this->assertLessThanOrEqual(4096, $limit->memory);
        }
    }

    /**
     * @test
     */
    public function itHasValidResourceLimits(): void
    {
        $this->assertIsBool($this->deploymentLimit->is_active);
        $this->assertIsFloat($this->deploymentLimit->cpu);
        $this->assertIsInt($this->deploymentLimit->memory);
        $this->assertTrue($this->deploymentLimit->is_active);
        $this->assertEquals(1.5, $this->deploymentLimit->cpu);
        $this->assertEquals(1024, $this->deploymentLimit->memory);
    }

    /**
     * @test
     */
    public function deploymentCanHaveLimit(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deployment->limit());
        $this->assertInstanceOf(DeploymentLimit::class, $this->deployment->limit);
        $this->assertEquals($this->deploymentLimit->id, $this->deployment->limit->id);
    }

    /**
     * @test
     */
    public function deploymentLimitIsUsedInTemplateRendering(): void
    {
        // Create a template file that uses the limit
        $templateFile = \App\Models\Projects\Templates\TemplateFile::factory()->create([
            'template_id' => $this->template->id,
            'content'     => '{{ $limits["enabled"] }} {{ $limits["cpu"] }} {{ $limits["memory"] }}',
        ]);

        // Interpret the template
        $rendered = $templateFile->interpret($this->deployment);

        // Check that the limit values are correctly rendered
        $this->assertEquals('true 1.5 1024', $rendered);
    }
}
