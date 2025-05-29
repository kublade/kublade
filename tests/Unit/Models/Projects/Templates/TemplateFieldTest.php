<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Projects\Templates;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFieldOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class TemplateFieldTest.
 *
 * Unit tests for the TemplateField model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateFieldTest extends TestCase
{
    use RefreshDatabase;

    private TemplateField $field;

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

        // Create test field
        $this->field = TemplateField::factory()->create([
            'template_id'   => $this->template->id,
            'type'          => 'input_text',
            'label'         => 'Test Field',
            'key'           => 'test_field',
            'value'         => 'Test Value',
            'required'      => true,
            'secret'        => false,
            'advanced'      => false,
            'set_on_create' => true,
            'set_on_update' => true,
            'min'           => 0,
            'max'           => 100,
            'step'          => 1,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('template_fields', $this->field->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->field->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = $this->field->getCasts();
        $this->assertIsArray($casts);
        $this->assertArrayHasKey('required', $casts);
        $this->assertArrayHasKey('secret', $casts);
        $this->assertArrayHasKey('set_on_create', $casts);
        $this->assertArrayHasKey('set_on_update', $casts);
        $this->assertArrayHasKey('advanced', $casts);
        $this->assertEquals('boolean', $casts['required']);
        $this->assertEquals('boolean', $casts['secret']);
        $this->assertEquals('boolean', $casts['set_on_create']);
        $this->assertEquals('boolean', $casts['set_on_update']);
        $this->assertEquals('boolean', $casts['advanced']);
    }

    /**
     * @test
     */
    public function itCanHaveTemplate(): void
    {
        $this->assertInstanceOf(Template::class, $this->field->template);
        $this->assertEquals($this->template->id, $this->field->template->id);
        $this->assertEquals($this->template->name, $this->field->template->name);
    }

    /**
     * @test
     */
    public function itCanHaveOptions(): void
    {
        // Create field options
        $options = TemplateFieldOption::factory()->count(3)->create([
            'template_field_id' => $this->field->id,
            'label'             => 'Option',
            'value'             => 'value',
            'default'           => false,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->field->options);
        $this->assertCount(3, $this->field->options);
        $this->assertInstanceOf(TemplateFieldOption::class, $this->field->options->first());
        $this->assertEquals($options->pluck('id')->toArray(), $this->field->options->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->field->options()->exists());
        $this->assertEquals(3, $this->field->options()->count());
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->field->deleted_at);
        $this->field->delete();
        $this->assertNotNull($this->field->deleted_at);
        $this->assertSoftDeleted($this->field);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->field->delete();
        $this->assertSoftDeleted($this->field);
        $this->field->restore();
        $this->assertNull($this->field->deleted_at);
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->field->id);
        $this->assertEquals(36, strlen($this->field->id));
    }

    /**
     * @test
     */
    public function itCanHaveDifferentFieldTypes(): void
    {
        $types = [
            'input_text',
            'input_number',
            'input_range',
            'input_radio',
            'input_radio_image',
            'input_checkbox',
            'input_hidden',
            'select',
            'textarea',
        ];

        foreach ($types as $type) {
            $field = TemplateField::factory()->create([
                'template_id' => $this->template->id,
                'type'        => $type,
            ]);

            $this->assertEquals($type, $field->type);
        }
    }

    /**
     * @test
     */
    public function itCanHaveNumericConstraints(): void
    {
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'type'        => 'input_number',
            'min'         => 10,
            'max'         => 100,
            'step'        => 5,
        ]);

        $this->assertEquals(10, $field->min);
        $this->assertEquals(100, $field->max);
        $this->assertEquals(5, $field->step);
    }

    /**
     * @test
     */
    public function itCanBeAdvanced(): void
    {
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'advanced'    => true,
        ]);

        $this->assertTrue($field->advanced);
    }

    /**
     * @test
     */
    public function itCanBeSecret(): void
    {
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'secret'      => true,
        ]);

        $this->assertTrue($field->secret);
    }

    /**
     * @test
     */
    public function itCanBeRequired(): void
    {
        $field = TemplateField::factory()->create([
            'template_id' => $this->template->id,
            'required'    => true,
        ]);

        $this->assertTrue($field->required);
    }

    /**
     * @test
     */
    public function itCanBeSetOnCreate(): void
    {
        $field = TemplateField::factory()->create([
            'template_id'   => $this->template->id,
            'set_on_create' => true,
        ]);

        $this->assertTrue($field->set_on_create);
    }

    /**
     * @test
     */
    public function itCanBeSetOnUpdate(): void
    {
        $field = TemplateField::factory()->create([
            'template_id'   => $this->template->id,
            'set_on_update' => true,
        ]);

        $this->assertTrue($field->set_on_update);
    }
}
