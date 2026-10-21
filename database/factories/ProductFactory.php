<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * The default state of a product.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $from = $this->faker->randomFloat(2, 1, 500);

        return [
            'name' => $this->faker->words(3, true),
            'priceFrom' => $from,
            'priceTo' => $from + $this->faker->randomFloat(2, 1, 100),
            'published' => $this->faker->randomElement(['Yes', 'No']),
        ];
    }
}
