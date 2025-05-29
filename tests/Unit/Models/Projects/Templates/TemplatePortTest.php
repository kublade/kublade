<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Templates;

use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplatePort;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class TemplatePortTest.
 *
 * Unit tests for the TemplatePort model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplatePortTest extends TestCase
{
    use RefreshDatabase;

    private TemplatePort $port;

    private Template $template;

    private User $user;

    private Project $project;

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

        // Create test port
        $this->port = TemplatePort::factory()->create([
            'template_id'    => $this->template->id,
            'group'          => 'test-group',
            'claim'          => 'test-claim',
            'preferred_port' => 8080,
            'random'         => true,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('template_ports', $this->port->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->port->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = $this->port->getCasts();
        $this->assertIsArray($casts);
        $this->assertArrayHasKey('random', $casts);
        $this->assertEquals('boolean', $casts['random']);
    }

    /**
     * @test
     */
    public function itCanHaveTemplate(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->port->template());
        $this->assertInstanceOf(Template::class, $this->port->template);
        $this->assertEquals($this->template->id, $this->port->template->id);
        $this->assertEquals($this->template->name, $this->port->template->name);
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->port->deleted_at);
        $this->port->delete();
        $this->assertNotNull($this->port->deleted_at);
        $this->assertSoftDeleted($this->port);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->port->delete();
        $this->assertSoftDeleted($this->port);
        $this->port->restore();
        $this->assertNull($this->port->deleted_at);
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->port->id);
        $this->assertEquals(36, strlen($this->port->id));
    }

    /**
     * @test
     */
    public function itCanHaveGroupAndClaim(): void
    {
        $port = TemplatePort::factory()->create([
            'template_id' => $this->template->id,
            'group'       => 'custom-group',
            'claim'       => 'custom-claim',
        ]);

        $this->assertEquals('custom-group', $port->group);
        $this->assertEquals('custom-claim', $port->claim);
    }

    /**
     * @test
     */
    public function itCanHavePreferredPort(): void
    {
        $port = TemplatePort::factory()->create([
            'template_id'    => $this->template->id,
            'preferred_port' => 9090,
        ]);

        $this->assertEquals(9090, $port->preferred_port);
    }

    /**
     * @test
     */
    public function itCanHaveRandomPort(): void
    {
        $port = TemplatePort::factory()->create([
            'template_id' => $this->template->id,
            'random'      => false,
        ]);

        $this->assertFalse($port->random);
    }
}
