<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Deployments;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\ReservedPort;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class ReservedPortTest.
 *
 * Unit tests for the ReservedPort model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ReservedPortTest extends TestCase
{
    use RefreshDatabase;

    private ReservedPort $reservedPort;

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

        // Create test reserved port
        $this->reservedPort = ReservedPort::factory()->create([
            'deployment_id' => $this->deployment->id,
            'group'         => 'services',
            'claim'         => 'test-claim',
            'port'          => 8080,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('reserved_ports', $this->reservedPort->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->reservedPort->getGuarded());
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->reservedPort->id);
        $this->assertEquals(36, strlen($this->reservedPort->id));
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->reservedPort->deleted_at);
        $this->reservedPort->delete();
        $this->assertNotNull($this->reservedPort->deleted_at);
        $this->assertSoftDeleted($this->reservedPort);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->reservedPort->delete();
        $this->assertSoftDeleted($this->reservedPort);
        $this->reservedPort->restore();
        $this->assertNull($this->reservedPort->deleted_at);
    }

    /**
     * @test
     */
    public function itHasDeploymentRelationship(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->reservedPort->deployment());
        $this->assertInstanceOf(Deployment::class, $this->reservedPort->deployment);
        $this->assertEquals($this->deployment->id, $this->reservedPort->deployment->id);
        $this->assertEquals($this->deployment->name, $this->reservedPort->deployment->name);
    }

    /**
     * @test
     */
    public function itCanGetDisallowedPorts(): void
    {
        // Create some reserved ports
        ReservedPort::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
            'group'         => 'services',
        ]);

        // Get disallowed ports
        $disallowed = ReservedPort::disallowed('services');
        $this->assertInstanceOf(Collection::class, $disallowed);
        $this->assertCount(4, $disallowed); // 3 new + 1 from setUp
        $this->assertContains($this->reservedPort->port, $disallowed);

        // Test with different group
        $disallowed = ReservedPort::disallowed('ingress');
        $this->assertInstanceOf(Collection::class, $disallowed);
        $this->assertCount(0, $disallowed);
    }

    /**
     * @test
     */
    public function itCanGetRandomAvailablePort(): void
    {
        // Get a random port
        $port = ReservedPort::random('services');
        $this->assertIsInt($port);
        $this->assertGreaterThanOrEqual(49152, $port);
        $this->assertLessThanOrEqual(65535, $port);

        // Test with custom disallowed ports
        $disallowed = collect([49152, 49153, 49154]);
        $port       = ReservedPort::random('services', $disallowed);
        $this->assertIsInt($port);
        $this->assertGreaterThanOrEqual(49155, $port);
        $this->assertLessThanOrEqual(65535, $port);
        $this->assertNotContains($port, $disallowed);
    }

    /**
     * @test
     */
    public function itReturnsNullWhenNoPortsAvailable(): void
    {
        // Create enough reserved ports to fill the range
        $ports = range(49152, 65535);

        foreach ($ports as $port) {
            ReservedPort::factory()->create([
                'deployment_id' => $this->deployment->id,
                'group'         => 'services',
                'port'          => $port,
            ]);
        }

        // Try to get a random port
        $port = ReservedPort::random('services');
        $this->assertNull($port);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithFactory(): void
    {
        $reservedPort = ReservedPort::factory()->create([
            'deployment_id' => $this->deployment->id,
            'group'         => 'ingress',
            'claim'         => 'factory-claim',
            'port'          => 9090,
        ]);

        $this->assertInstanceOf(ReservedPort::class, $reservedPort);
        $this->assertEquals('ingress', $reservedPort->group);
        $this->assertEquals('factory-claim', $reservedPort->claim);
        $this->assertEquals(9090, $reservedPort->port);
        $this->assertEquals($this->deployment->id, $reservedPort->deployment_id);
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithMultipleFactoryInstances(): void
    {
        $reservedPorts = ReservedPort::factory()->count(3)->create([
            'deployment_id' => $this->deployment->id,
            'group'         => 'services',
        ]);

        $this->assertCount(3, $reservedPorts);

        foreach ($reservedPorts as $port) {
            $this->assertInstanceOf(ReservedPort::class, $port);
            $this->assertEquals($this->deployment->id, $port->deployment_id);
            $this->assertEquals('services', $port->group);
        }
    }

    /**
     * @test
     */
    public function itCanBeCreatedWithoutClaim(): void
    {
        $reservedPort = ReservedPort::factory()->create([
            'deployment_id' => $this->deployment->id,
            'group'         => 'services',
            'claim'         => null,
        ]);

        $this->assertInstanceOf(ReservedPort::class, $reservedPort);
        $this->assertNull($reservedPort->claim);
    }
}
