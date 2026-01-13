<?php

namespace App\Http\Controllers;

use App\Models\AdmissionRegistration;
use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\Country;
use App\Models\MainProgram;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Mews\Captcha\Captcha;

class AdmissionController extends Controller
{
    function index()
    {
        $campus = Campus::all();
        $countries = Country::all();
        return view('admission.registration', [
            'campuses' => $campus,
            'countries' => $countries
        ]);
    }

    public function refreshCaptcha()
    {
        return response()->json(['captcha' => captcha_src('flat')]);
    }
    function admissionRegistration(Request $request)
    {

        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'mobile_no' => 'required|digits:10|unique:admission_registrations|regex:/^[0-9]+$/',
            'mail_id' => 'required|email|unique:admission_registrations|max:255',
            'campus' => 'required',
            'applicationType' => 'required',
            'country' => 'required',
            'password' => 'required|min:6',
            'captcha_input' => 'required|captcha',
        ]);

        //fetch active batch
        $batch =  BatchMaster::where('admission_active_batch', 1)->value('batch_name');

        $rec = new AdmissionRegistration();
        $rec->batch = $batch;
        $rec->fname = $request->firstname;
        $rec->lname = $request->lastname;
        $rec->mobile_no = $request->mobile_no;
        $rec->mail_id = $request->mail_id;
        $rec->application_type = $request->applicationType;
        $rec->country = $request->country;
        $rec->password = Hash::make($request->password);
        $rec->save();

        $userId = $rec->id;
        //send OTP for Verification
        $otp = StaticController::OtpGenerator($userId);

        //OTP on EMAIL
        $this->sendOTPEmail($otp, $request->mail_id);

        //OTP ON NUMBER
        $phoneNo = $request->mobile_no;
        $fullname = $request->firstname . ' ' . $request->lastname;
        $fields = array(
            "sender_id" => 'ATNFAS',
            "message" => '186603',
            "variables_values" => $fullname . '|' . $otp . '|2mins. Admission Committee Salesian College  ',
            "route" => "dlt",
            "numbers" => $phoneNo,
        );
        StaticController::otpSender($fields);

        return view('admission.otp-verification', ['userId' => $userId])->with('success', 'OTP sent to Registered Mail and Number');;
    }

    public function sendOTPEmail($otp, $email)
    {

        $html = View::make('emails.otp-mail', ['otpcode' => $otp])->render();
        $applicant_email = trim((string) $email);
        $response = Http::withToken(env('RESEND_API_KEY'))
            ->post('https://api.resend.com/emails', [
                'from' => 'salesian college autonomous <onboarding@resend.dev>', // Use verified sender
                'to' =>  $applicant_email,
                'subject' => 'Salesian College Autonomous - Otp Verification Code',
                'html' => $html,
            ]);
    }

    function otpResend(Request $request)
    {
        $userId = $request->applicantId;
        Otp::where('user_id', $userId)->where('status', 1)->update(['status' => 0]);

        $otp = StaticController::OtpGenerator($userId);
        $user = AdmissionRegistration::find($userId);
        //OTP ON NUMBER
        $phoneNo = $user->mobile_no;
        $fullname = $user->firstname . ' ' . $user->lastname;
        $fields = array(
            "sender_id" => 'ATNFAS',
            "message" => '186603',
            "variables_values" => $fullname . '|' . $otp . '|2mins. Admission Committee Salesian College  ',
            "route" => "dlt",
            "numbers" => $phoneNo,
        );
        StaticController::otpSender($fields);
        //Send Otp on Email
        $usermail = $user->mail_id;
        $this->sendOTPEmail($otp, $usermail);
        return view('admission.otp-verification', ['userId' => $userId])->with('success', 'OTP sent to Registered Mail and Number');
    }


    function applicantLogin(Request $request)
    {
        $request->validate([

            'registered_no' => 'required',
            'registered_password' => 'required',
        ]);

        $user = AdmissionRegistration::where('mobile_no', $request->registered_no)
            ->orWhere('mail_id', $request->registered_no)
            ->first();

        if ($user && Hash::check($request->registered_password, $user->password)) {
            Auth::login($user);
            return redirect()->route('dashboard');
        } else {
            return back()->withErrors(['registered_no' => 'Invalid credentials.']);
        }
    }

    function verify(Request $request)
    {
        $request->validate([

            'otp' => 'required|numeric',
        ]);
        $userId = $request->applicantId;
        $record = Otp::where('user_id', $userId)
            ->where('otp', $request->otp)
            ->where('status', 1)
            ->first();

        if ($record != null) {
            Otp::where('id', $record->id)->update([
                'status' => 0
            ]);
            AdmissionRegistration::where('id', $userId)->update([
                'otp_verification' => 1,
                'account_status' => 1
            ]);
            $userInfo = AdmissionRegistration::find($userId);
            Auth::login($userInfo, true);
            return redirect('erp/admission/application-form/' . $userId);
        } else {
            return view('admission.otp-verification', ['userId' => $userId])->with('info', 'Invalid OTP');;
        }
    }


    function showApplicationPage($userId)
    {
        //find the application
        $registrationInfo = AdmissionRegistration::with('programinfo')->where('id', $userId)->firstOrFail();
        if ($registrationInfo->programinfo->name == 'UG') {
            return view('admission.ug-application', ['userId' => $userId]);
        } else {
            return view('admission.pg-application', ['userId' => $userId]);
        }
    }


    function getMainPrograms(Request $request)
    {
        return MainProgram::where('campus_id', $request->campusId)->get();
    }


    function logout()
    {
        Auth::logout();
        return redirect('/');

        $this->applicantId = '';
    }
}
