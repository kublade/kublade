<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\TemplateFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateFile>
 */
class TemplateFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id'           => null,
            'template_directory_id' => null,
            'name'                  => $this->faker->word . '.yaml',
            'content'               => $this->faker->text,
            'mime_type'             => 'text/yaml',
            'created_at'            => now(),
            'updated_at'            => now(),
        ];
    }
}
