<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryId = Category::pluck('id')->random();
        $name = fake()->word();
        return [
            'name' => $name,
            'slug' => fake()->slug(),
            'price' => fake()->randomFloat(2, 10000, 150000),
            'description' => fake()->sentence(),
            'category_id' => $categoryId,
            'stock' => fake()->numberBetween(1, 40),
            'image_url' => 'https://placehold.co/128x81?text=' . $name
        ];
    }
}
