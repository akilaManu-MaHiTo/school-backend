<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComAssigneeLevel>
 */
class ComAssigneeLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'levelId' => $this->faker->numberBetween(1, 5),
            'levelName' => $this->faker->jobTitle,
        ];
    }
}
