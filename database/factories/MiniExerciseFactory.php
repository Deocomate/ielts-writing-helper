<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MiniExercise>
 */
class MiniExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'mistake_type' => fake()->randomElement(['tense', 'structure', 'vocabulary', 'punctuation']),
            'exercise_type' => 'fill_blank',
            'difficulty_level' => fake()->randomElement(['easy', 'medium', 'hard']),
            'question_data' => [
                'sentence' => 'She ___ to school yesterday.',
                'answers' => ['went'],
                'options' => ['go', 'went', 'gone'],
            ],
            'explanation' => 'Use past simple for a completed action in the past.',
            'status' => 'published',
        ];
    }
}
