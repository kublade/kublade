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
 * Class TemplateFieldOptionTest.
 *
 * Unit tests for the TemplateFieldOption model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class TemplateFieldOptionTest extends TestCase
{
    use RefreshDatabase;

    private TemplateFieldOption $option;

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
            'template_id' => $this->template->id,
            'type'        => 'select',
            'label'       => 'Test Field',
            'key'         => 'test_field',
        ]);

        // Create test option
        $this->option = TemplateFieldOption::factory()->create([
            'template_field_id' => $this->field->id,
            'label'             => 'Test Option',
            'value'             => 'test_value',
            'default'           => true,
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('template_field_options', $this->option->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectGuardedAttributes(): void
    {
        $guarded = ['id'];
        $this->assertEquals($guarded, $this->option->getGuarded());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = $this->option->getCasts();
        $this->assertIsArray($casts);
        $this->assertArrayHasKey('default', $casts);
        $this->assertEquals('boolean', $casts['default']);
    }

    /**
     * @test
     */
    public function itCanHaveField(): void
    {
        $this->assertInstanceOf(TemplateField::class, $this->option->field);
        $this->assertEquals($this->field->id, $this->option->field->id);
        $this->assertEquals($this->field->label, $this->option->field->label);
        $this->assertEquals($this->field->key, $this->option->field->key);
    }

    /**
     * @test
     */
    public function itCanBeSoftDeleted(): void
    {
        $this->assertNull($this->option->deleted_at);
        $this->option->delete();
        $this->assertNotNull($this->option->deleted_at);
        $this->assertSoftDeleted($this->option);
    }

    /**
     * @test
     */
    public function itCanBeRestored(): void
    {
        $this->option->delete();
        $this->assertSoftDeleted($this->option);
        $this->option->restore();
        $this->assertNull($this->option->deleted_at);
    }

    /**
     * @test
     */
    public function itHasUuid(): void
    {
        $this->assertIsString($this->option->id);
        $this->assertEquals(36, strlen($this->option->id));
    }

    /**
     * @test
     */
    public function itCanHaveDefaultValue(): void
    {
        // Test default value is true
        $this->assertTrue($this->option->default);

        // Create option with default false
        $option = TemplateFieldOption::factory()->create([
            'template_field_id' => $this->field->id,
            'default'           => false,
        ]);

        $this->assertFalse($option->default);
    }

    /**
     * @test
     */
    public function itCanHaveDifferentLabelsAndValues(): void
    {
        $labels = ['Option 1', 'Option 2', 'Option 3'];
        $values = ['value1', 'value2', 'value3'];

        foreach ($labels as $index => $label) {
            $option = TemplateFieldOption::factory()->create([
                'template_field_id' => $this->field->id,
                'label'             => $label,
                'value'             => $values[$index],
            ]);

            $this->assertEquals($label, $option->label);
            $this->assertEquals($values[$index], $option->value);
        }
    }

    /**
     * @test
     */
    public function itCanHaveMultipleOptionsForField(): void
    {
        // Create multiple options for the same field
        $options = TemplateFieldOption::factory()->count(3)->create([
            'template_field_id' => $this->field->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->field->options);
        $this->assertCount(4, $this->field->options); // 3 new + 1 from setUp
        $this->assertInstanceOf(TemplateFieldOption::class, $this->field->options->first());

        // Verify all options are associated with the correct field
        $this->field->options->each(function ($option) {
            $this->assertEquals($this->field->id, $option->template_field_id);
        });
    }

    /**
     * @test
     */
    public function itCanHaveUniqueValuesForField(): void
    {
        // Create options with unique values
        $options = collect([
            ['label' => 'Option 1', 'value' => 'value1'],
            ['label' => 'Option 2', 'value' => 'value2'],
            ['label' => 'Option 3', 'value' => 'value3'],
        ])->map(function ($data) {
            return TemplateFieldOption::factory()->create([
                'template_field_id' => $this->field->id,
                'label'             => $data['label'],
                'value'             => $data['value'],
            ]);
        });

        // Verify all options have unique values
        $values = $this->field->options->pluck('value')->toArray();
        $this->assertCount(count(array_unique($values)), $values);
    }

    /**
     * @test
     */
    public function itCanHaveDefaultOptionForField(): void
    {
        // Delete the default option from setUp to start fresh
        $this->option->delete();

        // Create multiple options with only one default
        $options = collect([
            ['label' => 'Option 1', 'value' => 'value1', 'default' => true],
            ['label' => 'Option 2', 'value' => 'value2', 'default' => false],
            ['label' => 'Option 3', 'value' => 'value3', 'default' => false],
        ])->map(function ($data) {
            return TemplateFieldOption::factory()->create([
                'template_field_id' => $this->field->id,
                'label'             => $data['label'],
                'value'             => $data['value'],
                'default'           => $data['default'],
            ]);
        });

        // Refresh the field to get updated options
        $this->field->refresh();

        // Verify only one option is default
        $defaultOptions = $this->field->options->where('default', true);
        $this->assertCount(1, $defaultOptions);
        $this->assertEquals('value1', $defaultOptions->first()->value);

        // Test that changing default on one option removes it from others
        $option2          = $options[1];
        $option2->default = true;
        $option2->save();

        // Refresh both the field and the option to get updated values
        $this->field->refresh();
        $option2->refresh();

        // Verify the default has changed
        $defaultOptions = $this->field->options->where('default', true);
        $this->assertCount(1, $defaultOptions);
        $this->assertEquals('value2', $defaultOptions->first()->value);
        $this->assertTrue($option2->default);
        $this->assertFalse($options[0]->fresh()->default);
    }
}
