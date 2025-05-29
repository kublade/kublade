<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Templates;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Deployments\DeploymentData;
use App\Models\Projects\Deployments\DeploymentLimit;
use App\Models\Projects\Deployments\DeploymentSecretData;
use App\Models\Projects\Deployments\ReservedPort;
use App\Models\Projects\Projects\Project;
use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\Projects\Templates\TemplatePort;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class TemplateFileTest.
 *
 * Unit tests for the TemplateFile model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateFileTest extends TestCase
{
    use RefreshDatabase;

    private TemplateFile $file;

    private Template $template;

    private TemplateDirectory $directory;

    private User $user;

    private Project $project;

    private Cluster $cluster;

    private Deployment $deployment;

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

        // Create test directory
        $this->directory = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'name'        => 'Test Directory',
        ]);

        // Create test file
        $this->file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $this->directory->id,
            'name'                  => 'test.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'Test content',
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
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('template_files', $this->file->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->file->getGuarded());
    }

    /**
     * @test
     */
    public function itCanHaveTemplate(): void
    {
        $this->assertInstanceOf(Template::class, $this->file->template);
        $this->assertEquals($this->template->id, $this->file->template->id);
        $this->assertEquals($this->template->name, $this->file->template->name);
    }

    /**
     * @test
     */
    public function itCanHaveDirectory(): void
    {
        $this->assertInstanceOf(TemplateDirectory::class, $this->file->directory);
        $this->assertEquals($this->directory->id, $this->file->directory->id);
        $this->assertEquals($this->directory->name, $this->file->directory->name);
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->file->deleted_at);
        $this->file->delete();
        $this->assertNotNull($this->file->deleted_at);
        $this->assertSoftDeleted($this->file);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->file->delete();
        $this->assertSoftDeleted($this->file);
        $this->file->restore();
        $this->assertNull($this->file->deleted_at);
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->file->id);
        $this->assertEquals(36, strlen($this->file->id));
    }

    /**
     * @test
     */
    public function itCanGetTreeAttribute(): void
    {
        $tree = $this->file->tree;
        $this->assertIsObject($tree);
        $this->assertEquals('file', $tree->type);
        $this->assertEquals($this->file->id, $tree->id);
        $this->assertEquals($this->file->name, $tree->name);
        $this->assertEquals($this->file->mime_type, $tree->mime_type);
    }

    /**
     * @test
     */
    public function itCanGetFullTreeAttribute(): void
    {
        $tree = $this->file->fullTree;
        $this->assertIsObject($tree);
        $this->assertEquals('file', $tree->type);
        $this->assertInstanceOf(TemplateFile::class, $tree->object);
        $this->assertEquals($this->file->id, $tree->object->id);
    }

    /**
     * @test
     */
    public function itCanGetPathAttribute(): void
    {
        // Test file in directory
        $this->assertEquals('/Test Directory/test.txt', $this->file->path);

        // Test file without directory
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
            'name'                  => 'root.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'Root content',
        ]);

        $this->assertEquals('/root.txt', $file->path);
    }

    /**
     * @test
     */
    public function itCanInterpretFileWithPublicData(): void
    {
        // Create deployment data
        DeploymentData::factory()->create([
            'deployment_id' => $this->deployment->id,
            'key'           => 'test_key',
            'value'         => 'test_value',
        ]);

        // Create file with template
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
            'name'                  => 'template.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'Public data: {{ $data["test_key"] }}',
        ]);

        $interpreted = $file->interpret($this->deployment);
        $this->assertEquals('Public data: test_value', $interpreted);
    }

    /**
     * @test
     */
    public function itCanInterpretFileWithSecretData(): void
    {
        // Create deployment secret data
        DeploymentSecretData::factory()->create([
            'deployment_id' => $this->deployment->id,
            'key'           => 'secret_key',
            'value'         => 'secret_value',
        ]);

        // Create file with template
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
            'name'                  => 'template.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'Secret data: {{ $secret["secret_key"] }}',
        ]);

        $interpreted = $file->interpret($this->deployment);
        $this->assertEquals('Secret data: secret_value', $interpreted);
    }

    /**
     * @test
     */
    public function itCanInterpretFileWithPortClaims(): void
    {
        // Create template port with claim
        $port = TemplatePort::factory()->create([
            'template_id'    => $this->template->id,
            'group'          => 'test-group',
            'claim'          => 'test-claim',
            'preferred_port' => 8080,
        ]);

        // Create reserved port
        ReservedPort::factory()->create([
            'deployment_id' => $this->deployment->id,
            'group'         => 'test-group',
            'claim'         => 'test-claim',
            'port'          => 9090,
        ]);

        // Create file with template
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
            'name'                  => 'template.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'Port: {{ $portClaims["test-claim"] }}',
        ]);

        $interpreted = $file->interpret($this->deployment);
        $this->assertEquals('Port: 9090', $interpreted);
    }

    /**
     * @test
     */
    public function itCanInterpretFileWithLimits(): void
    {
        // Create deployment limit
        DeploymentLimit::factory()->create([
            'deployment_id' => $this->deployment->id,
            'is_active'     => true,
            'cpu'           => 0.5, // 500m = 0.5 cores
            'memory'        => 512, // 512Mi = 512 MB
        ]);

        // Create file with template
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
            'name'                  => 'template.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'CPU: {{ $limits["cpu"] }} cores, Memory: {{ $limits["memory"] }} MB, Enabled: {{ $limits["enabled"] }}',
        ]);

        $interpreted = $file->interpret($this->deployment);
        $this->assertEquals('CPU: 0.5 cores, Memory: 512 MB, Enabled: true', $interpreted);
    }

    /**
     * @test
     */
    public function itCanInterpretFileWithPausedState(): void
    {
        // Create file with template
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
            'name'                  => 'template.txt',
            'mime_type'             => 'text/plain',
            'content'               => 'Paused: {{ $paused ? "yes" : "no" }}',
        ]);

        // Test not paused
        $this->deployment->paused = false;
        $this->deployment->save();
        $interpreted = $file->interpret($this->deployment);
        $this->assertEquals('Paused: no', $interpreted);

        // Test paused
        $this->deployment->paused = true;
        $this->deployment->save();
        $interpreted = $file->interpret($this->deployment);
        $this->assertEquals('Paused: yes', $interpreted);
    }
}
