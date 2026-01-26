<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\AdmissionApplication;
use App\Models\AdmissionFinalPhase;
use App\Models\AdmissionFirstPhase;
use App\Models\AdmissionRegistration;
use App\Models\BatchMaster;
use App\Models\BloodGroupMaster;
use App\Models\Campus;
use App\Models\Country;
use App\Models\DepartmentMaster;
use App\Models\MainProgram;
use App\Models\Otp;
use App\Models\ProgramGroup;
use App\Models\ReligionMaster;
use App\Models\SmsLog;
use App\Models\StudentProgram;
use App\Models\Subject;
use App\Models\SubjectHasStudentProgam;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasPermission;
use App\Models\UserHasRole;
use GuzzleHttp\Client;
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
        $rec->first_name = $request->firstname;
        $rec->last_name = $request->lastname;
        $rec->mobile_no = $request->mobile_no;
        $rec->mail_id = $request->mail_id;
        $rec->application_type = $request->applicationType;
        $rec->country = $request->country;
        $rec->password = Hash::make($request->password);
        $rec->save();

        Auth::login($rec, true);
        return redirect()->route('otp.verification.page');
    }


    function showOtpVerificationPage(Request $request)
    {
        $userId = Auth::user()->id;
        $isOtpVerified = Otp::where('user_id', $userId)->where('status', 1)->first();
        if (!$isOtpVerified) {
            //OTP on EMAIL

            $otp = StaticController::OtpGenerator($userId);
            $user = AdmissionRegistration::find($userId);
            //OTP ON NUMBER
            $phoneNo = $user->mobile_no;
            $fullname = $user->first_name . ' ' . $user->last_name;
            // $fields = array(
            //     "sender_id" => 'ATNFAS',
            //     "message" => '186603',
            //     "variables_values" => $fullname . '|' . $otp . '|2mins. Admission Committee Salesian College  ',
            //     "route" => "dlt",
            //     "numbers" => $phoneNo,
            // );

            //Send Otp on Whatsapp

            /**Pending Approval from Client */

            //Send Otp on Phone
            // StaticController::otpSender($fields);
            //Send Otp on Email
            $usermail = $user->mail_id;
            $this->sendOTPEmail($otp, $usermail);
        }
        return view('admission.otp-verification');
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
        $userId = Auth::user()->id;
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
            ->where('otp_verification', 1)
            ->first();
        if ($user != null) {
            if ($user && Hash::check($request->registered_password, $user->password)) {
                Auth::login($user, true);
                return redirect()->route('admission.apply.application');
            } else {
                return back()->withErrors(['registered_no' => 'Invalid credentials.']);
            }
        } else {
            return back()->withErrors(['registered_no' => 'No verified account found with the provided details.']);
        }
    }


    function login()
    {
        return view('admission.login');
    }

    function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);
        $userId = Auth::user()->id;
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

            return redirect()->route('admission.apply.application');
        } else {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }
    }





    function showApplicationPage()
    {
        Auth::check();
        $userId = Auth::id();
        //find the application
        $registrationInfo = AdmissionRegistration::with([
            'programinfo.campus',
            'countrymaster',
        ])->where('id', $userId)->firstOrFail();
        if ($registrationInfo->programinfo->name == 'UG') {
            $batch = BatchMaster::where('admission_active_batch', 1)->value('batch_name');
            $campusId = $registrationInfo->programinfo->campus->id;
            $courses = ProgramGroup::whereHas('programInfo', function ($q) use ($campusId) {
                $q->where('campus_id', $campusId);
            })->where('campus_id', $campusId)->get();

            return view('admission.ug-application', [
                'data' => $registrationInfo,
                'courses' => $courses,
                'bloodgroups' => BloodGroupMaster::all(),
                'religions' => ReligionMaster::all(),
                'batch' => $batch,

            ]);
        } else {
            return view('admission.ug-application', ['data' => $registrationInfo]);
        }
    }


    function getMainPrograms(Request $request)
    {
        return MainProgram::where('campus_id', $request->campusId)->get();
    }

    /**
     * Admin Functions
     */
    //Admin Structure to view Registrations

    function admissionRegistrations(Request $request)
    {
        $type = $request->type;
        $campusId =  StaticController::fetchCampusSettings();

        if ($campusId == null) {
            $registrations = AdmissionRegistration::with([
                'countrymaster',
                'programinfo',
                'applicationmaster',
            ])
                ->whereHas('programinfo', function ($query) use ($type) {
                    $query->where('name', $type);
                })
                ->latest()
                ->get();
        } else {

            $registrations = AdmissionRegistration::with([
                'countrymaster',
                'programinfo',
                'applicationmaster',
            ])->whereHas('programinfo.campus', function ($query) use ($campusId) {
                $query->where('id', $campusId);
            })->whereHas('programinfo', function ($query) use ($type) {
                $query->where('name', $type);
            })->latest()
                ->get();
        }

        return view('admin.admission.registration', ['registrations' => $registrations]);
    }

    function ugApplications()
    {
        //fetch user's campus
        $campusId =  StaticController::fetchCampusSettings();
        if ($campusId == null) {
            $data = AdmissionApplication::with([
                'registrationmaster.countrymaster',
                'stdprogramMaster',
            ])
                ->whereHas('registrationmaster.programinfo', function ($query) {
                    $query->where('name', 'UG');
                })
                ->latest()
                ->get();
        } else {

            $data = AdmissionApplication::with([
                'registrationmaster.countrymaster',
                'stdprogramMaster'
            ])
                ->whereHas('registrationmaster.programinfo', function ($query) {
                    $query->where('name', 'UG');
                })
                ->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                    $query->where('id', $campusId);
                })
                ->latest()
                ->get();
        }

        return view('admin.admission.ug.applications', ['data' => $data]);
    }

    function ugPhase1Registrations(Request $request)
    {

        $campusId =  StaticController::fetchCampusSettings();


        if ($campusId == null) {
            if (!empty($request->search)) {
                $search = $request->search;
                $data =   AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_id', 'like', '%' . $search . '%');
                })->latest()->get();
            } else {
                $data =   AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->latest()->get();
            }
        } else {


            if (!empty($request->search)) {
                $search = $request->search;
                $data =   AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_id', 'like', '%' . $search . '%');
                })
                    ->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                        $query->where('id', $campusId);
                    })->latest()->get();
            } else {
                $data =  AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                    $query->where('id', $campusId);
                })->latest()->get();
            }
        }
        return view('admin.admission.ug.phase1', ['data' => $data]);
    }


    function ugApplicationSingle($id)
    {
        $campusId =  StaticController::fetchCampusSettings();

        if ($campusId == null) {
            $data = AdmissionApplication::with([
                'registrationmaster.countrymaster',
            ])->where('id', $id)
                ->whereHas('registrationmaster.programinfo', function ($query) {
                    $query->where('name', 'UG');
                })
                ->firstOrFail();
        } else {
            $data =  AdmissionApplication::with([
                'registrationmaster.countrymaster',
            ])->where('id', $id)
                ->whereHas('registrationmaster.programinfo', function ($query) {
                    $query->where('name', 'UG');
                })->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                    $query->where('id', $campusId);
                })
                ->firstOrFail();
        }

        return view('admin.admission.ug.application-single', ['data' => $data]);
    }

    //sms Notification for phase 1

    function sendPhase1NotificationSingle(Request $request)
    {
        $request->validate([
            'interview_time' => 'required',
        ]);

        $regId = $request->id;
        $interviewDateTime = date('d-m-Y h:i A', strtotime($request->interview_time));
        $applicant = AdmissionRegistration::with('applicationmaster')->where('id', $regId)->firstOrFail();

        if (!$applicant) {
            return back()->with('info', 'No applicant found for the given application ID.');
        }

        //send sms to applicant
        $phoneNo = $applicant->mobile_no;
        $fullname = $applicant->first_name;

        $messageId = 186601; //preset message id for interview notification

        $fields = [
            'body' => json_encode([
                'route' => 'dlt',
                'requests' => [
                    [
                        'sender_id' => 'ATNFAS',
                        'numbers' => $phoneNo,
                        'message' => $messageId,
                        'variables_values' => $fullname . ',' . $interviewDateTime,
                    ]
                ]
            ])
        ];

        //single sms sender
        $apiResponse = StaticController::bulkSmsSender($fields);
        $jsonResponse = json_decode($apiResponse, true);

        if ($jsonResponse['return'] == true) {

            //store sms log if needed
            SmsLog::create([
                'message_id' => $messageId,
                'message_type' => 'Notification',
                'request_id' => $jsonResponse['request_id'],
                'message' => $jsonResponse['message'][0] ?? null,
                'sender_id' => Auth::user()->id,
            ]);

            $checkExistingRecord = AdmissionFirstPhase::where('reg_id', $regId)->first();
            if ($checkExistingRecord == null) {

                //Create Interview Phase 1 List
                AdmissionFirstPhase::create(
                    [
                        'application_id' => $applicant->applicationmaster->id,
                        'reg_id' => $regId,
                        'interview_datetime' => $interviewDateTime,

                    ]
                );
            } else {
                //Update Interview DateTime if record exists
                AdmissionFirstPhase::where('reg_id', $regId)->update(
                    [
                        'interview_datetime' => $interviewDateTime,
                    ]
                );
            }

            //return back with success
            return back()->with('success', 'Interview SMS sent successfully to the applicant.');
        } else {
            return back()->with('error', 'Failed to send Interview SMS. Please try again.');
        }
    }

    function sendPhase1BulkNotification(Request $request)
    {
        $request->validate([
            'programs' => 'required|array|min:1',
            'interview_time' => 'required',
        ]);

        $programs = $request->programs;
        $interviewDateTime = date('d-m-Y h:i A', strtotime($request->interview_time));

        $data = AdmissionApplication::with('registrationmaster:id,mobile_no,first_name,last_name')
            ->whereIn('programme_id', $programs)
            ->where('application_status', 1) //approved applications
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'No applicants found for the selected programs.');
        }
        //send sms to each applicant
        $mobileNumbers = [];
        $firstname = [];
        $messageId = 186601; //preset message id for interview notification
        foreach ($data as $applicant) {
            $phoneNo = $applicant->registrationmaster->mobile_no;
            $mobileNumbers[] = $phoneNo;
            $fullname = $applicant->registrationmaster->first_name;
            $firstname[] = $fullname;
        }
        $fields = [
            'body' => json_encode([
                'route' => 'dlt',
                'requests' => [
                    [
                        'sender_id' => 'ATNFAS',
                        'numbers' => implode(',', $mobileNumbers),
                        'message' => $messageId,
                        'variables_values' => implode(',', $firstname) . ',' . $interviewDateTime,
                    ]
                ]
            ])
        ];
        //bulk sms sender
        $apiResponse = StaticController::bulkSmsSender($fields);
        $jsonResponse = json_decode($apiResponse, true);

        if ($jsonResponse['return'] == true) {

            //store sms log if needed
            SmsLog::create([
                'message_id' => $messageId,
                'message_type' => 'Notification',
                'request_id' => $jsonResponse['request_id'],
                'message' => $jsonResponse['message'][0] ?? null,
                'sender_id' => Auth::user()->id,
            ]);

            //Create Interview Phase 1 List
            foreach ($data as $applicant) {
                AdmissionFirstPhase::create(
                    [
                        'application_id' => $applicant->id,
                        'reg_id' => $applicant->registrationmaster->id,
                        'interview_datetime' => $interviewDateTime,

                    ]
                );
            }

            //return back with success
            return back()->with('success', 'Interview SMS sent successfully to selected applicants.');
        } else {
            return back()->with('error', 'Failed to send Interview SMS. Please try again.');
        }
    }


    function updateUgPhase1Status(Request $request, $id)
    {

        $phase1Record = AdmissionFirstPhase::findOrFail($id);

        $phase1Record->document_verified = $request->document_verified;
        $phase1Record->proficiency_test_status = $request->proficiency_test_status;
        $phase1Record->proficiency_test_remarks = $request->proficiency_test_remarks;
        $phase1Record->dept_interview = $request->dept_interview;
        $phase1Record->dept_interview_remark = $request->dept_interview_remark;
        $phase1Record->mgt_interview_status = $request->mgt_interview_status;
        $phase1Record->mgt_interview_remark = $request->mgt_interview_remark;
        $phase1Record->final_status = $request->final_status;
        $phase1Record->save();




        if (($request->final_status == 1)) {
            $checkExistingRecord =  AdmissionFinalPhase::where('reg_id', $phase1Record->reg_id)->first();
            if ($checkExistingRecord == null) {
                //move to final phase
                Qs::moveToAdmissonFinalPhase($id);
            }
        }

        return back()->with('success', 'Updated successfully.');
    }


    function shiftUgProgram(Request $request, $id)
    {
        $request->validate([
            'new_program' => 'required',
        ]);
        //Find Student Program Groupd
        $new_program = $request->new_program;
        $programGroupId = ProgramGroup::where('program_id', $new_program)->value('id');

        $phase1Record = AdmissionFirstPhase::findOrFail($id);
        $application = AdmissionApplication::findOrFail($phase1Record->application_id);

        // Update the programme_id in the application
        $application->program_group_id = $programGroupId;
        $application->programme_id = $request->new_program;
        $application->save();

        return back()->with('success', 'Applicant program shifted successfully.');
    }


    //Selection Phase 2
    function ugPhase2Registrations(Request $request)
    {
        //Check user has permission
        $campusId =  StaticController::fetchCampusSettings();
        if ($campusId == null) {

            if (!empty($request->search)) {
                $search = $request->search;
                $data =   AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_id', 'like', '%' . $search . '%');
                })->latest()->get();
            } else {
                $data =   AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->latest()->get();
            }
        } else {


            if (!empty($request->search)) {
                $search = $request->search;
                $data =   AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_id', 'like', '%' . $search . '%');
                })
                    ->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                        $query->where('id', $campusId);
                    })->latest()->get();
            } else {
                $data =  AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                ])->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                    $query->where('id', $campusId);
                })->latest()->get();
            }
        }
        return view('admin.admission.ug.phase2', ['data' => $data]);
    }

    function updateUgPhase2Status(Request $request, $id)
    {

        $phase2Record = AdmissionFinalPhase::findOrFail($id);

        $phase2Record->is_doc_validated = $request->is_doc_validated;
        $phase2Record->is_subject_selected = $request->is_subject_selected;
        $phase2Record->uniform_applied = $request->uniform_applied;
        $phase2Record->fee_paid = $request->fee_paid;
        $phase2Record->icard_generated = $request->icard_generated;
        $phase2Record->contract_signed = $request->contract_signed;
        $phase2Record->enroll_status = $request->enroll_status;
        $phase2Record->save();

        if ($request->enroll_status == 1) {
            // Add Information into Student Master Table
            StaticController::addToStudentMaster($phase2Record->reg_id);
        }

        return back()->with('success', 'Updated successfully.');
    }


    function getProgramsByDepartment(Request $request)
    {
        $deptId = $request->deptId;
        $campusId = $request->campusId;

        return SubjectHasStudentProgam::where('subject_id', $deptId)->whereHas('student_program.campusmaster', function ($query) use ($campusId) {
            $query->where('id', $campusId);
        })->with('student_program')
            ->get();
    }


    function sendPhase2BulkNotification(Request $request)
    {
        $request->validate([
            'programs' => 'required|array|min:1',
            'interview_time' => 'required',
        ]);

        $programs = $request->programs;
        $interviewDateTime = date('d-m-Y h:i A', strtotime($request->interview_time));

        $data = AdmissionApplication::with('registrationmaster:id,mobile_no,first_name,last_name')
            ->whereIn('programme_id', $programs)
            ->where('application_status', 1) //approved applications
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'No applicants found for the selected programs.');
        }
        //send sms to each applicant
        $mobileNumbers = [];
        $firstname = [];
        $messageId = 186601; //preset message id for interview notification
        foreach ($data as $applicant) {
            $phoneNo = $applicant->registrationmaster->mobile_no;
            $mobileNumbers[] = $phoneNo;
            $fullname = $applicant->registrationmaster->first_name;
            $firstname[] = $fullname;
        }
        $fields = [
            'body' => json_encode([
                'route' => 'dlt',
                'requests' => [
                    [
                        'sender_id' => 'ATNFAS',
                        'numbers' => implode(',', $mobileNumbers),
                        'message' => $messageId,
                        'variables_values' => implode(',', $firstname) . ',' . $interviewDateTime,
                    ]
                ]
            ])
        ];
        //bulk sms sender
        $apiResponse = StaticController::bulkSmsSender($fields);
        $jsonResponse = json_decode($apiResponse, true);

        if ($jsonResponse['return'] == true) {

            //store sms log if needed
            SmsLog::create([
                'message_id' => $messageId,
                'message_type' => 'Notification',
                'request_id' => $jsonResponse['request_id'],
                'message' => $jsonResponse['message'][0] ?? null,
                'sender_id' => Auth::user()->id,
            ]);
            //return back with success
            return back()->with('success', 'Phase 2 SMS sent successfully to selected Programs.');
        } else {
            return back()->with('error', 'Failed to send Interview SMS. Please try again.');
        }
    }


    function submitApplication(Request $request)
    {
        $request->validate([
            'department' => 'required',
            'course' => 'required',
            'dob' => 'required|date',
            'bloodgroup' => 'required',
            'gender' => 'required',
            'religion' => 'required',
            'mothertongue' => 'required',
            'phychallenged' => 'required',

            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',

            'father_contact' => 'required|digits:10|regex:/^[0-9]+$/',
            'mother_contact' => 'required|digits:10|regex:/^[0-9]+$/',

            'father_occupation' => 'string|max:255',
            'mother_occupation' => 'string|max:255',

            'income' => 'required|regex:/^[0-9]+$/',
            'permanent_address' => 'required',
            'permanent_address_pincode' => 'required|regex:/^[0-9]+$/',

            'photo' => 'required',

            'institution10' => 'required',
            'institution12' => 'required',
            'certificate10' => 'required',
            'certificate12' => 'required',

            'sub1' => 'required|string|max:255',
            'sub2' => 'required|string|max:255',
            'sub3' => 'required|string|max:255',
            'sub4' => 'required|string|max:255',
            'sub5' => 'required|string|max:255',

            'score1' => 'required|regex:/^[0-9]+$/',
            'score2' => 'required|regex:/^[0-9]+$/',
            'score3' => 'required|regex:/^[0-9]+$/',
            'score4' => 'required|regex:/^[0-9]+$/',
            'score5' => 'required|regex:/^[0-9]+$/',
            'caste' =>  'required',
            'adhaar' => 'required',

        ]);
    }

    function logout()
    {
        Auth::logout();
        return redirect()->route('new.admission.login')->with('success', 'Logged out successfully.');
    }
}
