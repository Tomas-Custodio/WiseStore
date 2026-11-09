<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
        return [
            'img' => 'imgs/placeholder.jpg',
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'preco' => fake()->numberBetween(5, 200) * 1000,
            'description' => fake()->sentence(),
            'status' => 'active',
            'promotion' => false,
        ];
    }

    /**
     * Indicate that the product is inactive (hidden from the store).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
