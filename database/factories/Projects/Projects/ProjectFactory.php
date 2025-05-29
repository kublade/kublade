<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Projects;

use App\Models\Projects\Projects\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ProjectFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name'    => $this->faker->word(),
            'user_id' => User::factory(),
        ];
    }
}
