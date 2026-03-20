<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $assets = Asset::all();

        // Ensure we create 5000 transactions randomly attached to existing users and assets
        // We use loops to avoid memory exhaustion with 5000 big objects at once
        foreach (range(1, 10) as $i) {
            Transaction::factory(500)
                ->recycle($users)
                ->recycle($assets)
                ->create();
        }
    }
}
