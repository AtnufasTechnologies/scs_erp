<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HrGradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $gradeLevels = [
            ['name' => 'Level 14', 'code' => 'L14', 'description' => 'Professor - Highest Academic Grade Pay', 'min_salary' => 144200, 'max_salary' => 218200, 'level_order' => 14],
            ['name' => 'Level 13A', 'code' => 'L13A', 'description' => 'Associate Professor - Senior Scale', 'min_salary' => 131400, 'max_salary' => 217100, 'level_order' => 13],
            ['name' => 'Level 13', 'code' => 'L13', 'description' => 'Associate Professor', 'min_salary' => 101500, 'max_salary' => 167400, 'level_order' => 12],
            ['name' => 'Level 12', 'code' => 'L12', 'description' => 'Assistant Professor', 'min_salary' => 78800, 'max_salary' => 209200, 'level_order' => 11],
            ['name' => 'Level 11', 'code' => 'L11', 'description' => 'Senior Lecturer / Senior Scale', 'min_salary' => 67700, 'max_salary' => 208700, 'level_order' => 10],
            ['name' => 'Level 10', 'code' => 'L10', 'description' => 'Lecturer / Assistant Professor Entry', 'min_salary' => 56100, 'max_salary' => 177500, 'level_order' => 9],
            ['name' => 'Level 9', 'code' => 'L9', 'description' => 'Administrative Grade - Senior', 'min_salary' => 53100, 'max_salary' => 167800, 'level_order' => 8],
            ['name' => 'Level 8', 'code' => 'L8', 'description' => 'Administrative Grade', 'min_salary' => 47600, 'max_salary' => 151100, 'level_order' => 7],
            ['name' => 'Level 7', 'code' => 'L7', 'description' => 'Technical / Senior Assistant', 'min_salary' => 44900, 'max_salary' => 142400, 'level_order' => 6],
            ['name' => 'Level 6', 'code' => 'L6', 'description' => 'Assistant / Junior Scale', 'min_salary' => 35400, 'max_salary' => 112400, 'level_order' => 5],
            ['name' => 'Level 5', 'code' => 'L5', 'description' => 'Clerk / Data Entry', 'min_salary' => 29200, 'max_salary' => 92300, 'level_order' => 4],
            ['name' => 'Level 4', 'code' => 'L4', 'description' => 'Junior Assistant', 'min_salary' => 25500, 'max_salary' => 81100, 'level_order' => 3],
            ['name' => 'Level 3', 'code' => 'L3', 'description' => 'Support Staff - Senior', 'min_salary' => 21700, 'max_salary' => 69100, 'level_order' => 2],
            ['name' => 'Level 2', 'code' => 'L2', 'description' => 'Support Staff', 'min_salary' => 19900, 'max_salary' => 63200, 'level_order' => 1],
            ['name' => 'Level 1', 'code' => 'L1', 'description' => 'Entry Level Support', 'min_salary' => 18000, 'max_salary' => 56900, 'level_order' => 0],
        ];

        foreach ($gradeLevels as $level) {
            DB::table('hr_grade_levels')->insert([
                'name' => $level['name'],
                'code' => $level['code'],
                'description' => $level['description'],
                'min_salary' => $level['min_salary'],
                'max_salary' => $level['max_salary'],
                'level_order' => $level['level_order'],
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
