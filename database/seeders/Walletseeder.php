<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Walletseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('wallets')->insert([
            [
                'id'         => 1,
                'owner_type' => 'App\Models\Student',
                'owner_id'   => 1,
                'balance'    => 80000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 2,
                'owner_type' => 'App\Models\Teacher',
                'owner_id'   => 1,
                'balance'    => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 3,
                'owner_type' => 'App\Models\Admin',
                'owner_id'   => 1,
                'balance'    => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
