<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HrDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            // Teaching Staff
            ['name' => 'Professor', 'code' => 'PROF', 'category' => 'teaching', 'hierarchy_level' => 1, 'description' => 'Professor - Highest teaching position'],
            ['name' => 'Associate Professor', 'code' => 'ASSOC_PROF', 'category' => 'teaching', 'hierarchy_level' => 2, 'description' => 'Associate Professor'],
            ['name' => 'Assistant Professor', 'code' => 'ASST_PROF', 'category' => 'teaching', 'hierarchy_level' => 3, 'description' => 'Assistant Professor'],
            ['name' => 'Senior Lecturer', 'code' => 'SR_LECT', 'category' => 'teaching', 'hierarchy_level' => 4, 'description' => 'Senior Lecturer'],
            ['name' => 'Lecturer', 'code' => 'LECT', 'category' => 'teaching', 'hierarchy_level' => 5, 'description' => 'Lecturer'],
            ['name' => 'Guest Faculty', 'code' => 'GUEST_FAC', 'category' => 'teaching', 'hierarchy_level' => 10, 'description' => 'Guest Faculty'],
            ['name' => 'Visiting Faculty', 'code' => 'VISIT_FAC', 'category' => 'teaching', 'hierarchy_level' => 11, 'description' => 'Visiting Faculty'],

            // Administrative Staff
            ['name' => 'Director', 'code' => 'DIR', 'category' => 'administrative', 'hierarchy_level' => 1, 'description' => 'Director - Top administrative position'],
            ['name' => 'Dean', 'code' => 'DEAN', 'category' => 'administrative', 'hierarchy_level' => 2, 'description' => 'Dean of School/Faculty'],
            ['name' => 'Head of Department', 'code' => 'HOD', 'category' => 'administrative', 'hierarchy_level' => 3, 'description' => 'Head of Department'],
            ['name' => 'Registrar', 'code' => 'REG', 'category' => 'administrative', 'hierarchy_level' => 4, 'description' => 'Registrar'],
            ['name' => 'Deputy Registrar', 'code' => 'DY_REG', 'category' => 'administrative', 'hierarchy_level' => 5, 'description' => 'Deputy Registrar'],
            ['name' => 'Assistant Registrar', 'code' => 'ASST_REG', 'category' => 'administrative', 'hierarchy_level' => 6, 'description' => 'Assistant Registrar'],

            // Technical Staff
            ['name' => 'Senior Technical Assistant', 'code' => 'SR_TECH', 'category' => 'technical', 'hierarchy_level' => 1, 'description' => 'Senior Technical Assistant'],
            ['name' => 'Technical Assistant', 'code' => 'TECH_ASST', 'category' => 'technical', 'hierarchy_level' => 2, 'description' => 'Technical Assistant'],
            ['name' => 'Lab Assistant', 'code' => 'LAB_ASST', 'category' => 'technical', 'hierarchy_level' => 3, 'description' => 'Laboratory Assistant'],

            // Non-Teaching Staff
            ['name' => 'Senior Administrative Officer', 'code' => 'SAO', 'category' => 'non-teaching', 'hierarchy_level' => 1, 'description' => 'Senior Administrative Officer'],
            ['name' => 'Administrative Officer', 'code' => 'AO', 'category' => 'non-teaching', 'hierarchy_level' => 2, 'description' => 'Administrative Officer'],
            ['name' => 'Senior Clerk', 'code' => 'SR_CLK', 'category' => 'non-teaching', 'hierarchy_level' => 3, 'description' => 'Senior Clerk'],
            ['name' => 'Clerk', 'code' => 'CLK', 'category' => 'non-teaching', 'hierarchy_level' => 4, 'description' => 'Clerk'],
            ['name' => 'Data Entry Operator', 'code' => 'DEO', 'category' => 'non-teaching', 'hierarchy_level' => 5, 'description' => 'Data Entry Operator'],

            // Support Staff
            ['name' => 'Library Assistant', 'code' => 'LIB_ASST', 'category' => 'support', 'hierarchy_level' => 1, 'description' => 'Library Assistant'],
            ['name' => 'Office Assistant', 'code' => 'OFF_ASST', 'category' => 'support', 'hierarchy_level' => 2, 'description' => 'Office Assistant'],
            ['name' => 'Peon', 'code' => 'PEON', 'category' => 'support', 'hierarchy_level' => 3, 'description' => 'Peon'],
        ];

        foreach ($designations as $designation) {
            DB::table('hr_designations')->insert([
                'name' => $designation['name'],
                'code' => $designation['code'],
                'category' => $designation['category'],
                'hierarchy_level' => $designation['hierarchy_level'],
                'description' => $designation['description'],
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
