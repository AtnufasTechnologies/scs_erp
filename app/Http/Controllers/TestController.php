<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSuccessMail;
use App\Mail\OtpMail;
use App\Models\AdmissionFinalPhase;
use App\Models\AdmissionFirstPhase;
use App\Models\BatchMaster;
use App\Models\ExamSystem\Student;
use App\Models\FeeCourseMaster;
use App\Models\FeesStructure;
use App\Models\FeeStructureGroup;
use App\Models\FeeStructureHasManyProgram;
use App\Models\ProgramGroup;
use App\Models\SmsTemplate;
use App\Models\StudentAccountPivot;
use App\Models\StudentMaster;
use App\Models\StudentMasterUserPivot;
use App\Models\StudentPayment;
use App\Models\SubjectHasDeptAdmin;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasRole;
use App\Models\CiaMark;
use App\Models\CoHasCso;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramCourseMasterNew;
use App\Models\StudentCourseInfo;
use App\Models\StudentSemesterConfig;
use App\Models\SubjectCourseMaster;
use App\Models\SyllabusManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TestController extends Controller
{
    function DeptCampusMapping()
    {

        //get the campus_id of each dept user inside

        $data = SubjectHasDeptAdmin::with('subject')->get();

        foreach ($data as $item) {

            $userId = $item->user_id;
            $campusId = $item->subject->campus_id;
            $created = 0;
            $check = UserCampusSetting::where('user_id', $userId)->where('campus_id', $campusId)->doesntExist();
            if ($check) {
                $new = new UserCampusSetting();
                $new->user_id = $userId;
                $new->campus_id = $campusId;
                $new->save();
                $created++;
            }
        }
        return 'Created ' . $created . ' records';
    }
    //mail testing
    function mailTest()
    {
        $email = "prof.johngaurav@gmail.com";
        $applicant_email = trim((string) $email);

        //OTP  --WORKING FINE
        $otp = rand(100000, 999999);

        $details = [
            'otp' => $otp,
        ];
        Mail::to($applicant_email)->send(new OtpMail($details));
        dd('Mail sent successfully');

        //Application Mail Testing -- WORKING FINE
        $applicationId = 222111;
        /*  Mail::to($applicant_email)->send(new ApplicationSuccessMail($applicationId));
        dd('Application Mail sent successfully');*/
    }

    //sms Testing
    function smsTest(Request $request)
    {
        $mobile = '8100556241';
        $var1 = '123456'; //dynamic variable for applicant name and application id
        $var2 = 9933402478;
        $var3 = 'admissionenquiry@salesiancollege.net';
        $fields = array(
            "sender_id" => 'SCSCLG',
            "message" => '209860',
            "variables_values" => $var1 . '|' . $var2 . '|' . $var3,
            "route" => "dlt",
            "numbers" => $mobile,
        );

        StaticController::smsSender($fields);
        dd('SMS sent successfully to ' . $mobile);
    }

    //student master - fixing new program 

    function studentMasterProgramFixing()
    {
        $data = StudentMaster::all();
        for ($i = 0; $i < $data->count(); $i++) {
            $prgGroupId = $data[$i]->programme;
            $programId =  ProgramGroup::where('id', $prgGroupId)->value('program_id');

            if ($programId) {
                StudentMaster::where('id', $data[$i]->id)->update(['new_program_id' => $programId]);
            }
        }

        dd('All student master records updated with new program id');
    }

    function rollnoFixStudentPayment()
    {
        $data = StudentPayment::all();
        for ($i = 0; $i < $data->count(); $i++) {
            $studentId = $data[$i]->student_id;
            $rollNo =  StudentMaster::where('id', $studentId)->value('roll_no');

            if ($rollNo) {
                StudentPayment::where('id', $data[$i]->id)->update(['roll_no' => $rollNo]);
            }
        }

        dd('All student payment records updated with roll no');
    }

    function createStudentLogin()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $count = 0;
        StudentMaster::where('is_left', 0)->chunk(400, function ($students) use (&$count) {
            foreach ($students as $item) {
                $email = $item->mail_id;
                // If email exists in users table, skip this student
                if ($email && User::where('email', $email)->exists()) {
                    continue; // Skip duplicate email
                }

                $mobile = $item->mobile_no;
                // If phone is empty or already exists, skip this student
                if (empty($mobile) || User::where('phone', $mobile)->exists()) {
                    continue; // Skip duplicate or empty phone
                }

                $user_id =  User::insertGetId([
                    'name' => $item->first_name . ' ' . $item->last_name,
                    'roll_no' => $item->roll_no,
                    'email' => $email,
                    'phone' => $mobile,
                    'password' => Hash::make($item->roll_no), // Setting initial password as roll number
                    'decrypted_password' => $item->roll_no,
                    'status' => 'ACTIVE', // Storing decrypted password (not recommended for production)
                ]);

                UserHasRole::create([
                    'user_id' => $user_id,
                    'role_name' => 'student', //student , alumni
                ]);

                //Adding Pivot Table entry to Link Tables
                StudentMasterUserPivot::create([
                    'student_master_id' => $item->id,
                    'user_id' => $user_id,
                ]);

                $count++;
            }
        });

        dd('All student login records created successfully. Total: ' . $count);
    }

    function delAllStudentAccount()
    {
        $data =  UserHasRole::where('role_name', 'student')->get();
        foreach ($data as $item) {
            User::find($item->user_id)->forceDelete();
            UserHasRole::where('user_id', $item->user_id)->forceDelete();
        }
        dd('All student login records deleted successfully');
    }

    function feesIssueFixing()
    {

        $data = FeeStructureGroup::with('programgroup')->get();
        for ($i = 0; $i < count($data); $i++) {
            FeeStructureGroup::where('id', $data[$i]->id)->update([
                'student_program_id' => $data[$i]->programgroup->program_id
            ]);
        }
        dd('Student Program Id fixing completed successfully');

        /*
        $batchSize = 100; // Adjust the batch size as needed
        $count = 0;

        $batchId =  BatchMaster::where('admission_active_batch', 1)->first()->id;

        return   $feestructures = FeesStructure::where('batch_id', $batchId)->count();

        for ($i = 0; $i < count($feestructures); $i++) {
            $fee_structure_id = $feestructures[$i]->id;
            //get the the list of attached student programs in the fee_course_masters
            $data =  FeeCourseMaster::with('feegroups.programinfo')->where('id', $feestructures[$i]->program_id)->first();
            $feegroups =  $data->feegroups;

            //adding studentprogram id to fee_structure_has_many_programs
            for ($j = 0; $j < count($feegroups); $j++) {

                $checkIfExists = FeeStructureHasManyProgram::where('fee_structure_id', $fee_structure_id)
                    ->where('std_program_id', $data->feegroups[$j]->student_program_id)
                    ->first(); // Check if the record already exists

                if ($checkIfExists == null) {
                    FeeStructureHasManyProgram::create([
                        'fee_structure_id' => $fee_structure_id,
                        'std_program_id' => $data->feegroups[$j]->student_program_id,
                    ]);
                }
            }
        }
        dd('Fee structure fixing completed successfully. Total records updated: ' . $count);

        */
    }

    function fixAdmissionEnrollment()
    {
        $data = AdmissionFirstPhase::where('final_status', 1)->get();
        foreach ($data as  $item) {
            //check if record already exists in admission_final_phase table
            $checkIfExists = AdmissionFinalPhase::where('reg_id', $item->reg_id)
                ->first();
            // Check if the record already exists
            if ($checkIfExists == null) {
                AdmissionFinalPhase::create([
                    'application_id' => $item->id,
                    'reg_id' => $item->reg_id,
                    'interview_datetime' => $item->interview_datetime,
                    'is_doc_validated' => 0,
                    'is_subject_selected' => 0,
                    'uniform_applied' => 0,
                    'fee_paid' => 0,
                    'icard_generated' => 0,
                    'contract_signed' => 0,
                    'enroll_status' => 0
                ]);
            }
        }

        return dd('Admission final phase records created successfully for all first phase completed applications');
    }

    function courseFix()
    {
        //finding duplicate course codes
        // return    $duplicates = ProgramCourseMaster::whereIn('course_code', function ($query) {
        //     $query->select('course_code')
        //         ->from('program_course_masters')
        //         ->groupBy('course_code')
        //         ->havingRaw('COUNT(*) > 1');
        // })->withCount('csos')
        //     ->orderBy('course_code')
        //     ->get();


        $olddata = ProgramCourseMaster::get();
        // $newdata = ProgramCourseMasterNew::get();


        foreach ($olddata as $item) {
            //old data match the course_code with newdata course_code
            $matchCheck =  ProgramCourseMasterNew::where('course_code', $item->course_code)->first();
            //if match is Yes 
            if ($matchCheck != null) {
                //if same course existing in subjectCourseMaster
                $checkSubjectCourseMaster = SubjectCourseMaster::where('course_master_id', $matchCheck)->get();
                //if exist in above check then update the SubjectSourseMaster where olddata course_id to newdata course_id
                if ($checkSubjectCourseMaster != null) {
                    SubjectCourseMaster::where('course_master_id', $item->id)->update([
                        'course_master_id' =>   $matchCheck->id
                    ]);
                }
                //check CoHasCos for subunit mapping
                $CosdataCheck =  CoHasCso::where('co_id', $item->id)->get();
                if ($CosdataCheck != null) {
                    CoHasCso::where('co_id', $item->id)->update([
                        'co_id' => $matchCheck->id
                    ]);
                }
            } else {

                //if match is no

                //insert the record in the newdata table...
                $rec = new ProgramCourseMasterNew();
                $rec->academic_year = $item->academic_year;
                $rec->course_code = Str::upper($item->course_code);
                $rec->course_title = $item->course_title;
                $rec->course_type = $item->course_type;
                $rec->department = $item->subject_id;
                $rec->internal = $item->internal;
                $rec->external =  $item->external;
                $rec->total = $item->internal + $item->external;
                $rec->credits = $item->credits;
                $rec->paper_type_id = $item->paper_type_id;
                $rec->total_alloted_hours = $item->total_alloted_hours;
                $rec->is_deleted = 0;
                $rec->save();

                //fetch the newly created record id
                $newlyCreatedId = $rec->id;
                //update the SubjectCourseMaster where olddata course_id to newly created course_id
                if ($checkSubjectCourseMaster != null) {
                    SubjectCourseMaster::where('course_master_id', $item->id)->update([
                        'course_master_id' =>   $newlyCreatedId
                    ]);
                }

                //check CoHasCos for subunit mapping
                $CosdataCheck =  CoHasCso::where('co_id', $item->id)->get();
                if ($CosdataCheck != null) {
                    CoHasCso::where('co_id', $item->id)->update([
                        'co_id' => $newlyCreatedId
                    ]);
                }
            }
        }

        //Done... Check and review
        dd('Migration Complete .... Please check and Review');
    }

    function fixFeeStructure()
    {
        //find fee structure data
        $feestructures = FeesStructure::all();

        //run loop to fix all issues
        foreach ($feestructures as $item) {
            //check the individual Fee Structure Record
            $feestructure_record = FeesStructure::find($item->id);
            $fee_course_master_id = $item->course_name;

            //find connected links with fee_course_masteer_id in fee_structure_groups
            $connectedStudentPrograms = FeeStructureGroup::where('fee_course_master_id', $fee_course_master_id)->get();

            //delete links in fee_structure_has_many_programs having fee_structure_id
            $fee_structure_id = $item->id;
            FeeStructureHasManyProgram::where('fee_structure_id', $fee_structure_id)->delete();

            //now create the new corrected ones

            foreach ($connectedStudentPrograms as $rec) {
                FeeStructureHasManyProgram::create([
                    'fee_structure_id' => $fee_structure_id,
                    'std_program_id' => $rec->student_program_id,
                ]);
            }
        }
        dd('Fee Structure Fixing Complete');
    }


    function fixSyllabusIssue()
    {

        $syllabusManager = SyllabusManager::all();

        foreach ($syllabusManager as $item) {
            //match with old Program Master
            $record = DB::table('program_course_masters_old')->where('id', $item->co_id)->first();
            if ($record != null) {

                //get the course_code
                $course_code = $record->course_code;
                //Match it in the new Table to find the ID
                $newRecord  =   DB::table('program_course_masters')->where('course_code', $course_code)->first();

                //update the Syllabus Mapping
                SyllabusManager::where('co_id', $item->co_id)->update([
                    'co_id' => $newRecord->id
                ]);
            }
        }

        dd('all corrections done');
    }

    function fixStudentSemester()
    {
        /*
        $data = StudentMaster::all();
        foreach ($data as $item) {
            //find the Unique semesters for every student
            $semesterdata =  StudentCourseInfo::where('student_id', $item->id)->distinct()->get('semester');
            $current_semester_id = $semesterdata->last();

            for ($i = 0; $i < count($semesterdata); $i++) {
                StudentSemesterConfig::create([
                    'student_id' => $item->id,
                    'semester_id' => $semesterdata[$i]->semester,
                    'current_semester' => $semesterdata[$i]->semester == $current_semester_id->semester ? 1 : 0,
                ]);
            }
        }
        */

        $data = StudentMaster::where('batch', 11)->get();
        foreach ($data as $item) {
            //find the Unique semesters for every student
            // $semesterdata =  StudentCourseInfo::where('student_id', $item->id)->distinct()->get('semester');
            // $current_semester_id = $semesterdata->last();

            // for ($i = 0; $i < count($semesterdata); $i++) {

            // }
            // dd('student Semester mapping done');

            StudentSemesterConfig::create([
                'student_id' => $item->id,
                'semester_id' => 1,
                'current_semester' => 1,
            ]);
        }
        dd('1st Year Student Semester mapping done');
    }
}
