<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentMetric;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DeploymentMetricTest.
 *
 * Unit tests for the DeploymentMetric model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentMetricTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentMetric $deploymentMetric;

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

        // Create test deployment metric
        $this->deploymentMetric = DeploymentMetric::factory()->create([
            'deployment_id'  => $this->deployment->id,
            'cpu_core_usage' => 0.5,
            'memory_bytes'   => 1024 * 1024 * 512, // 512MB
            'storage_bytes'  => 1024 * 1024 * 1024, // 1GB
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployment_metrics', $this->deploymentMetric->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deploymentMetric->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->deploymentMetric->id);
        $this->assertEquals(36, strlen($this->deploymentMetric->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->deploymentMetric->deleted_at);
        $this->deploymentMetric->delete();
        $this->assertNotNull($this->deploymentMetric->deleted_at);
        $this->assertSoftDeleted($this->deploymentMetric);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->deploymentMetric->delete();
        $this->assertSoftDeleted($this->deploymentMetric);
        $this->deploymentMetric->restore();
        $this->assertNull($this->deploymentMetric->deleted_at);
    }

    /**
     * @test
     */
    public function itHasDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentMetric->deployment());
        $this->assertInstanceOf(Deployment::class, $this->deploymentMetric->deployment);
        $this->assertEquals($this->deployment->id, $this->deploymentMetric->deployment->id);
        $this->assertEquals($this->deployment->name, $this->deploymentMetric->deployment->name);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $deploymentMetric = DeploymentMetric::factory()->create([
            'deployment_id'  => $this->deployment->id,
            'cpu_core_usage' => 0.75,
            'memory_bytes'   => 1024 * 1024 * 256, // 256MB
            'storage_bytes'  => 1024 * 1024 * 512, // 512MB
        ]);

        $this->assertInstanceOf(DeploymentMetric::class, $deploymentMetric);
        $this->assertEquals($this->deployment->id, $deploymentMetric->deployment_id);
        $this->assertEquals(0.75, $deploymentMetric->cpu_core_usage);
        $this->assertEquals(1024 * 1024 * 256, $deploymentMetric->memory_bytes);
        $this->assertEquals(1024 * 1024 * 512, $deploymentMetric->storage_bytes);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $deploymentMetrics = DeploymentMetric::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        $this->assertCount(3, $deploymentMetrics);

        foreach ($deploymentMetrics as $metric) {
            $this->assertInstanceOf(DeploymentMetric::class, $metric);
            $this->assertEquals($this->deployment->id, $metric->deployment_id);
            $this->assertIsFloat($metric->cpu_core_usage);
            $this->assertIsInt($metric->memory_bytes);
            $this->assertIsInt($metric->storage_bytes);
            $this->assertGreaterThanOrEqual(0, $metric->cpu_core_usage);
            $this->assertLessThanOrEqual(1, $metric->cpu_core_usage);
            $this->assertGreaterThanOrEqual(1024 * 1024, $metric->memory_bytes);
            $this->assertLessThanOrEqual(1024 * 1024 * 1024, $metric->memory_bytes);
            $this->assertGreaterThanOrEqual(1024 * 1024, $metric->storage_bytes);
            $this->assertLessThanOrEqual(1024 * 1024 * 1024, $metric->storage_bytes);
        }
    }

    /**
     * @test
     */
    public function itHasValidMetricValues(): void
    {
        $this->assertIsFloat($this->deploymentMetric->cpu_core_usage);
        $this->assertIsInt($this->deploymentMetric->memory_bytes);
        $this->assertIsInt($this->deploymentMetric->storage_bytes);
        $this->assertEquals(0.5, $this->deploymentMetric->cpu_core_usage);
        $this->assertEquals(1024 * 1024 * 512, $this->deploymentMetric->memory_bytes);
        $this->assertEquals(1024 * 1024 * 1024, $this->deploymentMetric->storage_bytes);
    }

    /**
     * @test
     */
    public function deploymentCanHaveMetrics(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->deployment->metrics());
        $this->assertCount(1, $this->deployment->metrics);
        $this->assertInstanceOf(DeploymentMetric::class, $this->deployment->metrics->first());
        $this->assertEquals($this->deploymentMetric->id, $this->deployment->metrics->first()->id);
    }

    /**
     * @test
     */
    public function deploymentCanHaveMultipleMetrics(): void
    {
        // Create additional metrics
        DeploymentMetric::factory()->count(2)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        $this->assertCount(3, $this->deployment->metrics);
        $this->assertContainsOnlyInstancesOf(DeploymentMetric::class, $this->deployment->metrics);
    }

    /**
     * @test
     */
    public function deploymentStatisticsAttributeReturnsLatestMetric(): void
    {
        // Create a newer metric with different values
        $newMetric = DeploymentMetric::factory()->create([
            'deployment_id'  => $this->deployment->id,
            'cpu_core_usage' => 0.8,
            'memory_bytes'   => 1024 * 1024 * 768, // 768MB
            'storage_bytes'  => 1024 * 1024 * 1536, // 1.5GB
            'created_at'     => now()->addHour(),
        ]);

        $statistics = $this->deployment->statistics;

        $this->assertIsArray($statistics);
        $this->assertEquals(80, $statistics['cpu']); // 0.8 * 100
        $this->assertEquals(768, $statistics['memory']); // 768MB
        $this->assertEquals(1536, $statistics['storage']); // 1.5GB
    }

    /**
     * @test
     */
    public function deploymentStatisticsAttributeReturnsNullWhenNoMetrics(): void
    {
        // Delete all metrics
        $this->deployment->metrics()->delete();

        $this->assertNull($this->deployment->statistics);
    }
}
