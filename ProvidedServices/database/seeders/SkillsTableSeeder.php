<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Skills;
use Illuminate\Support\Facades\DB;

class SkillsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('skills')->insert([
            ['name' => 'JavaScript'],
            ['name' => 'Python'],
            ['name' => 'PHP'],
            ['name' => 'Laravel'],
            ['name' => 'Vue.js'],
            ['name' => 'React'],
            ['name' => 'Angular'],
            ['name' => 'Node.js'],
            ['name' => 'SQL'],
            ['name' => 'Java'],
            ['name' => 'C++'],
            ['name' => 'Machine Learning'],
            ['name' => 'Data Analysis'],
            ['name' => 'UI/UX Design'],
            ['name' => 'Project Management'],
        ]);
    }
}
