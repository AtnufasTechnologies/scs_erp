<?php

namespace Database\Seeders;

use App\Models\MenuMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $created_at = Carbon::now();
        $updated_at = Carbon::now();
        MenuMaster::insert([
            //Master menus can be added here
            [
                'slug' => 'batch-master',
                'menu_name' => 'Batch Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'blood-group-master',
                'menu_name' => 'Blood Group Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'campus-master',
                'menu_name' => 'Campus Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'cognitive-level-master',
                'menu_name' => 'Cognitive Level Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'deanery-master',
                'menu_name' => 'Deanery Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'academic-department-master',
                'menu_name' => 'Academic Department Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'main-program-master',
                'menu_name' => 'Main Program Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'program-group-master',
                'menu_name' => 'Program Group Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'student-program-group-master',
                'menu_name' => 'Student Program Group Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'hours-master',
                'menu_name' => 'Hours Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'lecturehall-master',
                'menu_name' => 'Lecturehall Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'rooms-master',
                'menu_name' => 'Rooms Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'religion-master',
                'menu_name' => 'Religion Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'semester-master',
                'menu_name' => 'Semester Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'designation-master',
                'menu_name' => 'Designation Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'department-master',
                'menu_name' => 'Department Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            //Hr Module Menus
            [
                'slug' => 'faculty-master',
                'menu_name' => 'Faculty Master',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'student-master',
                'menu_name' => 'Student Master',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'student-master-sonada',
                'menu_name' => 'Student Master Sonada',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'student-master-siliguri',
                'menu_name' => 'Student Master Siliguri',
                'status' => 'active',
                'module_type' => 'master',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            //PG Menus

            [
                'slug' => 'admission-registration-pg',
                'menu_name' => 'Admission Registration Pg',
                'status' => 'active',
                'module_type' => 'admission-pg',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-application-pg',
                'menu_name' => 'Admission Application Pg',
                'status' => 'active',
                'module_type' => 'admission-pg',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-selection1-pg',
                'menu_name' => 'Admission Selection1 Pg',
                'status' => 'active',
                'module_type' => 'admission-pg',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-selection2-pg',
                'menu_name' => 'Admission Selection2 Pg',
                'status' => 'active',
                'module_type' => 'admission-pg',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-dept-access-pg',
                'menu_name' => 'Admission Dept Access Pg',
                'status' => 'active',
                'module_type' => 'admission-pg',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            //UG Menus


            [
                'slug' => 'admission-registration-ug',
                'menu_name' => 'Admission Registration Ug',
                'status' => 'active',
                'module_type' => 'admission-ug',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-application-ug',
                'menu_name' => 'Admission Application Ug',
                'status' => 'active',
                'module_type' => 'admission-ug',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-selection1-ug',
                'menu_name' => 'Admission Selection1 Ug',
                'status' => 'active',
                'module_type' => 'admission-ug',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-selection2-ug',
                'menu_name' => 'Admission Selection2 Ug',
                'status' => 'active',
                'module_type' => 'admission-ug',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'admission-dept-access-ug',
                'menu_name' => 'Admission Dept Access Ug',
                'status' => 'active',
                'module_type' => 'admission-ug',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            //Academics Menu
            [
                'slug' => 'subject-master',
                'menu_name' => 'Subject Master',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'subject-type-master',
                'menu_name' => 'Subject Type Master',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'program-objective-master',
                'menu_name' => 'Program Objective Master',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'course-objective-master',
                'menu_name' => 'Course Objective Master',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'course-specific-objective',
                'menu_name' => 'Course Specific Objective',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'question-bank',
                'menu_name' => 'Question Bank',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'question-bank-master',
                'menu_name' => 'Question Bank Master',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'attendance-master',
                'menu_name' => 'Attendance Master',
                'status' => 'active',
                'module_type' => 'academics',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            //Acounts Menu
            [
                'slug' => 'late-fee-master',
                'menu_name' => 'Late Fee Master',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'bank-master',
                'menu_name' => 'Bank Master',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'fee-head-master',
                'menu_name' => 'Fee Head Master',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'fee-course-master',
                'menu_name' => 'Fee Course Master',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'fee-structure-master',
                'menu_name' => 'Fee Structure Master',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'fee-collection-master',
                'menu_name' => 'Fee Collection Master',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'fee-allpayments',
                'menu_name' => 'Fee All Payments ',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'admission-application-fee',
                'menu_name' => 'Admission application Fee ',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'faculty-pay-roll',
                'menu_name' => 'Faculty Pay Roll ',
                'status' => 'active',
                'module_type' => 'accounts',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            //Human Resource Menus
            [
                'slug' => 'staff-records',
                'menu_name' => 'Staff Records ',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'roles-and-departments',
                'menu_name' => 'Roles and Departments ',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'basic-pay-structure',
                'menu_name' => 'Basic Pay Structure ',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'apr-report',
                'menu_name' => 'APR Report ',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'grievances',
                'menu_name' => 'Grievances ',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'faculty-applications',
                'menu_name' => 'Applications ',
                'status' => 'active',
                'module_type' => 'hr',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            //Examination Menus
            /**Pre examination */
            [
                'slug' => 'pre-examination-checklist',
                'menu_name' => 'Pre Examination Checklist ',
                'status' => 'active',
                'module_type' => 'examination',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-creation',
                'menu_name' => 'Exam Creation ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'student-exam-registration',
                'menu_name' => 'Student Exam Registration ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'hall-ticket-generation',
                'menu_name' => 'Hall Ticket Generation ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-enrollment-manager',
                'menu_name' => 'Exam Enrollment Manager ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-timetable-manager',
                'menu_name' => 'Exam Timetable Manager ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-seating-arrangement',
                'menu_name' => 'Exam Seating Arrangement ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-invigilators-assign',
                'menu_name' => 'Exam Invigilators Assign ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-contournement-request',
                'menu_name' => 'Exam Contournement Request ',
                'status' => 'active',
                'module_type' => 'pre-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],


            /**During Examination */
            [
                'slug' => 'exam-attendance-capture',
                'menu_name' => 'Exam Attendance Capture ',
                'status' => 'active',
                'module_type' => 'during-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'exam-center-and-room-management',
                'menu_name' => 'Exam Center and Room Management ',
                'status' => 'active',
                'module_type' => 'during-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'packet-management',
                'menu_name' => 'Packet Management ',
                'status' => 'active',
                'module_type' => 'during-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            /**Post Examination */

            [
                'slug' => 'moderation-and-evaluation-management',
                'menu_name' => 'Moderation and Evaluation Management ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'marks-entry-management',
                'menu_name' => 'Marks Entry Management ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 're-evaluation-management',
                'menu_name' => 'Re-Evaluation Management ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],

            [
                'slug' => 'result-publication-management',
                'menu_name' => 'Result Publication Management ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'backlog-management',
                'menu_name' => 'Backlog Management ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'promotion-management',
                'menu_name' => 'Promotion Management ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'student-academic-history-access',
                'menu_name' => 'Student Academic History Access ',
                'status' => 'active',
                'module_type' => 'post-exam',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            //Access Management Menu
            [
                'slug' => 'user-list',
                'menu_name' => 'User List ',
                'status' => 'active',
                'module_type' => 'user-access',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],
            [
                'slug' => 'impersonate-access',
                'menu_name' => 'Impersonate Access ',
                'status' => 'active',
                'module_type' => 'user-access',
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ],






        ]);
    }
}
