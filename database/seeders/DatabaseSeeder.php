<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\UserType;
use App\Models\UserVerification;
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
        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('Admin@1234!'), // Matches password requirements
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'wmsu_email' => 'admin@wmsu.edu.ph',
            'phone' => '+639172534680',
            'date_of_birth' => '1985-06-12',
            'gender' => 'male',
            'profile_picture' => 'defaults/admin-avatar.jpg',
            'user_type_id' => null,
            'wmsu_dept_id' => null,
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create admin's verification record
        UserVerification::create([
            'user_id' => $admin->id,
            'is_email_verified' => true,
            'is_phone_verified' => true
        ]);

        // College Student (Seller)
        $user1 = User::create([
            'username' => 'drikz',
            'password' => Hash::make('College@1234!'),
            'first_name' => 'Aldrikz',
            'last_name' => 'Suarez',
            'wmsu_email' => 'eh202201066@wmsu.edu.ph',
            'phone' => '+639352178943',
            'date_of_birth' => '2001-08-25',
            'gender' => 'male',
            'user_type_id' => UserType::where('code', 'COL')->first()->id,
            'wmsu_dept_id' => 7,
            'profile_picture' => 'college/profile_pictures/user1-avatar.jpg',
            'wmsu_id_front' => 'college/id_front/student1-id-front.jpg',
            'wmsu_id_back' => 'college/id_back/student1-id-back.jpg',
            'is_seller' => true,
            'seller_code' => 'S00001',
            'email_verified_at' => now(),
        ]);

        // Create user1's verification record
        UserVerification::create([
            'user_id' => $user1->id,
            'is_email_verified' => true,
            'is_phone_verified' => true
        ]);

        // High School Student
        $user2 = User::create([
            'username' => 'hsstudent',
            'password' => Hash::make('Student@1234!'),
            'first_name' => 'John',
            'last_name' => 'Smith',
            'wmsu_email' => 'js20240001@wmsu.edu.ph',
            'phone' => '+639458216790',
            'date_of_birth' => '2007-03-15',
            'gender' => 'male',
            'user_type_id' => UserType::where('code', 'HS')->first()->id,
            'grade_level_id' => 4,
            'profile_picture' => 'highschool/profile_pictures/student2-avatar.jpg',
            'wmsu_id_front' => 'highschool/id_front/student2-id-front.jpg',
            'wmsu_id_back' => 'highschool/id_back/student2-id-back.jpg',
        ]);

        // Create user2's verification record
        UserVerification::create([
            'user_id' => $user2->id,
            'is_email_verified' => false,
            'is_phone_verified' => false
        ]);

        // Employee
        $user3 = User::create([
            'username' => 'employee',
            'password' => Hash::make('Employee@1234!'),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'wmsu_email' => 'jane.doe@wmsu.edu.ph',
            'phone' => '+639265438921',
            'date_of_birth' => '1982-11-30',
            'gender' => 'female',
            'user_type_id' => UserType::where('code', 'EMP')->first()->id,
            'wmsu_dept_id' => null,
            'profile_picture' => 'employee/profile_pictures/emp1-avatar.jpg',
            'email_verified_at' => now(),
        ]);

        // Create employee's verification record
        UserVerification::create([
            'user_id' => $user3->id,
            'is_email_verified' => true,
            'is_phone_verified' => true
        ]);

        // Alumni (Seller)
        $user4 = User::create([
            'username' => 'alumni',
            'password' => Hash::make('Alumni@1234!'),
            'first_name' => 'Robert',
            'last_name' => 'Johnson',
            'phone' => '+639178903456',
            'date_of_birth' => '1995-04-18',
            'gender' => 'male',
            'user_type_id' => UserType::where('code', 'ALM')->first()->id,
            'profile_picture' => 'alumni/profile_pictures/alum1-avatar.jpg',
            'wmsu_id_front' => 'alumni/id_front/alum1-id-front.jpg',
            'wmsu_id_back' => 'alumni/id_back/alum1-id-back.jpg',
            'is_seller' => true,
            'seller_code' => 'S00002',
            'email_verified_at' => now(),
        ]);

        // Create alumni's verification record
        UserVerification::create([
            'user_id' => $user4->id,
            'is_email_verified' => true,
            'is_phone_verified' => true
        ]);

        // Create products for sellers
        $categoryDistribution = [1, 1, 1, 2, 2, 3, 3, 4]; // More products in categories 1-3

        foreach ([$user1, $user4] as $user) {
            for ($i = 0; $i < 4; $i++) {
                Product::factory()->create([
                    'seller_code' => $user->seller_code,
                    'category_id' => $categoryDistribution[array_rand($categoryDistribution)],
                    'status' => 'Active'
                ]);
            }
        }
    }
}
