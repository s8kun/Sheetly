<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sheet>
 */
class SheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['chapter', 'midterm', 'final']);
        
        return [
            'title' => ucfirst($this->faker->words(3, true)),
            'user_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'type' => $type,
            'chapter_number' => $type === 'chapter' ? $this->faker->numberBetween(1, 10) : null,
            'file_url' => $this->faker->url(),
            'downloads_count' => $this->faker->numberBetween(0, 100),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
