<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users of different types

        // Admin User
        User::create([
            'username' => 'admin',
            'password' => Hash::make('Admin@1234!'), // Matches password requirements
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'wmsu_email' => 'admin@wmsu.edu.ph',
            'phone' => '+63' . fake()->numberBetween(9000000000, 9999999999),
            'date_of_birth' => Carbon::now()->subYears(rand(25, 40))->format('Y-m-d'),
            'gender' => 'male',
            'profile_picture' => 'defaults/admin-avatar.jpg',
            'user_type_id' => null,
            'wmsu_dept_id' => 1,
            'is_admin' => true,
            'is_verified' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        // College Student (Seller)
        $user1 = User::create([
            'username' => 'drikz',
            'password' => Hash::make('College@1234!'),
            'first_name' => 'Aldrikz',
            'last_name' => 'Suarez',
            'wmsu_email' => 'eh202201066@wmsu.edu.ph',
            'phone' => '+63' . fake()->numberBetween(9000000000, 9999999999),
            'date_of_birth' => Carbon::now()->subYears(rand(18, 22))->format('Y-m-d'),
            'gender' => 'male',
            'user_type_id' => UserType::where('code', 'COL')->first()->id,
            'wmsu_dept_id' => 7,
            'profile_picture' => 'college/profile_pictures/user1-avatar.jpg',
            'wmsu_id_front' => 'college/id_front/student1-id-front.jpg',
            'wmsu_id_back' => 'college/id_back/student1-id-back.jpg',
            'is_seller' => true,
            'seller_code' => 'S' . str_pad(1, 5, '0', STR_PAD_LEFT),
        ]);

        // High School Student
        User::create([
            'username' => 'hsstudent',
            'password' => Hash::make('Student@1234!'),
            'first_name' => 'John',
            'last_name' => 'Smith',
            'wmsu_email' => 'js20240001@wmsu.edu.ph',
            'phone' => '+63' . fake()->numberBetween(9000000000, 9999999999),
            'date_of_birth' => Carbon::now()->subYears(rand(13, 17))->format('Y-m-d'),
            'gender' => 'male',
            'user_type_id' => UserType::where('code', 'HS')->first()->id,
            'grade_level_id' => rand(1, 6),
            'profile_picture' => 'highschool/profile_pictures/student2-avatar.jpg',
            'wmsu_id_front' => 'highschool/id_front/student2-id-front.jpg',
            'wmsu_id_back' => 'highschool/id_back/student2-id-back.jpg',
        ]);

        // Employee
        User::create([
            'username' => 'employee',
            'password' => Hash::make('Employee@1234!'),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'wmsu_email' => 'jane.doe@wmsu.edu.ph',
            'phone' => '+63' . fake()->numberBetween(9000000000, 9999999999),
            'date_of_birth' => Carbon::now()->subYears(rand(25, 50))->format('Y-m-d'),
            'gender' => 'female',
            'user_type_id' => UserType::where('code', 'EMP')->first()->id,
            'wmsu_dept_id' => 3,
            'profile_picture' => 'employee/profile_pictures/emp1-avatar.jpg',
        ]);

        // Alumni (Seller)
        $user2 = User::create([
            'username' => 'alumni',
            'password' => Hash::make('Alumni@1234!'),
            'first_name' => 'Robert',
            'last_name' => 'Johnson',
            'phone' => '+63' . fake()->numberBetween(9000000000, 9999999999),
            'date_of_birth' => Carbon::now()->subYears(rand(23, 40))->format('Y-m-d'),
            'gender' => 'male',
            'user_type_id' => UserType::where('code', 'ALM')->first()->id,
            'profile_picture' => 'alumni/profile_pictures/alum1-avatar.jpg',
            'wmsu_id_front' => 'alumni/id_front/alum1-id-front.jpg',
            'wmsu_id_back' => 'alumni/id_back/alum1-id-back.jpg',
            'is_seller' => true,
            'seller_code' => 'S' . str_pad(2, 5, '0', STR_PAD_LEFT),
        ]);

        // Create products for sellers
        foreach ([$user1, $user2] as $user) {
            for ($i = 0; $i < 4; $i++) {
                Product::factory()->create([
                    'seller_code' => $user->seller_code,
                    'category_id' => rand(1, 4),
                    'status' => 'Active'
                ]);
            }
        }
    }
}
