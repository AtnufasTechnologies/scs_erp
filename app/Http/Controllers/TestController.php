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
use App\Models\SubjectCourseMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
        //old program course list
        $oldCourses =  DB::table('program_course_masters')->count();  // 2219
        //find new program courses List from cp_course_root table
        $courseRoot =  DB::table('cp_course_root')->get(); // total = 2434

        //run foreach loop on courseRoot to verify 
        foreach ($courseRoot as $item) {
            //find program_course_masters exist or not table
            $record =  ProgramCourseMaster::where('course_code', $item->course_code)->first();

            if ($record == null) {
                //add the course in the ProgramCourseMaster Table
                $rec = new ProgramCourseMaster();
                $rec->department = $item->department;
                $rec->academic_year = $item->academic_year;
                $rec->course_type = $item->course_type;
                $rec->credits = $item->credits;
                $rec->internal = $item->internal;
                $rec->external = $item->external;
                $rec->course_code = $item->course_code;
                $rec->course_title = $item->course_title;
                $rec->paper_type = $item->paper_type;
                $rec->save();
            } else {
                $newlyAddedCourses = [];
                //record found...check for ids are same or not but course_code is same
                if ($record->id != $item->id && $record->course_code == $item->course_code) {
                    array_push($newlyAddedCourses, $record->id);
                    /*
                    $course_master_id = $record->id;
                    //find if connected to any Subject
                    $connectedToSubjects = SubjectCourseMaster::with('courseMaster.csos')->where('course_master_id', $course_master_id)->get();

                    //update the program_course_masters table with new id
                    $record->id = $item->id;
                    $record->save();

                    //update the subject_course_masters table with new id
                    if ($connectedToSubjects != null) {
                        SubjectCourseMaster::where('course_master_id', $course_master_id)->update([
                            'course_master_id' => $item->id
                        ]);
                    }
                    //update the CoHasCsos Table
                    if ($connectedToSubjects->csos != null) {
                        CoHasCso::where('co_id', $course_master_id)->update([
                            'course_master_id' => $item->id
                        ]);
                    }*/
                }
            }
        }
        //Show the list of Records Updated
        if ($newlyAddedCourses != null) {
            return response()->json($newlyAddedCourses);
        } else {
            'Fixing Done No newly added courses';
        }
    }
}
