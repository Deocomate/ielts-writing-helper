<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingMaterial>
 */
class ReadingMaterialFactory extends Factory
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
            'topic' => fake()->randomElement(['people', 'environment', 'technology', 'inspiration']),
            'excerpt' => fake()->sentence(14),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'vocabulary_notes' => [
                [
                    'term' => 'sustainable',
                    'meaning' => 'bền vững',
                    'note' => 'Useful for environment topics.',
                ],
            ],
            'status' => 'published',
            'views_count' => 0,
        ];
    }
}
