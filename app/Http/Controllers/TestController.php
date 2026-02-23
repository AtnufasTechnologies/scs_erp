<?php

namespace App\Http\Controllers;

use App\Mail\ApplicationSuccessMail;
use App\Mail\OtpMail;
use App\Models\ProgramGroup;
use App\Models\StudentMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\UserCampusSetting;
use Illuminate\Http\Request;
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

        $mobile = '7699900008';
        $var1 = 100053;
        $var2 = 9933402478;
        $var3 = 'admissionenquiry@salesaincollge.net';

        $fields = array(
            "sender_id" => 'SCSCLG',
            "message" => '209774',
            "variables_values" => $var1 . '|' . $var2 . '|' . $var3,
            "route" => "dlt",
            "numbers" => $mobile,
        );
        StaticController::otpSender($fields);
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
}
