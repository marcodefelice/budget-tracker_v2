<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\BudgetTracker\Models\Account;

class AccountSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::factory(8)->create([
            'user_id' => 1
        ]);

        Account::factory(1)->create([
            'user_id' => 1,
            'installement' => 1,
            'installementValue' => 200,
            'type' => 'Credit Card',
            'date' => '2023-06-12',
            'balance' => -2000.00,
        ]);

        Account::factory(1)->create([
            'user_id' => 1,
            'installement' => 1,
            'type' => 'Bank',
            'balance' => 1000.00,
        ]);
    }
}
