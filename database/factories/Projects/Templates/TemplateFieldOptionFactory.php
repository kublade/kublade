<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\TemplateField;
use App\Models\Projects\Templates\TemplateFieldOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateFieldOption>
 */
class TemplateFieldOptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateFieldOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_field_id' => TemplateField::factory(),
            'label'             => $this->faker->word,
            'value'             => $this->faker->word,
            'default'           => false,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
