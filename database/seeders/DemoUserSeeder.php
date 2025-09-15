<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user if it doesn't exist
        $user = User::where('email', 'admin@admin.com')->first();
        
        if (!$user) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'preferences' => [],
            ]);
            
            $this->command->info('Demo user created successfully!');
            $this->command->info('Email: admin@admin.com');
            $this->command->info('Password: admin123');
        } else {
            $this->command->info('Demo user already exists!');
        }
    }
}