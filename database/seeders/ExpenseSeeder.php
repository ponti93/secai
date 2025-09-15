<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Expense;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        // Create expenses for the first (and only) user
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No user found. Please create a user first.');
            return;
        }

        // Create sample expenses
        $expenses = [
            [
                'description' => 'Office supplies for Q4',
                'amount' => 125.50,
                'category' => 'office-supplies',
                'expense_date' => now()->subDays(2),
                'vendor' => 'Office Depot',
                'status' => 'approved',
                'notes' => 'Purchased pens, paper, and notebooks'
            ],
            [
                'description' => 'Client dinner meeting',
                'amount' => 89.75,
                'category' => 'meals',
                'expense_date' => now()->subDays(1),
                'vendor' => 'Restaurant ABC',
                'status' => 'pending',
                'notes' => 'Business dinner with potential client'
            ],
            [
                'description' => 'Software license renewal',
                'amount' => 299.00,
                'category' => 'software',
                'expense_date' => now()->subDays(3),
                'vendor' => 'TechCorp Inc',
                'status' => 'approved',
                'notes' => 'Annual subscription for project management tool'
            ],
            [
                'description' => 'Flight to conference',
                'amount' => 450.00,
                'category' => 'travel',
                'expense_date' => now()->subDays(5),
                'vendor' => 'Airline XYZ',
                'status' => 'approved',
                'notes' => 'Business trip to annual conference'
            ],
            [
                'description' => 'Internet bill',
                'amount' => 75.00,
                'category' => 'utilities',
                'expense_date' => now()->subDays(7),
                'vendor' => 'Internet Provider',
                'status' => 'approved',
                'notes' => 'Monthly internet service'
            ],
            [
                'description' => 'Personal lunch',
                'amount' => 25.00,
                'category' => 'meals',
                'expense_date' => now()->subDays(4),
                'vendor' => 'Local Cafe',
                'status' => 'rejected',
                'notes' => 'Personal expense, not business related'
            ],
            [
                'description' => 'Office party decorations',
                'amount' => 150.00,
                'category' => 'other',
                'expense_date' => now()->subDays(6),
                'vendor' => 'Party Store',
                'status' => 'rejected',
                'notes' => 'Not essential business expense'
            ]
        ];

        foreach ($expenses as $expenseData) {
            Expense::create([
                'user_id' => $user->id,
                ...$expenseData
            ]);
        }

        $this->command->info('Sample expenses created successfully!');
    }
}
