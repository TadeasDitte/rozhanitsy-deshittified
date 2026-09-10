<?php

namespace Database\Factories;

use App\Models\Format;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Format>
 */
class FormatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'version' => null,
        ];
    }

    public function cpe(): static
    {
        return $this->state(fn (): array => ['name' => 'cpe', 'version' => '2.3']);
    }

    public function purl(): static
    {
        return $this->state(fn (): array => ['name' => 'purl', 'version' => null]);
    }
}
