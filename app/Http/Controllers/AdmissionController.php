<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Mail\ApplicationSuccessMail;
use App\Models\AdmissionApplication;
use App\Models\AdmissionApplicationPaymentLog;
use App\Models\AdmissionFinalPhase;
use App\Models\AdmissionFirstPhase;
use App\Models\AdmissionRegistration;
use App\Models\AdmissionSetting;
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
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectHasStudentProgam;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasPermission;
use App\Models\UserHasRole;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
        $rec->campus_id = $request->campus;
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
            $fields = array(
                "sender_id" => 'ATNFAS',
                "message" => '186603',
                "variables_values" => $fullname . '|' . $otp . '|2mins. Admission Committee Salesian College  ',
                "route" => "dlt",
                "numbers" => $phoneNo,
            );

            //Send Otp on Whatsapp

            /**Pending Approval from Client */

            //Send Otp on Phone
            StaticController::otpSender($fields);
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
            'campusmaster',
            'countrymaster',
        ])->where('id', $userId)->firstOrFail();

        if ($registrationInfo->application_type == 'UG') {
            //UG Application Page
            $batch = BatchMaster::where('admission_active_batch', 1)->value('batch_name');
            $campusId = $registrationInfo->campus_id;
            $courses = ProgramGroup::whereHas('programInfo', function ($q) use ($campusId) {
                $q->where('campus_id', $campusId);
            })->where('campus_id', $campusId)->get();

            $academic_departments = Subject::where('campus_id', $campusId)
                ->where('main_program_type', 'UG')
                ->get();

            $application = AdmissionApplication::where('registration_id', $registrationInfo->id)->first();
            if ($application == null) {
                view('admission.ug-application', [
                    'data' => $registrationInfo,
                    'courses' => $courses,
                    'bloodgroups' => BloodGroupMaster::all(),
                    'religions' => ReligionMaster::all(),
                    'batch' => $batch,
                    'academic_departments' => $academic_departments,

                ]);
            } else {
                if ($application->payment_gateway_ref != null && $application->payment_gateway_status == 'success') {
                    return   $this->showSuccessPage();
                } else {
                    return redirect()->route('admission.payment.page');
                }
            }
        } else {

            //PG Application Page
            return view('admission.pg-application', ['data' => $registrationInfo]);
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


    function ugApplicationSubmit(Request $request)
    {

        $request->validate([
            'photo' => 'required',
            'department' => 'required',
            'course' => 'required',
            'dob' => 'required|date',
            'bloodgroup' => 'required',
            'gender' => 'required',
            'religion' => 'required',
            'mothertongue' => 'required',
            'phychallenged' => 'required',
            'caste' =>  'required',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'father_contact' => 'required',
            'mother_contact' => 'required',
            'father_occupation' => 'string|max:255',
            'mother_occupation' => 'string|max:255',
            'income' => 'required',
            'permanent_address' => 'required',
            'district' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'pincode' => 'required',
            'local_address' => 'required',
            'local_district' => 'required|string|max:255',
            'local_city' => 'required|string|max:255',
            'local_pincode' => 'required',
            //Class 10 Details
            // 'institution10' => 'required',
            // 'rollno10' => 'required|string|max:255',
            // 'board10' => 'required|string|max:255',
            // 'passingyear10' => 'required',
            // 'certificate10' => 'required',
            // 'percentage10' => 'required',
            // 'passmark10' => 'required',
            //Class 10th Subjects
            // 'subject10_1' => 'required|string|max:255',
            // 'score10_1' => 'required',

            // 'subject10_2' => 'required|string|max:255',
            // 'score10_2' => 'required',

            // 'subject10_3' => 'required|string|max:255',
            // 'score10_3' => 'required',

            // 'subject10_4' => 'required|string|max:255',
            // 'score10_4' => 'required',

            // 'subject10_5' => 'required|string|max:255',
            // 'score10_5' => 'required',


            //Class 12 Details
            // 'institution12' => 'required',
            // 'rollno12' => 'required|string|max:255',
            // 'board12' => 'required|string|max:255',
            // 'passingyear12' => 'required',
            // 'certificate12' => 'required',
            // 'percentage12' => 'required',
            // 'passmark12' => 'required',
            //Class 12th Subjects
            // 'subject12_1' => 'required|string|max:255',
            // 'score12_1' => 'required',

            // 'subject12_2' => 'required|string|max:255',
            // 'score12_2' => 'required',

            // 'subject12_3' => 'required|string|max:255',
            // 'score12_3' => 'required',

            // 'subject12_4' => 'required|string|max:255',
            // 'score12_4' => 'required',

            // 'subject12_5' => 'required|string|max:255',
            // 'score12_5' => 'required',

            //Baptism Certificate
        ]);

        $laptop = !empty($request->laptop_checkbox) ? 1 : 0;
        $teaestate = !empty($request->teaestate_checkbox) ? 1 : 0;


        if ($request->religion == 10) {
            $request->validate([
                'baptism' => 'required',
            ]);
            $baptism =  $request->baptism;
            $baptismFilename = StaticController::s3_file_uploader($baptism, 'admission_baptisms');
        }

        // Save application
        $userId = Auth::user()->id;
        $registrationId = AdmissionRegistration::where('id', $userId)->value('id');

        $application = new AdmissionApplication();
        $application->user_id = $userId;
        $application->registration_id = $registrationId;
        $application->department = $request->department;
        $application->course = $request->course;
        $application->dob = $request->dob;
        $application->bloodgroup = $request->bloodgroup;
        $application->gender = $request->gender;
        $application->religion = $request->religion;
        $application->mothertongue = $request->mothertongue;
        $application->phychallenged = $request->phychallenged;
        $application->caste = $request->caste;
        $application->father_name = $request->father_name;
        $application->mother_name = $request->mother_name;
        $application->father_contact = $request->father_contact;
        $application->mother_contact = $request->mother_contact;
        $application->father_occupation = $request->father_occupation;
        $application->mother_occupation = $request->mother_occupation;
        $application->income = $request->income;
        $application->permanent_address = $request->permanent_address;
        $application->district = $request->district;
        $application->city = $request->city;
        $application->pincode = $request->pincode;
        $application->local_address = $request->local_address;
        $application->local_district = $request->local_district;
        $application->local_city = $request->local_city;
        $application->local_pincode = $request->local_pincode;
        $application->laptop = $laptop;
        $application->teaestate = $teaestate;

        // Handle file uploads

        $photo =  $request->photo;
        $photoFilename = StaticController::s3_resize_image_uploader($photo, 'admission_photos', 300, 300);

        $certificate10 =  $request->certificate10;
        $certificate10Filename = StaticController::s3_file_uploader($certificate10, 'admission_cerificates10');

        $certificate12 =  $request->certificate12;
        $certificate12Filename = StaticController::s3_file_uploader($certificate12, 'admission_cerificates12');

        $application->photo = $photoFilename;
        $application->certificate10 = $certificate10Filename;
        $application->certificate12 = $certificate12Filename;

        if (isset($baptismFilename)) {
            $application->baptism = $baptismFilename;
        } else {
            $application->baptism = null;
        }
        // Academic details
        $application->institution10 = $request->institution10;
        $application->rollno10 = $request->rollno10;
        $application->board10 = $request->board10;
        $application->passingyear10 = $request->passingyear10;
        $application->percentage10 = $request->percentage10;
        $application->passmark10 = $request->passmark10;
        $application->fullmark10 = $request->fullmark10;

        $application->subject10_1 = $request->subject10_1;
        $application->score10_1 = $request->score10_1;


        $application->subject10_2 = $request->subject10_2;
        $application->score10_2 = $request->score10_2;


        $application->subject10_3 = $request->subject10_3;
        $application->score10_3 = $request->score10_3;

        $application->subject10_4 = $request->subject10_4;
        $application->score10_4 = $request->score10_4;

        $application->subject10_5 = $request->subject10_5;
        $application->score10_5 = $request->score10_5;


        $application->institution12 = $request->institution12;
        $application->rollno12 = $request->rollno12;
        $application->board12 = $request->board12;
        $application->passingyear12 = $request->passingyear12;
        $application->percentage12 = $request->percentage12;
        $application->passmark12 = $request->passmark12;
        $application->fullmark12 = $request->fullmark12;

        $application->subject12_1 = $request->subject12_1;
        $application->score12_1 = $request->score12_1;

        $application->subject12_2 = $request->subject12_2;
        $application->score12_2 = $request->score12_2;

        $application->subject12_3 = $request->subject12_3;
        $application->score12_3 = $request->score12_3;

        $application->subject12_4 = $request->subject12_4;
        $application->score12_4 = $request->score12_4;

        $application->subject12_5 = $request->subject12_5;
        $application->score12_5 = $request->score12_5;

        // Payment fields (to be filled after payment)
        $application->gateway_type = 'easebuzz';
        $application->payment_gateway_ref = null;
        $application->captured_amount = null;
        $application->hash = null;
        $application->payment_gateway_status = null;

        $application->save();

        return redirect()->route('admission.payment.checkout')->with('success', 'Application Saved successfully. Please proceed to payment.');
    }

    function paymentCheckout()
    {

        $userId = Auth::user()->id;
        $applicationRecord = AdmissionApplication::where('user_id', $userId)->first();
        if ($applicationRecord != null) {

            if ($applicationRecord->payment_gateway_status == 'success' && $applicationRecord->payment_gateway_ref != null) {

                return redirect()->route('admission.application.success')->with('success', 'Payment already completed for your application.');
            } else {
                $generatedNo = $userId . $applicationRecord->id . rand(1000, 9999);
                $appl_no = encrypt($generatedNo);
                AdmissionApplication::where('id', $userId)->update([
                    'application_code' => $generatedNo,
                ]);
                $data = AdmissionRegistration::with([
                    'applicationmaster.academicDeptMaster',
                    'applicationmaster.stdCourseMaster'
                ])->where('id', $userId)->first();

                if ($data->programinfo->name == 'PG') {
                    $amount =  AdmissionSetting::where('application_fee_pg')
                        ->value('application_fee_pg');
                } else {
                    $amount =  AdmissionSetting::where('application_fee_ug')
                        ->value('application_fee_ug');
                }
                return view('admission.payment-checkout', ['data' => $data, 'amount' => $amount]);
            }
        } else {
            return redirect()->route('admission.apply.application');
        }
    }

    function getCombinationsByDepartment(Request $request)
    {
        $deptId = $request->departmentId;
        $campusId = $request->campusId;
        $batchId =  BatchMaster::where('admission_active_batch', 1)->value('id');
        return SubjectHasStudentProgam::where('subject_id', $deptId)->where('campus_id', $campusId)
            ->where('batch_id', $batchId)
            ->with('student_program')
            ->get();
    }

    function initateEaseBuzzPayment(Request $request)
    {
        $request->validate([
            'application_code' => 'required',
            'amount' => 'required|numeric',
        ]);

        $userId = Auth::user()->id;
        $applicationRegRecord = AdmissionRegistration::where('user_id', $userId)->first();
        $fullname = $applicationRegRecord->first_name . ' ' . $applicationRegRecord->last_name;
        $email = $applicationRegRecord->email;
        $phone = $applicationRegRecord->mobile_no;
        $payableAmount = $request->amount;

        //Payment Split Logic
        if ($applicationRegRecord->campus == 1) {
            $split =  'SAL_SONADA' + (float) $payableAmount;
        } else {
            $split =  'SAL_CAMPUS' + (float) $payableAmount;
        }
        /** Easebuzz Params */
        $key = env('EASEBUZZ_KEY');
        $salt = env('EASEBUZZ_SALT');
        $txnid = $request->application_code;
        $productinfo = 'Salesian College Autonomous - Admission Form Payment';

        $hashString = "$key|$txnid|$payableAmount|$productinfo|$fullname|$email|$userId||||||||||$salt";
        $hash = strtolower(hash('sha512', $hashString));

        /** Initiate Payment */
        $client = new \GuzzleHttp\Client();
        $response = $client->post(env('EASEBUZZ_INITIATE_URL'), [
            'form_params' => [
                'key' => $key,
                'txnid' => $txnid,
                'amount' => $payableAmount,
                'productinfo' => $productinfo,
                'firstname' => $fullname,
                'phone' => $phone,
                'email' => $email,
                'surl' => route('admission.payment.success'),
                'furl' => route('admission.payment.failure'),
                'hash' => $hash,
                'udf1' => $userId,
                'split_payments' => $split
            ],
        ]);

        $apiResponse = json_decode($response->getBody(), true);

        if ($apiResponse['status'] == 1) {
            return redirect(env('EASEBUZZ_PAYMENT_URL') . $apiResponse['data']);
        }

        return back()->withErrors('Payment initiation failed');
    }

    function paymentSuccess(Request $request)
    {
        $hash  =  $request->hash;
        $amount = $request->amount;
        $msg = $request->error_Message;
        $easepayid = $request->easepayid;
        $status = $request->status;
        $txnid = $request->txnid;
        $userId = $request->udf1;
        //Online Transaction - Update Payment Record
        AdmissionApplication::where('application_code', $txnid)
            ->update(
                [
                    'payment_gateway_ref' => $easepayid,
                    'captured_amount' => $amount,
                    'payment_gateway_status' => $status,
                    'message' => $msg,
                    'hash' => $hash,
                ]
            );
        //Send Email to the Applicant
        $applicantPhone = AdmissionRegistration::where('user_id', $userId)->value('mobile_no');
        $applicantEmail = AdmissionRegistration::where('user_id', $userId)->value('mail_id');
        $applicationId = AdmissionApplication::where('application_code', $txnid)->value('application_code');
        $html = View::make('emails.admission.success', ['application_code' => $txnid])->render();
        // $applicant_email = trim((string) $email);
        $applicant_email = 'prof.johngaurav@gmail.com';
        $response = Http::withToken(env('RESEND_API_KEY'))
            ->post('https://api.resend.com/emails', [
                'from' => 'salesian college autonomous <onboarding@resend.dev>', // Use verified sender
                'to' =>  $applicant_email,
                'subject' => 'Salesian College Autonomous - Application Successful',
                'html' => $html,
            ]);

        //Send SMS to the Applicant

        return redirect()->route('admission.apply.application')->with('success', 'Payment successful. Your application is now complete.');
    }



    function paymentFailure(Request $request)
    {
        $rec = new AdmissionApplicationPaymentLog();
        $rec->txnid = $request->txnid;
        $rec->user_id = $request->udf1;
        $rec->easepayid = $request->easepayid;
        $rec->amount = $request->amount;
        $rec->hash = $request->hash;
        $rec->status = $request->status;
        $rec->msg = $request->error_Message;
        $rec->save();
        // Handle payment failure logic here
        return redirect()->route('admission.apply.application')->with('info', 'Payment failed. Please try again.');
    }


    function showSuccessPage()
    {
        $userId = Auth::user()->id;
        $applicationRecord = AdmissionApplication::with(
            [
                'registrationmaster.campusmaster',
                'academicDeptMaster',
                'stdCourseMaster',
                'phaseoneinfo',
                'phasetwoinfo'
            ]
        )->where('user_id', $userId)->first();

        return view('admission.success-confirmation', ['data' => $applicationRecord]);
    }

    function admissionSettings()
    {
        $data = AdmissionSetting::first();
        return view('admin.admission.settings', ['data' => $data]);
    }

    function updateAdmissionSettingsUg(Request $request)
    {


        AdmissionSetting::updateOrCreate(
            ['id' => 1],
            [
                'open_date_ug' => $request->open_date_ug,
                'close_date_ug' => $request->close_date_ug,
                'instructions_ug' => $request->instructions_ug,
                'application_fee_ug' => $request->application_fee_ug,

            ]
        );

        return back()->with('success', 'Admission settings updated successfully.');
    }

    function updateAdmissionSettingsPg(Request $request)
    {


        AdmissionSetting::updateOrCreate(
            ['id' => 1],
            [
                'open_date_pg' => $request->open_date_pg,
                'close_date_pg' => $request->close_date_pg,
                'instructions_pg' => $request->instructions_pg,
                'application_fee_pg' => $request->application_fee_pg,
            ]
        );

        return back()->with('success', 'Admission settings updated successfully.');
    }




    function logout()
    {
        Auth::logout();
        return redirect()->route('new.admission.login')->with('success', 'Logged out successfully.');
    }


    //Adding Dept Console Functions
    function deptApplicationList()
    {

        $userId = Auth::user()->id;
        if (StaticController::fetchUserRole() == 'dept-admin-erp') {
            $campusId =  UserCampusSetting::where('user_id', $userId)->value('campus_id');
            $departId =  SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
            if ($campusId == null) {
                return back()->with('error', 'No campus assigned to your account. Please contact ITCELL.');
            } else {
                $data =  AdmissionApplication::with([
                    'registrationmaster.countrymaster',
                ])->whereHas('registrationmaster.programinfo', function ($query) {
                    $query->where('name', 'UG');
                })->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                    $query->where('id', $campusId);
                })->where('department', $departId)
                    ->latest()->get();
            }
            return view('admin.subject.admission.application-list', ['data' => $data]);
        } else {
            return back()->with('info', 'Unauthorized access.');
        }
    }

    function deptInterviewList(Request $request)
    {
        $userId = Auth::user()->id;
        if (StaticController::fetchUserRole() == 'dept-admin-erp') {
            //fetch Dept 
            $departId =  SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
            $campusId =  UserCampusSetting::where('user_id', $userId)->value('campus_id');

            if ($campusId == null) {
                return back()->with('error', 'No campus assigned to your account. Please contact ITCELL.');
            } else {

                if (!empty($request->search)) {
                    $search = $request->search;
                    $data =   AdmissionFirstPhase::with([
                        'registrationmaster',
                        'applicationinfo',
                    ])->whereHas('applicationinfo', function ($query) use ($search, $departId) {
                        $query->where('application_id', 'like', '%' . $search . '%');
                        $query->where('department', $departId);
                    })
                        ->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                            $query->where('id', $campusId);
                        })->latest()->get();
                } else {
                    $data =  AdmissionFirstPhase::with([
                        'registrationmaster',
                        'applicationinfo',
                    ])->whereHas('applicationinfo', function ($query) use ($departId) {

                        $query->where('department', $departId);
                    })
                        ->whereHas('registrationmaster.programinfo.campus', function ($query) use ($campusId) {
                            $query->where('id', $campusId);
                        })->latest()->get();
                }
            }
            return view('admin.admission.ug.phase1', ['data' => $data]);
        } else {
            return back()->with('info', 'Unauthorized access.');
        }
    }
}
