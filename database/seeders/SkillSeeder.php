<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'Web Development',
            'Graphic Design',
            'Digital Marketing',
            'Project Management',
            'Data Analysis',
            'Machine Learning',
            'UI/UX Design',
            'Cybersecurity',
            'Mobile App Development',
            'Cloud Computing',
        ];

        foreach ($skills as $skill) {
            DB::table('skills')->insert([
                'name' => $skill,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
