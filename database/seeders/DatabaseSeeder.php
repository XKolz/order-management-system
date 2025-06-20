<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Create regular user
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'Laptop',
                'price' => 999.99,
                'stock' => 50,
            ],
            [
                'name' => 'Smartphone',
                'price' => 699.99,
                'stock' => 100,
            ],
            [
                'name' => 'Wireless Headphones',
                'price' => 199.99,
                'stock' => 75,
            ],
            [
                'name' => 'Tablet',
                'price' => 449.99,
                'stock' => 30,
            ],
            [
                'name' => 'Smart Watch',
                'price' => 299.99,
                'stock' => 40,
            ],
            [
                'name' => 'USB-C Cable',
                'price' => 19.99,
                'stock' => 200,
            ],
            [
                'name' => 'Wireless Mouse',
                'price' => 49.99,
                'stock' => 80,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'price' => 129.99,
                'stock' => 35,
            ],
            [
                'name' => 'Monitor 27"',
                'price' => 349.99,
                'stock' => 25,
            ],
            [
                'name' => 'Webcam HD',
                'price' => 89.99,
                'stock' => 60,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create additional test users
        User::factory(10)->create();
    }
}