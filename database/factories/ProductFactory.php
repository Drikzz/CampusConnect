<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(100, 10000);
        // Generate discount as decimal (0 to 0.10)
        $discount = $this->faker->boolean(30) ? $this->faker->randomFloat(2, 0.01, 0.90) : 0;
        $discountedPrice = $price * (1 - $discount);

        // Generate 1-3 random image URLs
        $imageCount = $this->faker->numberBetween(1, 3);
        $imageUrls = [];
        for ($i = 0; $i < $imageCount; $i++) {
            $selectedIndex = $this->faker->numberBetween(1, 8);
            $imageUrls[] = "products/sample_imgs/img{$selectedIndex}.jpg";
        }

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraphs(2, true),
            'price' => $price,
            'discount' => $discount, // Now stores as decimal (e.g., 0.05 for 5%)
            'discounted_price' => round($discountedPrice, 2),
            'images' => $imageUrls,
            'stock' => $this->faker->numberBetween(1, 20),
            'seller_code' => null, // Will be set in DatabaseSeeder
            'category_id' => $this->faker->numberBetween(1, 4),
            'is_buyable' => $this->faker->boolean(80),
            'is_tradable' => $this->faker->boolean(20),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }
}
