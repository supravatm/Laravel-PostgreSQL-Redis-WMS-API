<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'unit' => 'pcs',
            'status' => 'active',
            'low_stock_threshold' => 10,
        ];
    }
}
