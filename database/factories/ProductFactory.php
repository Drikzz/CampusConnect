<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        static $imageIndex = 1;

        $price = $this->faker->numberBetween(100, 10000); // Price in cents
        $discount = $this->faker->numberBetween(0, 100); // Discount percentage

        // Calculate discounted price
        $discountedPrice = $price - ($price * ($discount / 100));

        // Generate the full URL for the image
        $imagePath = 'imgs/img' . $imageIndex++ . '.jpg';
        $imageUrl = asset($imagePath);

        // Generate random values ensuring at least one is true 80% of the time
        $rand = $this->faker->numberBetween(1, 100);
        if ($rand <= 80) {
            // 80% chance of having at least one true
            $is_buyable = $this->faker->boolean();
            $is_tradable = $is_buyable ? $this->faker->boolean() : true;
        } else {
            // 20% chance of both being true
            $is_buyable = true;
            $is_tradable = true;
        }

        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'price' => $price,
            'discount' => $discount,
            'discounted_price' => $discountedPrice,
            'image' => $imageUrl,
            'stock' => $this->faker->numberBetween(1, 100),
            'quantity' => $this->faker->numberBetween(0, 100),
            'user_id' => 1,
            'is_buyable' => $is_buyable,
            'is_tradable' => $is_tradable,
        ];
    }
}
