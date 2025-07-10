<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Adminseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert([
            'id'       => 1,
            'name'     => 'ameer',
            'email'    => 'ameer2@gmail.com',
            'password' => bcrypt('ameer12345'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
