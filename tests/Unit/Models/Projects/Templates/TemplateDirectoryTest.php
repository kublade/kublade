<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Templates;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class TemplateDirectoryTest.
 *
 * Unit tests for the TemplateDirectory model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private TemplateDirectory $directory;

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

        // Create test directory
        $this->directory = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'name'        => 'Test Directory',
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('template_directories', $this->directory->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->directory->getGuarded());
    }

    /**
     * @test
     */
    public function itCanHaveTemplate(): void
    {
        $this->assertInstanceOf(Template::class, $this->directory->template);
        $this->assertEquals($this->template->id, $this->directory->template->id);
        $this->assertEquals($this->template->name, $this->directory->template->name);
    }

    /**
     * @test
     */
    public function itCanHaveParentDirectory(): void
    {
        // Create parent directory
        $parentDirectory = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'name'        => 'Parent Directory',
        ]);

        // Set parent directory
        $this->directory->parent_id = $parentDirectory->id;
        $this->directory->save();

        // Test the relationship
        $this->assertInstanceOf(TemplateDirectory::class, $this->directory->parent);
        $this->assertEquals($parentDirectory->id, $this->directory->parent->id);
        $this->assertEquals($parentDirectory->name, $this->directory->parent->name);
    }

    /**
     * @test
     */
    public function itCanHaveSubdirectories(): void
    {
        // Create subdirectories
        $subdirectories = TemplateDirectory::factory()->count(3)->create([
            'template_id' => $this->template->id,
            'parent_id'   => $this->directory->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->directory->folders);
        $this->assertCount(3, $this->directory->folders);
        $this->assertInstanceOf(TemplateDirectory::class, $this->directory->folders->first());
        $this->assertEquals($subdirectories->pluck('id')->toArray(), $this->directory->folders->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->directory->folders()->exists());
        $this->assertEquals(3, $this->directory->folders()->count());
    }

    /**
     * @test
     */
    public function itCanHaveFiles(): void
    {
        // Create files
        $files = TemplateFile::factory()->count(3)->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $this->directory->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->directory->files);
        $this->assertCount(3, $this->directory->files);
        $this->assertInstanceOf(TemplateFile::class, $this->directory->files->first());
        $this->assertEquals($files->pluck('id')->toArray(), $this->directory->files->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->directory->files()->exists());
        $this->assertEquals(3, $this->directory->files()->count());
    }

    /**
     * @test
     */
    public function itCanGetTree(): void
    {
        // Create subdirectory
        $subdirectory = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'parent_id'   => $this->directory->id,
            'name'        => 'Subdirectory',
        ]);

        // Create file in subdirectory
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $subdirectory->id,
            'name'                  => 'test.yaml',
        ]);

        // Get tree
        $tree = $this->directory->tree;

        // Test tree structure
        $this->assertIsObject($tree);
        $this->assertEquals('folder', $tree->type);
        $this->assertEquals($this->directory->id, $tree->id);
        $this->assertEquals($this->directory->name, $tree->name);
        $this->assertCount(1, $tree->children);

        // Test subdirectory in tree
        $subdirectoryTree = $tree->children->first();
        $this->assertEquals('folder', $subdirectoryTree->type);
        $this->assertEquals($subdirectory->id, $subdirectoryTree->id);
        $this->assertEquals($subdirectory->name, $subdirectoryTree->name);
        $this->assertCount(1, $subdirectoryTree->children);

        // Test file in subdirectory tree
        $fileTree = $subdirectoryTree->children->first();
        $this->assertEquals('file', $fileTree->type);
        $this->assertEquals($file->id, $fileTree->id);
        $this->assertEquals($file->name, $fileTree->name);
        $this->assertEquals($file->mime_type, $fileTree->mime_type);
    }

    /**
     * @test
     */
    public function itCanGetFullTree(): void
    {
        // Create subdirectory
        $subdirectory = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'parent_id'   => $this->directory->id,
            'name'        => 'Subdirectory',
        ]);

        // Create file in subdirectory
        $file = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $subdirectory->id,
            'name'                  => 'test.yaml',
        ]);

        // Get full tree
        $fullTree = $this->directory->fullTree;

        // Test full tree structure
        $this->assertIsObject($fullTree);
        $this->assertEquals('folder', $fullTree->type);
        $this->assertInstanceOf(TemplateDirectory::class, $fullTree->object);
        $this->assertEquals($this->directory->id, $fullTree->object->id);
        $this->assertCount(1, $fullTree->children);

        // Test subdirectory in full tree
        $subdirectoryFullTree = $fullTree->children->first();
        $this->assertEquals('folder', $subdirectoryFullTree->type);
        $this->assertInstanceOf(TemplateDirectory::class, $subdirectoryFullTree->object);
        $this->assertEquals($subdirectory->id, $subdirectoryFullTree->object->id);
        $this->assertCount(1, $subdirectoryFullTree->children);

        // Test file in subdirectory full tree
        $fileFullTree = $subdirectoryFullTree->children->first();
        $this->assertEquals('file', $fileFullTree->type);
        $this->assertInstanceOf(TemplateFile::class, $fileFullTree->object);
        $this->assertEquals($file->id, $fileFullTree->object->id);
    }

    /**
     * @test
     */
    public function itCanGetPath(): void
    {
        // Test root directory path
        $this->assertEquals('/' . $this->directory->name, $this->directory->path);

        // Create parent directory
        $parentDirectory = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'name'        => 'Parent Directory',
        ]);

        // Set parent directory and test nested path
        $this->directory->parent_id = $parentDirectory->id;
        $this->directory->save();

        // Load the parent relationship
        $this->directory->load('parent');

        $this->assertEquals('/' . $parentDirectory->name . '/' . $this->directory->name, $this->directory->path);
    }
}
