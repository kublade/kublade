<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Kubernetes\Resources\Ns;
use App\Models\Kubernetes\Resources\Pod;
use App\Models\Kubernetes\Resources\PodLog;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentCommit;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Deployments\DeploymentLimit;
use App\Models\Projects\Deployments\DeploymentLink;
use App\Models\Projects\Deployments\DeploymentMetric;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Deployments\ReservedPort;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection as SupportCollection;
use Tests\TestCase;

/**
 * Class DeploymentTest.
 *
 * Unit tests for the Deployment model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    private Deployment $deployment;

    private User $user;

    private Project $project;

    private Template $template;

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
            'uuid'        => '123e4567-e89b-12d3-a456-426614174000',
            'paused'      => false,
            'update'      => false,
            'delete'      => false,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployments', $this->deployment->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deployment->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = $this->deployment->getCasts();
        $this->assertIsArray($casts);
        $this->assertArrayHasKey('paused', $casts);
        $this->assertArrayHasKey('update', $casts);
        $this->assertArrayHasKey('delete', $casts);
        $this->assertArrayHasKey('deployed_at', $casts);
        $this->assertArrayHasKey('deployment_updated_at', $casts);
        $this->assertArrayHasKey('creation_dispatched_at', $casts);
        $this->assertArrayHasKey('update_dispatched_at', $casts);
        $this->assertArrayHasKey('deletion_dispatched_at', $casts);
        $this->assertEquals('boolean', $casts['paused']);
        $this->assertEquals('boolean', $casts['update']);
        $this->assertEquals('boolean', $casts['delete']);
        $this->assertEquals('datetime', $casts['deployed_at']);
        $this->assertEquals('datetime', $casts['deployment_updated_at']);
        $this->assertEquals('datetime', $casts['creation_dispatched_at']);
        $this->assertEquals('datetime', $casts['update_dispatched_at']);
        $this->assertEquals('datetime', $casts['deletion_dispatched_at']);
    }

    /**
     * @test
     */
    public function itCanHaveProject(): void
    {
        $this->assertInstanceOf(Project::class, $this->deployment->project);
        $this->assertEquals($this->project->id, $this->deployment->project->id);
        $this->assertEquals($this->project->name, $this->deployment->project->name);
    }

    /**
     * @test
     */
    public function itCanHaveTemplate(): void
    {
        $this->assertInstanceOf(Template::class, $this->deployment->template);
        $this->assertEquals($this->template->id, $this->deployment->template->id);
        $this->assertEquals($this->template->name, $this->deployment->template->name);
    }

    /**
     * @test
     */
    public function itCanHaveCluster(): void
    {
        $this->assertInstanceOf(Cluster::class, $this->deployment->cluster);
        $this->assertEquals($this->cluster->id, $this->deployment->cluster->id);
    }

    /**
     * @test
     */
    public function itCanHaveMetrics(): void
    {
        // Create deployment metrics
        $metrics = DeploymentMetric::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->metrics);
        $this->assertCount(3, $this->deployment->metrics);
        $this->assertInstanceOf(DeploymentMetric::class, $this->deployment->metrics->first());
        $this->assertEquals($metrics->pluck('id')->toArray(), $this->deployment->metrics->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->metrics()->exists());
        $this->assertEquals(3, $this->deployment->metrics()->count());
    }

    /**
     * @test
     */
    public function itCanHaveDeploymentData(): void
    {
        // Create deployment data
        $data = DeploymentData::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->deploymentData);
        $this->assertCount(3, $this->deployment->deploymentData);
        $this->assertInstanceOf(DeploymentData::class, $this->deployment->deploymentData->first());
        $this->assertEquals($data->pluck('id')->toArray(), $this->deployment->deploymentData->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->deploymentData()->exists());
        $this->assertEquals(3, $this->deployment->deploymentData()->count());
    }

    /**
     * @test
     */
    public function itCanHaveDeploymentSecretData(): void
    {
        // Create deployment secret data
        $secretData = DeploymentSecretData::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->deploymentSecretData);
        $this->assertCount(3, $this->deployment->deploymentSecretData);
        $this->assertInstanceOf(DeploymentSecretData::class, $this->deployment->deploymentSecretData->first());
        $this->assertEquals($secretData->pluck('id')->toArray(), $this->deployment->deploymentSecretData->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->deploymentSecretData()->exists());
        $this->assertEquals(3, $this->deployment->deploymentSecretData()->count());
    }

    /**
     * @test
     */
    public function itCanHaveNamespaces(): void
    {
        // Create namespaces
        $namespaces = Ns::factory()->count(3)->create([
            'cluster_id' => $this->cluster->id,
        ]);

        // Set namespace_id on deployment
        $this->deployment->namespace_id = $namespaces->first()->id;
        $this->deployment->save();

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->namespaces);
        $this->assertCount(1, $this->deployment->namespaces);
        $this->assertInstanceOf(Ns::class, $this->deployment->namespaces->first());
        $this->assertEquals($namespaces->first()->id, $this->deployment->namespaces->first()->id);

        // Test relationship methods
        $this->assertTrue($this->deployment->namespaces()->exists());
        $this->assertEquals(1, $this->deployment->namespaces()->count());
    }

    /**
     * @test
     */
    public function itCanHaveLimit(): void
    {
        // Create deployment limit
        $limit = DeploymentLimit::factory()->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(DeploymentLimit::class, $this->deployment->limit);
        $this->assertEquals($limit->id, $this->deployment->limit->id);
    }

    /**
     * @test
     */
    public function itCanHavePorts(): void
    {
        // Create reserved ports
        $ports = ReservedPort::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->ports);
        $this->assertCount(3, $this->deployment->ports);
        $this->assertInstanceOf(ReservedPort::class, $this->deployment->ports->first());
        $this->assertEquals($ports->pluck('id')->toArray(), $this->deployment->ports->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->ports()->exists());
        $this->assertEquals(3, $this->deployment->ports()->count());
    }

    /**
     * @test
     */
    public function itCanHaveIngressAsSource(): void
    {
        // Create deployment links as source
        $links = DeploymentLink::factory()->count(3)->create([
            'source_deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->ingressAsSource);
        $this->assertCount(3, $this->deployment->ingressAsSource);
        $this->assertInstanceOf(DeploymentLink::class, $this->deployment->ingressAsSource->first());
        $this->assertEquals($links->pluck('id')->toArray(), $this->deployment->ingressAsSource->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->ingressAsSource()->exists());
        $this->assertEquals(3, $this->deployment->ingressAsSource()->count());
    }

    /**
     * @test
     */
    public function itCanHaveIngressAsTarget(): void
    {
        // Create deployment links as target
        $links = DeploymentLink::factory()->count(3)->create([
            'target_deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->ingressAsTarget);
        $this->assertCount(3, $this->deployment->ingressAsTarget);
        $this->assertInstanceOf(DeploymentLink::class, $this->deployment->ingressAsTarget->first());
        $this->assertEquals($links->pluck('id')->toArray(), $this->deployment->ingressAsTarget->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->ingressAsTarget()->exists());
        $this->assertEquals(3, $this->deployment->ingressAsTarget()->count());
    }

    /**
     * @test
     */
    public function itCanHaveCommits(): void
    {
        // Create deployment commits
        $commits = DeploymentCommit::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->deployment->commits);
        $this->assertCount(3, $this->deployment->commits);
        $this->assertInstanceOf(DeploymentCommit::class, $this->deployment->commits->first());
        $this->assertEquals($commits->pluck('id')->toArray(), $this->deployment->commits->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->deployment->commits()->exists());
        $this->assertEquals(3, $this->deployment->commits()->count());
    }

    /**
     * @test
     */
    public function itCanGetStatus(): void
    {
        // Test pending status
        $this->assertEquals('<span class="badge bg-info">' . __('Pending') . '</span>', $this->deployment->status);

        // Test deployed status
        $this->deployment->deployed_at = now();
        $this->deployment->save();
        $this->assertEquals('<span class="badge bg-success">' . __('Deployed') . '</span>', $this->deployment->status);

        // Test updating status
        $this->deployment->update = true;
        $this->deployment->save();
        $this->assertEquals('<span class="badge bg-warning text-body">' . __('Updating') . '</span>', $this->deployment->status);

        // Test deleting status
        $this->deployment->delete = true;
        $this->deployment->save();
        $this->assertEquals('<span class="badge bg-danger">' . __('Deleting') . '</span>', $this->deployment->status);
    }

    /**
     * @test
     */
    public function itCanGetSimpleStatus(): void
    {
        // Test pending status
        $this->assertEquals(__('Pending'), $this->deployment->simpleStatus);

        // Test deployed status
        $this->deployment->deployed_at = now();
        $this->deployment->save();
        $this->assertEquals(__('Deployed'), $this->deployment->simpleStatus);

        // Test updating status
        $this->deployment->update = true;
        $this->deployment->save();
        $this->assertEquals(__('Updating'), $this->deployment->simpleStatus);

        // Test deleting status
        $this->deployment->delete = true;
        $this->deployment->save();
        $this->assertEquals(__('Deleting'), $this->deployment->simpleStatus);
    }

    /**
     * @test
     */
    public function itCanGetPath(): void
    {
        $expectedPath = $this->cluster->repositoryDeploymentPath . $this->deployment->uuid;
        $this->assertEquals($expectedPath, $this->deployment->path);
    }

    /**
     * @test
     */
    public function itCanGetStatistics(): void
    {
        // Create deployment metric
        $metric = DeploymentMetric::factory()->create([
            'deployment_id'  => $this->deployment->id,
            'cpu_core_usage' => 0.5,
            'memory_bytes'   => 1024 * 1024 * 100, // 100 MB
            'storage_bytes'  => 1024 * 1024 * 200, // 200 MB
        ]);

        // Test statistics
        $statistics = $this->deployment->statistics;
        $this->assertIsArray($statistics);
        $this->assertEquals(50, $statistics['cpu']); // 0.5 * 100
        $this->assertEquals(100, $statistics['memory']); // 100 MB
        $this->assertEquals(200, $statistics['storage']); // 200 MB

        // Test null statistics when no metrics exist
        $metric->delete();
        $this->assertNull($this->deployment->statistics);
    }

    /**
     * @test
     */
    public function itCanGetLogs(): void
    {
        // Create namespace
        $namespace = Ns::factory()->create([
            'cluster_id' => $this->cluster->id,
        ]);

        // Create pod
        $pod = Pod::factory()->create([
            'namespace_id' => $namespace->id,
        ]);

        // Update deployment with namespace_id
        $this->deployment->namespace_id = $namespace->id;
        $this->deployment->save();

        // Create pod logs
        $logs = PodLog::factory()->count(3)->create([
            'pod_id' => $pod->id,
        ]);

        // Test logs
        $this->assertInstanceOf(SupportCollection::class, $this->deployment->logs);
        $this->assertCount(3, $this->deployment->logs);
        $this->assertInstanceOf(PodLog::class, $this->deployment->logs->first());
        $this->assertEquals($logs->pluck('id')->toArray(), $this->deployment->logs->pluck('id')->toArray());

        // Test empty logs
        $logs->each->delete();
        $this->assertInstanceOf(SupportCollection::class, $this->deployment->logs);
        $this->assertCount(0, $this->deployment->logs);
    }

    /**
     * @test
     */
    public function itCanGetNetworkPolicies(): void
    {
        // Create deployment links as source and target
        $sourceLinks = DeploymentLink::factory()->count(2)->create([
            'source_deployment_id' => $this->deployment->id,
        ]);
        $targetLinks = DeploymentLink::factory()->count(2)->create([
            'target_deployment_id' => $this->deployment->id,
        ]);

        // Test network policies
        $policies = $this->deployment->networkPolicies;
        $this->assertInstanceOf(SupportCollection::class, $policies);
        $this->assertCount(4, $policies);
        $this->assertInstanceOf(DeploymentLink::class, $policies->first());

        // Verify all links are included
        $allLinkIds = array_merge($sourceLinks->pluck('id')->toArray(), $targetLinks->pluck('id')->toArray());
        $this->assertEqualsCanonicalizing($allLinkIds, $policies->pluck('id')->toArray());
    }
}
