<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create first user
        $user1 = User::create([
            'username' => 'drikz',
            'password' => Hash::make('123'),
            'first_name' => 'Aldrikz',
            'last_name' => 'Suarez',
            'wmsu_email' => 'eh202201066@wmsu.edu.ph',
            'user_type_id' => 2,
            'wmsu_dept_id' => 7,
            'profile_picture' => 'college/profile_pictures/4JF9RlRLSyvQNwUWhws17WvtfIZo66ZgRkQsiYDA.jpg',
            'is_seller' => true,
            'seller_code' => '67519E36CD1BC',
            'is_verified' => false, // Changed to string to match migration
            'verified_at' => null,
            'wmsu_id_front' => 'college/id_front/0xF6SRLYJmKg0KOHyhK8jQ8tFLKyBMRB4aYBzLem.jpg',
            'wmsu_id_back' => 'college/id_back/EmrH7e5OUQLd1NzJsZnuObeNPCUJxHxtuN3Od3Un.jpg',
            'email_verified_at' => null,
        ]);

        // Create second user
        $user2 = User::create([
            'username' => 'rem',
            'password' => Hash::make('123'),
            'first_name' => 'John',
            'last_name' => 'Harold',
            'wmsu_email' => 'eh202201067@wmsu.edu.ph',
            'user_type_id' => 2,
            'wmsu_dept_id' => 7,
            'profile_picture' => 'college/profile_pictures/CRCWg6iCOO6afdH7fpO7SCqcbLsb20GDikddDORW.jpg',
            'is_seller' => true,
            'seller_code' => '67519F4354792',
            'is_verified' => false, // Changed to string to match migration
            'verified_at' => null,
            'wmsu_id_front' => 'college/id_front/0xF6SRLYJmKg0KOHyhK8jQ8tFLKyBMRB4aYBzLem.jpg',
            'wmsu_id_back' => 'college/id_back/1VSal4a1lgpHI2OuxYH08CdAEPMpifnGfee1p020.jpg',
            'email_verified_at' => null,
        ]);

        // Create exactly 2 products per user
        Product::factory(4)->create(['seller_code' => $user1->seller_code]); // changed from 1 to 2
        Product::factory(4)->create(['seller_code' => $user2->seller_code]); // changed from 1 to 2
    }
}
