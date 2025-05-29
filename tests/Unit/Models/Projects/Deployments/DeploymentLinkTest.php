<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentLink;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DeploymentLinkTest.
 *
 * Unit tests for the DeploymentLink model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class DeploymentLinkTest extends TestCase
{
    use RefreshDatabase;

    private DeploymentLink $deploymentLink;

    private Deployment $sourceDeployment;

    private Deployment $targetDeployment;

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

        // Create source deployment
        $this->sourceDeployment = Deployment::factory()->create([
            'user_id'     => $this->user->id,
            'project_id'  => $this->project->id,
            'template_id' => $this->template->id,
            'cluster_id'  => $this->cluster->id,
            'name'        => 'Source Deployment',
        ]);

        // Create target deployment
        $this->targetDeployment = Deployment::factory()->create([
            'user_id'     => $this->user->id,
            'project_id'  => $this->project->id,
            'template_id' => $this->template->id,
            'cluster_id'  => $this->cluster->id,
            'name'        => 'Target Deployment',
        ]);

        // Create test deployment link
        $this->deploymentLink = DeploymentLink::factory()->create([
            'source_deployment_id' => $this->sourceDeployment->id,
            'target_deployment_id' => $this->targetDeployment->id,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('deployment_links', $this->deploymentLink->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->deploymentLink->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->deploymentLink->id);
        $this->assertEquals(36, strlen($this->deploymentLink->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->deploymentLink->deleted_at);
        $this->deploymentLink->delete();
        $this->assertNotNull($this->deploymentLink->deleted_at);
        $this->assertSoftDeleted($this->deploymentLink);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->deploymentLink->delete();
        $this->assertSoftDeleted($this->deploymentLink);
        $this->deploymentLink->restore();
        $this->assertNull($this->deploymentLink->deleted_at);
    }

    /**
     * @test
     */
    public function itHasSourceDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentLink->source());
        $this->assertInstanceOf(Deployment::class, $this->deploymentLink->source);
        $this->assertEquals($this->sourceDeployment->id, $this->deploymentLink->source->id);
        $this->assertEquals($this->sourceDeployment->name, $this->deploymentLink->source->name);
    }

    /**
     * @test
     */
    public function itHasTargetDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->deploymentLink->target());
        $this->assertInstanceOf(Deployment::class, $this->deploymentLink->target);
        $this->assertEquals($this->targetDeployment->id, $this->deploymentLink->target->id);
        $this->assertEquals($this->targetDeployment->name, $this->deploymentLink->target->name);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $deploymentLink = DeploymentLink::factory()->create([
            'source_deployment_id' => $this->sourceDeployment->id,
            'target_deployment_id' => $this->targetDeployment->id,
        ]);

        $this->assertInstanceOf(DeploymentLink::class, $deploymentLink);
        $this->assertEquals($this->sourceDeployment->id, $deploymentLink->source_deployment_id);
        $this->assertEquals($this->targetDeployment->id, $deploymentLink->target_deployment_id);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $deploymentLinks = DeploymentLink::factory()->count(3)->create([
            'source_deployment_id' => $this->sourceDeployment->id,
        ]);

        $this->assertCount(3, $deploymentLinks);

        foreach ($deploymentLinks as $link) {
            $this->assertInstanceOf(DeploymentLink::class, $link);
            $this->assertEquals($this->sourceDeployment->id, $link->source_deployment_id);
        }
    }

    /**
     * @test
     */
    public function itCanLinkDeploymentToItself(): void
    {
        $deploymentLink = DeploymentLink::factory()->create([
            'source_deployment_id' => $this->sourceDeployment->id,
            'target_deployment_id' => $this->sourceDeployment->id,
        ]);

        $this->assertInstanceOf(DeploymentLink::class, $deploymentLink);
        $this->assertEquals($this->sourceDeployment->id, $deploymentLink->source_deployment_id);
        $this->assertEquals($this->sourceDeployment->id, $deploymentLink->target_deployment_id);
    }
}
