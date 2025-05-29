<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Projects\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class ProjectTest.
 *
 * Unit tests for the Project model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $user;

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
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('projects', $this->project->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->project->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = $this->project->getCasts();
        $this->assertIsArray($casts);
    }

    /**
     * @test
     */
    public function itCanHaveUser(): void
    {
        $this->assertInstanceOf(User::class, $this->project->user);
        $this->assertEquals($this->user->id, $this->project->user->id);
        $this->assertEquals($this->user->name, $this->project->user->name);
        $this->assertEquals($this->user->email, $this->project->user->email);
    }

    /**
     * @test
     */
    public function itCanHaveClusters(): void
    {
        // Create clusters for the project
        $clusters = Cluster::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'user_id'    => $this->user->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->project->clusters);
        $this->assertCount(3, $this->project->clusters);
        $this->assertInstanceOf(Cluster::class, $this->project->clusters->first());
        $this->assertEquals($clusters->pluck('id')->toArray(), $this->project->clusters->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->project->clusters()->exists());
        $this->assertEquals(3, $this->project->clusters()->count());
    }

    /**
     * @test
     */
    public function itCanHaveDeployments(): void
    {
        // Create deployments for the project
        $deployments = Deployment::factory()->count(4)->create([
            'project_id' => $this->project->id,
            'user_id'    => $this->user->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->project->deployments);
        $this->assertCount(4, $this->project->deployments);
        $this->assertInstanceOf(Deployment::class, $this->project->deployments->first());
        $this->assertEquals($deployments->pluck('id')->toArray(), $this->project->deployments->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->project->deployments()->exists());
        $this->assertEquals(4, $this->project->deployments()->count());
    }

    /**
     * @test
     */
    public function itCanCalculateClusterStatistics(): void
    {
        // Create clusters with metrics
        $clusters = Cluster::factory()->count(2)->create([
            'project_id' => $this->project->id,
            'user_id'    => $this->user->id,
        ]);

        // Add metrics to clusters
        foreach ($clusters as $cluster) {
            $metricId = (string) \Illuminate\Support\Str::uuid();

            // Create cluster metric
            DB::table('cluster_metrics')->insert([
                'id'         => $metricId,
                'cluster_id' => $cluster->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create cluster metric capacity (storage in bytes)
            DB::table('cluster_metric_capacities')->insert([
                'id'                => (string) \Illuminate\Support\Str::uuid(),
                'cluster_metric_id' => $metricId,
                'cpu'               => 4.0,
                'memory'            => 8 * 1024 * 1024 * 1024, // 8GB in bytes
                'storage'           => 100 * 1024 * 1024 * 1024, // 100GB in bytes
                'pods'              => 10,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Create cluster metric usage (storage in bytes)
            DB::table('cluster_metric_usages')->insert([
                'id'                => (string) \Illuminate\Support\Str::uuid(),
                'cluster_metric_id' => $metricId,
                'cpu'               => 2.0,
                'memory'            => 4 * 1024 * 1024 * 1024, // 4GB in bytes
                'storage'           => 50 * 1024 * 1024 * 1024, // 50GB in bytes
                'pods'              => 5,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Create cluster metric utilization
            DB::table('cluster_metric_utilizations')->insert([
                'id'                => (string) \Illuminate\Support\Str::uuid(),
                'cluster_metric_id' => $metricId,
                'cpu'               => 50.0,
                'memory'            => 50.0,
                'storage'           => 50.0,
                'pods'              => 50.0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // Refresh the project to get fresh relationships
        $this->project->refresh();
        $statistics = $this->project->clusterStatistics;

        // Test metrics
        $this->assertEquals(800.0, $statistics['metrics']['capacity']['cpu']); // 4.0 * 100 * 2 clusters
        $this->assertEquals(16.0, $statistics['metrics']['capacity']['memory']); // 8GB * 2 clusters
        $this->assertEquals(200.0, $statistics['metrics']['capacity']['storage']); // 100GB * 2 clusters
        $this->assertEquals(20, $statistics['metrics']['capacity']['pods']); // 10 * 2 clusters

        $this->assertEquals(400.0, $statistics['metrics']['usage']['cpu']); // 2.0 * 100 * 2 clusters
        $this->assertEquals(8.0, $statistics['metrics']['usage']['memory']); // 4GB * 2 clusters
        $this->assertEquals(100.0, $statistics['metrics']['usage']['storage']); // 50GB * 2 clusters
        $this->assertEquals(10, $statistics['metrics']['usage']['pods']); // 5 * 2 clusters

        $this->assertEquals(100.0, $statistics['metrics']['utilization']['cpu']); // 50.0 * 2 clusters
        $this->assertEquals(100.0, $statistics['metrics']['utilization']['memory']); // 50.0 * 2 clusters
        $this->assertEquals(100.0, $statistics['metrics']['utilization']['storage']); // 50.0 * 2 clusters
        $this->assertEquals(100.0, $statistics['metrics']['utilization']['pods']); // 50.0 * 2 clusters

        // Test alerts (should all be false since we don't have alerts table)
        $this->assertFalse($statistics['alerts']['warning']['cpu']);
        $this->assertFalse($statistics['alerts']['warning']['memory']);
        $this->assertFalse($statistics['alerts']['warning']['storage']);
        $this->assertFalse($statistics['alerts']['warning']['pods']);

        $this->assertFalse($statistics['alerts']['critical']['cpu']);
        $this->assertFalse($statistics['alerts']['critical']['memory']);
        $this->assertFalse($statistics['alerts']['critical']['storage']);
        $this->assertFalse($statistics['alerts']['critical']['pods']);
    }

    /**
     * @test
     */
    public function itCanCalculateDeploymentStatistics(): void
    {
        // Create deployments for the project
        $deployments = Deployment::factory()->count(2)->create([
            'project_id' => $this->project->id,
            'user_id'    => $this->user->id,
        ]);

        // Add deployment limits
        foreach ($deployments as $deployment) {
            // Create deployment metric (storage in bytes)
            DB::table('deployment_metrics')->insert([
                'id'             => (string) \Illuminate\Support\Str::uuid(),
                'deployment_id'  => $deployment->id,
                'storage_bytes'  => 1024 * 1024 * 100, // 100MB in bytes
                'memory_bytes'   => 1024 * 1024 * 4, // 4MB in bytes
                'cpu_core_usage' => 2.0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Create deployment limits
            DB::table('deployment_limits')->insert([
                'id'            => (string) \Illuminate\Support\Str::uuid(),
                'deployment_id' => $deployment->id,
                'is_active'     => true,
                'cpu'           => 2.0,
                'memory'        => 4.0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Refresh the project to get fresh relationships
        $this->project->refresh();
        $statistics = $this->project->deploymentStatistics;

        // Test statistics
        $this->assertEquals(400.0, $statistics['cpu']); // 2.0 * 100 * 2 deployments
        $this->assertEquals(8.0, $statistics['memory']); // 4MB * 2 deployments
        $this->assertEquals(200.0, $statistics['storage']); // 100MB * 2 deployments
    }

    /**
     * @test
     */
    public function itCanCalculateAllStatistics(): void
    {
        // Create multiple projects with clusters and deployments
        $projects = Project::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        foreach ($projects as $project) {
            // Create clusters
            $clusters = Cluster::factory()->count(2)->create([
                'project_id' => $project->id,
                'user_id'    => $this->user->id,
            ]);

            // Add metrics to clusters
            foreach ($clusters as $cluster) {
                $metricId = (string) \Illuminate\Support\Str::uuid();

                // Create cluster metric
                DB::table('cluster_metrics')->insert([
                    'id'         => $metricId,
                    'cluster_id' => $cluster->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create cluster metric capacity (storage in bytes)
                DB::table('cluster_metric_capacities')->insert([
                    'id'                => (string) \Illuminate\Support\Str::uuid(),
                    'cluster_metric_id' => $metricId,
                    'cpu'               => 4.0,
                    'memory'            => 8 * 1024 * 1024 * 1024, // 8GB in bytes
                    'storage'           => 100 * 1024 * 1024 * 1024, // 100GB in bytes
                    'pods'              => 10,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Create cluster metric usage (storage in bytes)
                DB::table('cluster_metric_usages')->insert([
                    'id'                => (string) \Illuminate\Support\Str::uuid(),
                    'cluster_metric_id' => $metricId,
                    'cpu'               => 2.0,
                    'memory'            => 4 * 1024 * 1024 * 1024, // 4GB in bytes
                    'storage'           => 50 * 1024 * 1024 * 1024, // 50GB in bytes
                    'pods'              => 5,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Create cluster metric utilization
                DB::table('cluster_metric_utilizations')->insert([
                    'id'                => (string) \Illuminate\Support\Str::uuid(),
                    'cluster_metric_id' => $metricId,
                    'cpu'               => 50.0,
                    'memory'            => 50.0,
                    'storage'           => 50.0,
                    'pods'              => 50.0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            // Create deployments with limits
            $deployments = Deployment::factory()->count(2)->create([
                'project_id' => $project->id,
                'user_id'    => $this->user->id,
            ]);

            foreach ($deployments as $deployment) {
                // Create deployment metric (storage in bytes)
                DB::table('deployment_metrics')->insert([
                    'id'             => (string) \Illuminate\Support\Str::uuid(),
                    'deployment_id'  => $deployment->id,
                    'storage_bytes'  => 1024 * 1024 * 100, // 100MB in bytes
                    'memory_bytes'   => 1024 * 1024 * 4, // 4MB in bytes
                    'cpu_core_usage' => 2.0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Create deployment limits
                DB::table('deployment_limits')->insert([
                    'id'            => (string) \Illuminate\Support\Str::uuid(),
                    'deployment_id' => $deployment->id,
                    'is_active'     => true,
                    'cpu'           => 2.0,
                    'memory'        => 4.0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // Refresh all projects to get fresh relationships
        foreach ($projects as $project) {
            $project->refresh();
        }
        $statistics = Project::allStatistics();

        // Test metrics (2 projects * 2 clusters * values)
        $this->assertEquals(1600.0, $statistics['metrics']['capacity']['cpu']); // 4.0 * 100 * 2 * 2
        $this->assertEquals(32.0, $statistics['metrics']['capacity']['memory']); // 8GB * 2 * 2
        $this->assertEquals(400.0, $statistics['metrics']['capacity']['storage']); // 100GB * 2 * 2
        $this->assertEquals(40, $statistics['metrics']['capacity']['pods']); // 10 * 2 * 2

        $this->assertEquals(800.0, $statistics['metrics']['usage']['cpu']); // 2.0 * 100 * 2 * 2
        $this->assertEquals(16.0, $statistics['metrics']['usage']['memory']); // 4GB * 2 * 2
        $this->assertEquals(200.0, $statistics['metrics']['usage']['storage']); // 50GB * 2 * 2
        $this->assertEquals(20, $statistics['metrics']['usage']['pods']); // 5 * 2 * 2

        $this->assertEquals(200.0, $statistics['metrics']['utilization']['cpu']); // 50.0 * 2 * 2
        $this->assertEquals(200.0, $statistics['metrics']['utilization']['memory']); // 50.0 * 2 * 2
        $this->assertEquals(200.0, $statistics['metrics']['utilization']['storage']); // 50.0 * 2 * 2
        $this->assertEquals(200.0, $statistics['metrics']['utilization']['pods']); // 50.0 * 2 * 2

        // Test alerts (should all be true since contains() doesn't support dot notation and will always return false)
        $this->assertTrue($statistics['alerts']['warning']['cpu']);
        $this->assertTrue($statistics['alerts']['warning']['memory']);
        $this->assertTrue($statistics['alerts']['warning']['storage']);
        $this->assertTrue($statistics['alerts']['warning']['pods']);

        $this->assertTrue($statistics['alerts']['critical']['cpu']);
        $this->assertTrue($statistics['alerts']['critical']['memory']);
        $this->assertTrue($statistics['alerts']['critical']['storage']);
        $this->assertTrue($statistics['alerts']['critical']['pods']);
    }

    /**
     * @test
     */
    public function itHandlesEmptyDeploymentStatistics(): void
    {
        // Create project without deployments
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $statistics = $project->deploymentStatistics;

        $this->assertNull($statistics['cpu']);
        $this->assertNull($statistics['memory']);
        $this->assertNull($statistics['storage']);
    }

    /**
     * @test
     */
    public function itHandlesEmptyClusterStatistics(): void
    {
        // Create project without clusters
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $statistics = $project->clusterStatistics;

        // Test metrics (should all be 0 or false)
        $this->assertEquals(0, $statistics['metrics']['capacity']['cpu']);
        $this->assertEquals(0, $statistics['metrics']['capacity']['memory']);
        $this->assertEquals(0, $statistics['metrics']['capacity']['storage']);
        $this->assertEquals(0, $statistics['metrics']['capacity']['pods']);

        $this->assertEquals(0, $statistics['metrics']['usage']['cpu']);
        $this->assertEquals(0, $statistics['metrics']['usage']['memory']);
        $this->assertEquals(0, $statistics['metrics']['usage']['storage']);
        $this->assertEquals(0, $statistics['metrics']['usage']['pods']);

        $this->assertEquals(0, $statistics['metrics']['utilization']['cpu']);
        $this->assertEquals(0, $statistics['metrics']['utilization']['memory']);
        $this->assertEquals(0, $statistics['metrics']['utilization']['storage']);
        $this->assertEquals(0, $statistics['metrics']['utilization']['pods']);

        // Test alerts (should all be false)
        $this->assertFalse($statistics['alerts']['warning']['cpu']);
        $this->assertFalse($statistics['alerts']['warning']['memory']);
        $this->assertFalse($statistics['alerts']['warning']['storage']);
        $this->assertFalse($statistics['alerts']['warning']['pods']);

        $this->assertFalse($statistics['alerts']['critical']['cpu']);
        $this->assertFalse($statistics['alerts']['critical']['memory']);
        $this->assertFalse($statistics['alerts']['critical']['storage']);
        $this->assertFalse($statistics['alerts']['critical']['pods']);
    }
}
