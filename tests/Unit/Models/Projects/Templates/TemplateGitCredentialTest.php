<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Templates;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateGitCredential;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

/**
 * Class TemplateGitCredentialTest.
 *
 * Unit tests for the TemplateGitCredential model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateGitCredentialTest extends TestCase
{
    use RefreshDatabase;

    private TemplateGitCredential $gitCredential;

    private Template $template;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test template
        $this->template = Template::factory()->create([
            'name'    => 'Test Template',
            'user_id' => $this->user->id,
        ]);

        // Create test git credential
        $this->gitCredential = TemplateGitCredential::factory()->create([
            'template_id' => $this->template->id,
            'url'         => 'https://github.com/example/repo.git',
            'branch'      => 'main',
            'credentials' => 'test-credentials',
            'username'    => 'test-user',
            'email'       => 'test@example.com',
            'base_path'   => '/',
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('template_git_credentials', $this->gitCredential->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->gitCredential->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectEncryptableAttributes(): void
    {
        $reflection = new ReflectionClass($this->gitCredential);
        $property   = $reflection->getProperty('encryptable');
        $property->setAccessible(true);

        $encryptable = ['credentials'];
        $this->assertEquals($encryptable, $property->getValue($this->gitCredential));
    }

    /**
     * @test
     */
    public function itCanHaveTemplate(): void
    {
        $this->assertInstanceOf(HasOne::class, $this->gitCredential->template());
        $this->assertInstanceOf(Template::class, $this->gitCredential->template);
        $this->assertEquals($this->template->id, $this->gitCredential->template->id);
        $this->assertEquals($this->template->name, $this->gitCredential->template->name);
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->gitCredential->deleted_at);
        $this->gitCredential->delete();
        $this->assertNotNull($this->gitCredential->deleted_at);
        $this->assertSoftDeleted($this->gitCredential);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->gitCredential->delete();
        $this->assertSoftDeleted($this->gitCredential);
        $this->gitCredential->restore();
        $this->assertNull($this->gitCredential->deleted_at);
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->gitCredential->id);
        $this->assertEquals(36, strlen($this->gitCredential->id));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->gitCredential->id);
    }

    /**
     * @test
     */
    public function itCanHaveAllAttributes(): void
    {
        $gitCredential = TemplateGitCredential::factory()->create([
            'template_id' => $this->template->id,
            'url'         => 'https://github.com/custom/repo.git',
            'branch'      => 'develop',
            'credentials' => 'custom-credentials',
            'username'    => 'custom-user',
            'email'       => 'custom@example.com',
            'base_path'   => '/custom/path',
        ]);

        $this->assertEquals('https://github.com/custom/repo.git', $gitCredential->url);
        $this->assertEquals('develop', $gitCredential->branch);
        $this->assertEquals('custom-credentials', $gitCredential->credentials);
        $this->assertEquals('custom-user', $gitCredential->username);
        $this->assertEquals('custom@example.com', $gitCredential->email);
        $this->assertEquals('/custom/path', $gitCredential->base_path);
    }

    /**
     * @test
     */
    public function itEncryptsCredentials(): void
    {
        $credentials   = 'sensitive-credentials';
        $gitCredential = TemplateGitCredential::factory()->create([
            'template_id' => $this->template->id,
            'credentials' => $credentials,
        ]);

        // The credentials should be encrypted in the database
        $this->assertNotEquals($credentials, $gitCredential->getRawOriginal('credentials'));

        // But when accessing the attribute, it should be decrypted
        $this->assertEquals($credentials, $gitCredential->credentials);
    }
}
