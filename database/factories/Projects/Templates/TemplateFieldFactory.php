<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\Template;
use App\Models\Projects\Templates\TemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class TemplateFieldFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<TemplateField>
 */
class TemplateFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id'   => Template::factory(),
            'key'           => $this->faker->word,
            'label'         => $this->faker->words(3, true),
            'type'          => $this->faker->randomElement(['input_text', 'input_hidden', 'textarea']),
            'advanced'      => $this->faker->boolean,
            'set_on_create' => $this->faker->boolean,
            'set_on_update' => $this->faker->boolean,
            'required'      => $this->faker->boolean,
            'secret'        => $this->faker->boolean,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
