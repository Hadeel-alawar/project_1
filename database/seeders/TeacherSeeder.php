<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Teacher::create([
            'full_name' => 'John Doe',
            'user_name' => 'johndoe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'bio' => 'Experienced software engineer',
            'age' => 35,
            'gender' => 'male',
            'specialization' => 'Web Development',
            'cv' => null,
            'email_otp' => '123456',
            'email_otp_expires_at' => Carbon::now()->addMinutes(10),
            'is_email_verified' => true,
        ]);
 
    }
}
