<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\TemplateDirectory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateDirectory>
 */
class TemplateDirectoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateDirectory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => null,
            'parent_id'   => null,
            'name'        => $this->faker->word,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
