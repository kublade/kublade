<?php

declare(strict_types=1);

namespace Database\Factories\Projects\Templates;

use App\Models\Projects\Templates\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class TemplateFactory.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        return [
            'name'    => $this->faker->word(),
            'user_id' => User::factory(),
            'netpol'  => true,
        ];
    }
}
