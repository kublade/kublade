<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Templates;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateDirectory;
use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFile;
use App\Models\Projects\Templates\TemplatePort;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection as SupportCollection;
use Tests\TestCase;

/**
 * Class TemplateTest.
 *
 * Unit tests for the Template model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateTest extends TestCase
{
    use RefreshDatabase;

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
            'netpol'  => true,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('templates', $this->template->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->template->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = $this->template->getCasts();
        $this->assertIsArray($casts);
        $this->assertArrayHasKey('netpol', $casts);
        $this->assertEquals('boolean', $casts['netpol']);
    }

    /**
     * @test
     */
    public function itCanHaveUser(): void
    {
        $this->assertInstanceOf(User::class, $this->template->user);
        $this->assertEquals($this->user->id, $this->template->user->id);
        $this->assertEquals($this->user->name, $this->template->user->name);
        $this->assertEquals($this->user->email, $this->template->user->email);
    }

    /**
     * @test
     */
    public function itCanHaveFields(): void
    {
        // Create template fields
        $fields = TemplateField::factory()->count(3)->create([
            'template_id' => $this->template->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->template->fields);
        $this->assertCount(3, $this->template->fields);
        $this->assertInstanceOf(TemplateField::class, $this->template->fields->first());
        $this->assertEquals($fields->pluck('id')->toArray(), $this->template->fields->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->template->fields()->exists());
        $this->assertEquals(3, $this->template->fields()->count());
    }

    /**
     * @test
     */
    public function itCanHavePorts(): void
    {
        // Create template ports
        $ports = TemplatePort::factory()->count(3)->create([
            'template_id' => $this->template->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->template->ports);
        $this->assertCount(3, $this->template->ports);
        $this->assertInstanceOf(TemplatePort::class, $this->template->ports->first());
        $this->assertEquals($ports->pluck('id')->toArray(), $this->template->ports->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->template->ports()->exists());
        $this->assertEquals(3, $this->template->ports()->count());
    }

    /**
     * @test
     */
    public function itCanHaveDirectories(): void
    {
        // Create template directories
        $directories = TemplateDirectory::factory()->count(3)->create([
            'template_id' => $this->template->id,
            'parent_id'   => null,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->template->directories);
        $this->assertCount(3, $this->template->directories);
        $this->assertInstanceOf(TemplateDirectory::class, $this->template->directories->first());
        $this->assertEquals($directories->pluck('id')->toArray(), $this->template->directories->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->template->directories()->exists());
        $this->assertEquals(3, $this->template->directories()->count());
    }

    /**
     * @test
     */
    public function itCanHaveFiles(): void
    {
        // Create template files
        $files = TemplateFile::factory()->count(3)->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => null,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->template->files);
        $this->assertCount(3, $this->template->files);
        $this->assertInstanceOf(TemplateFile::class, $this->template->files->first());
        $this->assertEquals($files->pluck('id')->toArray(), $this->template->files->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->template->files()->exists());
        $this->assertEquals(3, $this->template->files()->count());
    }

    /**
     * @test
     */
    public function itCanGetTree(): void
    {
        // Create a directory with subdirectories and files
        $rootDir = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'parent_id'   => null,
            'name'        => 'root',
        ]);

        $subDir = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'parent_id'   => $rootDir->id,
            'name'        => 'sub',
        ]);

        $file1 = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $rootDir->id,
            'name'                  => 'file1.txt',
        ]);

        $file2 = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $subDir->id,
            'name'                  => 'file2.txt',
        ]);

        // Get the tree
        $tree = $this->template->tree;

        // Test the tree structure
        $this->assertInstanceOf(SupportCollection::class, $tree);
        $this->assertCount(1, $tree); // Only root directory at top level

        // Find the root directory in the tree
        $rootDirInTree = $tree->first();
        $this->assertNotNull($rootDirInTree);
        $this->assertEquals('folder', $rootDirInTree->type);
        $this->assertEquals('root', $rootDirInTree->name);
        $this->assertCount(2, $rootDirInTree->children); // sub directory and file1

        // Find the sub directory in the tree
        $subDirInTree = collect($rootDirInTree->children)->firstWhere('name', 'sub');
        $this->assertNotNull($subDirInTree);
        $this->assertEquals('folder', $subDirInTree->type);
        $this->assertEquals('sub', $subDirInTree->name);
        $this->assertCount(1, $subDirInTree->children); // file2

        // Find file1 in the tree
        $file1InTree = collect($rootDirInTree->children)->firstWhere('name', 'file1.txt');
        $this->assertNotNull($file1InTree);
        $this->assertEquals('file', $file1InTree->type);
        $this->assertEquals('file1.txt', $file1InTree->name);

        // Find file2 in the tree
        $file2InTree = collect($subDirInTree->children)->firstWhere('name', 'file2.txt');
        $this->assertNotNull($file2InTree);
        $this->assertEquals('file', $file2InTree->type);
        $this->assertEquals('file2.txt', $file2InTree->name);
    }

    /**
     * @test
     */
    public function itCanGetFullTree(): void
    {
        // Create a directory with subdirectories and files
        $rootDir = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'parent_id'   => null,
            'name'        => 'root',
        ]);

        $subDir = TemplateDirectory::factory()->create([
            'template_id' => $this->template->id,
            'parent_id'   => $rootDir->id,
            'name'        => 'sub',
        ]);

        $file1 = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $rootDir->id,
            'name'                  => 'file1.txt',
        ]);

        $file2 = TemplateFile::factory()->create([
            'template_id'           => $this->template->id,
            'template_directory_id' => $subDir->id,
            'name'                  => 'file2.txt',
        ]);

        // Get the full tree
        $fullTree = $this->template->fullTree;

        // Test the full tree structure
        $this->assertInstanceOf(SupportCollection::class, $fullTree);
        $this->assertCount(1, $fullTree); // Only root directory at top level

        // Find the root directory in the full tree
        $rootDirInTree = $fullTree->first();
        $this->assertNotNull($rootDirInTree);
        $this->assertEquals('folder', $rootDirInTree->type);
        $this->assertInstanceOf(TemplateDirectory::class, $rootDirInTree->object);
        $this->assertEquals($rootDir->id, $rootDirInTree->object->id);
        $this->assertCount(2, $rootDirInTree->children); // sub directory and file1

        // Find the sub directory in the full tree
        $subDirInTree = collect($rootDirInTree->children)->firstWhere('type', 'folder');
        $this->assertNotNull($subDirInTree);
        $this->assertEquals('folder', $subDirInTree->type);
        $this->assertInstanceOf(TemplateDirectory::class, $subDirInTree->object);
        $this->assertEquals($subDir->id, $subDirInTree->object->id);
        $this->assertCount(1, $subDirInTree->children); // file2

        // Find file1 in the full tree
        $file1InTree = collect($rootDirInTree->children)->firstWhere('type', 'file');
        $this->assertNotNull($file1InTree);
        $this->assertEquals('file', $file1InTree->type);
        $this->assertInstanceOf(TemplateFile::class, $file1InTree->object);
        $this->assertEquals($file1->id, $file1InTree->object->id);

        // Find file2 in the full tree
        $file2InTree = collect($subDirInTree->children)->first();
        $this->assertNotNull($file2InTree);
        $this->assertEquals('file', $file2InTree->type);
        $this->assertInstanceOf(TemplateFile::class, $file2InTree->object);
        $this->assertEquals($file2->id, $file2InTree->object->id);
    }

    /**
     * @test
     */
    public function itCanGetGroupedFields(): void
    {
        // Create template fields with different types and settings
        $fields = [
            // Advanced fields for create
            TemplateField::factory()->create([
                'template_id'   => $this->template->id,
                'advanced'      => true,
                'set_on_create' => true,
                'set_on_update' => false,
                'type'          => 'input_text',
            ]),
            // Default fields for create
            TemplateField::factory()->create([
                'template_id'   => $this->template->id,
                'advanced'      => false,
                'set_on_create' => true,
                'set_on_update' => false,
                'type'          => 'input_text',
            ]),
            // Hidden fields for create
            TemplateField::factory()->create([
                'template_id'   => $this->template->id,
                'set_on_create' => true,
                'set_on_update' => false,
                'type'          => 'input_hidden',
            ]),
            // Advanced fields for update
            TemplateField::factory()->create([
                'template_id'   => $this->template->id,
                'advanced'      => true,
                'set_on_create' => false,
                'set_on_update' => true,
                'type'          => 'input_text',
            ]),
            // Default fields for update
            TemplateField::factory()->create([
                'template_id'   => $this->template->id,
                'advanced'      => false,
                'set_on_create' => false,
                'set_on_update' => true,
                'type'          => 'input_text',
            ]),
            // Hidden fields for update
            TemplateField::factory()->create([
                'template_id'   => $this->template->id,
                'set_on_create' => false,
                'set_on_update' => true,
                'type'          => 'input_hidden',
            ]),
        ];

        // Get grouped fields
        $groupedFields = $this->template->groupedFields;

        // Test the structure
        $this->assertIsObject($groupedFields);
        $this->assertObjectHasProperty('all', $groupedFields);
        $this->assertObjectHasProperty('on_create', $groupedFields);
        $this->assertObjectHasProperty('on_update', $groupedFields);

        // Test all fields (excluding hidden)
        $this->assertCount(4, $groupedFields->all); // All non-hidden fields

        // Test create fields
        $this->assertObjectHasProperty('advanced', $groupedFields->on_create);
        $this->assertObjectHasProperty('default', $groupedFields->on_create);
        $this->assertObjectHasProperty('hidden', $groupedFields->on_create);
        $this->assertCount(1, $groupedFields->on_create->advanced);
        $this->assertCount(1, $groupedFields->on_create->default);
        $this->assertCount(1, $groupedFields->on_create->hidden);

        // Test update fields
        $this->assertObjectHasProperty('advanced', $groupedFields->on_update);
        $this->assertObjectHasProperty('default', $groupedFields->on_update);
        $this->assertObjectHasProperty('hidden', $groupedFields->on_update);
        $this->assertCount(1, $groupedFields->on_update->advanced);
        $this->assertCount(1, $groupedFields->on_update->default);
        $this->assertCount(1, $groupedFields->on_update->hidden);
    }

    /**
     * @test
     */
    public function itHandlesEmptyTree(): void
    {
        $template = Template::factory()->create();

        $this->assertInstanceOf(SupportCollection::class, $template->tree);
        $this->assertCount(0, $template->tree);
    }

    /**
     * @test
     */
    public function itHandlesEmptyGroupedFields(): void
    {
        $template = Template::factory()->create();

        $groupedFields = $template->groupedFields;

        $this->assertIsObject($groupedFields);
        $this->assertObjectHasProperty('all', $groupedFields);
        $this->assertObjectHasProperty('on_create', $groupedFields);
        $this->assertObjectHasProperty('on_update', $groupedFields);
        $this->assertCount(0, $groupedFields->all);
        $this->assertObjectHasProperty('advanced', $groupedFields->on_create);
        $this->assertObjectHasProperty('default', $groupedFields->on_create);
        $this->assertObjectHasProperty('hidden', $groupedFields->on_create);
        $this->assertCount(0, $groupedFields->on_create->advanced);
        $this->assertCount(0, $groupedFields->on_create->default);
        $this->assertCount(0, $groupedFields->on_create->hidden);
        $this->assertObjectHasProperty('advanced', $groupedFields->on_update);
        $this->assertObjectHasProperty('default', $groupedFields->on_update);
        $this->assertObjectHasProperty('hidden', $groupedFields->on_update);
        $this->assertCount(0, $groupedFields->on_update->advanced);
        $this->assertCount(0, $groupedFields->on_update->default);
        $this->assertCount(0, $groupedFields->on_update->hidden);
    }
}
