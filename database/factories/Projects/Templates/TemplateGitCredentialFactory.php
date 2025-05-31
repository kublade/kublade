<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\TemplateGitCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateGitCredential>
 */
class TemplateGitCredentialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemplateGitCredential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => null, // Should be set when creating
            'url'         => $this->faker->url(),
            'branch'      => $this->faker->randomElement(['main', 'develop', 'feature/test']),
            'credentials' => null,
            'username'    => $this->faker->userName(),
            'email'       => $this->faker->email(),
            'base_path'   => '/',
        ];
    }
}
