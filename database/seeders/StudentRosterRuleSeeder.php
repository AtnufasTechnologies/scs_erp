<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentRosterRuleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EXISTING MASTER IDS
        |--------------------------------------------------------------------------
        |
        | Academic Pathway Master
        |
        | 1 = Single MAJOR
        | 2 = Dual Major
        |
        | Degree Track Master
        |
        | 1 = Regular
        | 2 = Honours
        | 3 = Honours with Research
        |
        */

        $SINGLE_MAJOR = 1;
        $DUAL_MAJOR   = 2;

        $REGULAR      = 1;
        $HONOURS      = 2;
        $HONOURS_RESEARCH = 3;

        $rules = [

            /*
            |--------------------------------------------------------------------------
            | DUAL MAJOR
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'DUAL_COMBO1',
                'rule_name' => 'Dual Major - COMBO1',
                'description' =>
                'Dual Major students following COMBO1.',
                'academic_pathway_id' => $DUAL_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'COMBO1',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO1',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 20,
            ],

            [
                'rule_code' => 'DUAL_COMBO2',
                'rule_name' => 'Dual Major - COMBO2',
                'description' =>
                'Dual Major students following COMBO2.',
                'academic_pathway_id' => $DUAL_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'COMBO2',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO2',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 20,
            ],

            /*
            |--------------------------------------------------------------------------
            | DUAL MAJOR - MDC AUTO
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'DUAL_MDC_AUTO',
                'rule_name' => 'Dual Major - MDC Auto',
                'description' =>
                'Dual Major MDC with AUTO selection follows COMBO1.',
                'academic_pathway_id' => $DUAL_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'MDC',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO1',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | DUAL MAJOR - MDC STUDENT CHOICE
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'DUAL_MDC_STUDENT_CHOICE',
                'rule_name' => 'Dual Major - MDC Student Choice',
                'description' =>
                'Dual Major student-selected MDC. Selected MDC cannot belong to either Major department.',
                'academic_pathway_id' => $DUAL_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'MDC',
                'selection_type' => 'STUDENT_CHOICE',
                'roster_source' => 'STUDENT_SELECTION',
                'major_restriction' => 'EXCLUDE_MAJOR_DEPARTMENTS',
                'student_selection_required' => true,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | DUAL MAJOR - COMMON AUTO
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'DUAL_COMMON_AUTO',
                'rule_name' => 'Dual Major - Common Auto',
                'description' =>
                'Dual Major COMMON with AUTO selection follows COMBO1.',
                'academic_pathway_id' => $DUAL_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'COMMON',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO1',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | DUAL MAJOR - COMMON STUDENT CHOICE
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'DUAL_COMMON_STUDENT_CHOICE',
                'rule_name' => 'Dual Major - Common Student Choice',
                'description' =>
                'Dual Major COMMON with student selection.',
                'academic_pathway_id' => $DUAL_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'COMMON',
                'selection_type' => 'STUDENT_CHOICE',
                'roster_source' => 'STUDENT_SELECTION',
                'major_restriction' => 'NONE',
                'student_selection_required' => true,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | SINGLE MAJOR - REGULAR - MDC
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'SINGLE_REGULAR_MDC_AUTO',
                'rule_name' => 'Single Major Regular - MDC Auto',
                'description' =>
                'Single Major Regular compulsory/AUTO MDC follows COMBO1.',
                'academic_pathway_id' => $SINGLE_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'MDC',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO1',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | SINGLE MAJOR - HONOURS - MDC
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'SINGLE_HONOURS_MDC_AUTO',
                'rule_name' => 'Single Major Honours - MDC Auto',
                'description' =>
                'Single Major Honours compulsory/AUTO MDC follows COMBO1.',
                'academic_pathway_id' => $SINGLE_MAJOR,
                'degree_track_id' => $HONOURS,
                'delivery_type' => 'MDC',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO1',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | SINGLE MAJOR - HONOURS WITH RESEARCH - MDC
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'SINGLE_HONOURS_RESEARCH_MDC_AUTO',
                'rule_name' => 'Single Major Honours with Research - MDC Auto',
                'description' =>
                'Single Major Honours with Research compulsory/AUTO MDC follows COMBO1.',
                'academic_pathway_id' => $SINGLE_MAJOR,
                'degree_track_id' => $HONOURS_RESEARCH,
                'delivery_type' => 'MDC',
                'selection_type' => 'AUTO',
                'roster_source' => 'COMBO1',
                'major_restriction' => 'NONE',
                'student_selection_required' => false,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | SINGLE MAJOR - REGULAR - MDC STUDENT CHOICE
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'SINGLE_REGULAR_MDC_STUDENT_CHOICE',
                'rule_name' => 'Single Major Regular - MDC Student Choice',
                'description' =>
                'Single Major Regular student-selected MDC.',
                'academic_pathway_id' => $SINGLE_MAJOR,
                'degree_track_id' => $REGULAR,
                'delivery_type' => 'MDC',
                'selection_type' => 'STUDENT_CHOICE',
                'roster_source' => 'STUDENT_SELECTION',
                'major_restriction' => 'NONE',
                'student_selection_required' => true,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | SINGLE MAJOR - HONOURS - MDC STUDENT CHOICE
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'SINGLE_HONOURS_MDC_STUDENT_CHOICE',
                'rule_name' => 'Single Major Honours - MDC Student Choice',
                'description' =>
                'Single Major Honours student-selected MDC.',
                'academic_pathway_id' => $SINGLE_MAJOR,
                'degree_track_id' => $HONOURS,
                'delivery_type' => 'MDC',
                'selection_type' => 'STUDENT_CHOICE',
                'roster_source' => 'STUDENT_SELECTION',
                'major_restriction' => 'NONE',
                'student_selection_required' => true,
                'priority' => 10,
            ],

            /*
            |--------------------------------------------------------------------------
            | SINGLE MAJOR - HONOURS WITH RESEARCH - MDC STUDENT CHOICE
            |--------------------------------------------------------------------------
            */

            [
                'rule_code' => 'SINGLE_HONOURS_RESEARCH_MDC_STUDENT_CHOICE',
                'rule_name' => 'Single Major Honours with Research - MDC Student Choice',
                'description' =>
                'Single Major Honours with Research student-selected MDC.',
                'academic_pathway_id' => $SINGLE_MAJOR,
                'degree_track_id' => $HONOURS_RESEARCH,
                'delivery_type' => 'MDC',
                'selection_type' => 'STUDENT_CHOICE',
                'roster_source' => 'STUDENT_SELECTION',
                'major_restriction' => 'NONE',
                'student_selection_required' => true,
                'priority' => 10,
            ],
        ];

        foreach ($rules as $rule) {

            /*
            |--------------------------------------------------------------------------
            | Create / Update Rule Master
            |--------------------------------------------------------------------------
            */

            $ruleMaster = DB::table('student_roster_rule_masters')
                ->where('rule_code', $rule['rule_code'])
                ->first();

            if ($ruleMaster) {

                $ruleId = $ruleMaster->id;

                DB::table('student_roster_rule_masters')
                    ->where('id', $ruleId)
                    ->update([
                        'rule_name' => $rule['rule_name'],
                        'description' => $rule['description'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
            } else {

                $ruleId = DB::table('student_roster_rule_masters')
                    ->insertGetId([
                        'rule_code' => $rule['rule_code'],
                        'rule_name' => $rule['rule_name'],
                        'description' => $rule['description'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create / Update Rule Mapping
            |--------------------------------------------------------------------------
            */

            DB::table('student_roster_rule_mappings')
                ->updateOrInsert(
                    [
                        'rule_id' => $ruleId,
                        'academic_pathway_id' =>
                        $rule['academic_pathway_id'],
                        'degree_track_id' =>
                        $rule['degree_track_id'],
                        'delivery_type' =>
                        $rule['delivery_type'],
                        'selection_type' =>
                        $rule['selection_type'],
                    ],
                    [
                        'roster_source' =>
                        $rule['roster_source'],

                        'semester_scope' => 'SAME',
                        'batch_scope' => 'SAME',
                        'program_scope' => 'MULTIPLE',
                        'specialization_scope' => 'ANY',

                        'major_restriction' =>
                        $rule['major_restriction'],

                        'student_selection_required' =>
                        $rule['student_selection_required'],

                        'teaching_group_override' => true,

                        'priority' =>
                        $rule['priority'],

                        'is_active' => true,

                        'updated_at' => now(),
                    ]
                );
        }
    }
}
