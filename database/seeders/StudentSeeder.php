<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::create([
            'full_name' => 'Alice Smith',
            'user_name' => 'alicesmith',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123'),
            'bio' => 'Interested in AI and Data Science',
            'age' => 22,
            'gender' => 'female',
            'specialization' => 'Data Science',
            'email_otp' => '654321',
            'email_otp_expires_at' => Carbon::now()->addMinutes(10),
            'is_email_verified' => true,
        ]);
    }
}
