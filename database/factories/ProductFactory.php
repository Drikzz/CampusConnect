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
        // Realistic product data arrays
        $productOptions = [
            // School supplies
            ['Scientific Calculator', 'Texas Instruments TI-84 Plus CE graphing calculator with color screen and rechargeable battery. Perfect for advanced math classes.', 1200, 4],
            ['Notebook Set', 'Set of 5 college-ruled spiral notebooks, 100 pages each. Perfect for taking notes in multiple subjects.', 250, 12],
            ['Mechanical Pencil Set', 'Premium mechanical pencil set with 3 pencils (0.5mm, 0.7mm, 0.9mm) and lead refills.', 180, 15],
            ['USB Flash Drive', '32GB USB 3.0 flash drive, compact and reliable for storing all your academic files and presentations.', 350, 8],
            ['Backpack', 'Durable waterproof backpack with laptop compartment, multiple pockets and ergonomic design.', 1500, 6],
            
            // Tech products
            ['Wireless Earbuds', 'Bluetooth 5.0 wireless earbuds with noise cancellation and 6-hour battery life.', 800, 5],
            ['Laptop Stand', 'Adjustable aluminum laptop stand, improves posture and cooling for your device.', 450, 7],
            ['Wireless Mouse', 'Ergonomic wireless mouse with silent clicking and long battery life.', 350, 9],
            ['Power Bank', '10000mAh power bank with fast charging capability for phones and small devices.', 600, 10],
            ['Phone Holder', 'Flexible phone holder that clamps to your desk for hands-free use during online classes.', 200, 12],
            
            // Books and materials
            ['Programming Textbook', 'Introduction to Python Programming, 3rd Edition. Slightly used with minimal highlighting.', 500, 2],
            ['Lab Coat', 'Standard white lab coat, size M/L, required for science laboratory classes.', 350, 8],
            ['Art Supply Kit', 'Comprehensive art supply kit including sketch pads, pencils, and watercolors.', 450, 4],
            ['Academic Planner', '2023-2024 academic year planner with monthly and weekly views, goal setting pages.', 180, 15],
            ['Graphing Paper Pad', 'A4 size graphing paper pad, 50 sheets, perfect for engineering and math courses.', 120, 20]
        ];
        
        // Select a random product from our options
        $randomProduct = $productOptions[array_rand($productOptions)];
        
        // Extract product details
        $name = $randomProduct[0];
        $description = $randomProduct[1];
        $price = $randomProduct[2];
        $stock = $randomProduct[3];
        
        // Apply discount to some products (20% chance)
        $hasDiscount = (rand(1, 100) <= 20);
        $discount = $hasDiscount ? rand(5, 30) / 100 : 0; // 5% to 30% discount
        $discountedPrice = $price * (1 - $discount);
        
        // Generate 1-3 realistic image URLs
        $imageCount = rand(1, 3);
        $imageUrls = [];
        for ($i = 0; $i < $imageCount; $i++) {
            $selectedIndex = rand(1, 8);
            $imageUrls[] = "products/sample_imgs/img{$selectedIndex}.jpg";
        }

        return [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'discount' => $discount,
            'discounted_price' => round($discountedPrice, 2),
            'images' => $imageUrls,
            'stock' => $stock,
            'seller_code' => null, // Will be set in DatabaseSeeder
            'category_id' => rand(1, 4),
            'is_buyable' => (rand(1, 100) <= 80), // 80% chance of being buyable
            'is_tradable' => (rand(1, 100) <= 20), // 20% chance of being tradable
            'status' => rand(1, 100) <= 90 ? 'Active' : 'Inactive', // 90% chance of being active
        ];
    }
}
