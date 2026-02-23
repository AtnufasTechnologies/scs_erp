<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Mail\AdmissionRegistrationForgotMail;
use App\Mail\ApplicationSuccessMail;
use App\Mail\OtpMail;
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
use App\Models\ErrorLog;
use App\Models\MainProgram;
use App\Models\Otp;
use App\Models\PasswordReset;
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
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Mews\Captcha\Captcha;
use Illuminate\Support\Str;


class AdmissionController extends Controller
{
    function index()
    {

        $campus = Campus::all();
        $countries = Country::all();
        $admissionSetting = AdmissionSetting::find(1);
        //check if admission close date UG and PG is set and compare with current date to show admission closed page if current date is out of range

        if ($admissionSetting->open_date_ug != null && $admissionSetting->open_date_pg != null) {
            $currentDate = Carbon::now()->format('Y-m-d');
            if ($currentDate >= $admissionSetting->open_date_ug || $currentDate >= $admissionSetting->open_date_pg) {
                //Admission is open
                return view('admission.registration', [
                    'admissionSetting' => $admissionSetting,
                    'campuses' => $campus,
                    'countries' => $countries
                ]);
            } else {
                //Admission is closed
                return view('admission.closed');
            }
        } else {
            return view('admission.registration', [
                'campuses' => $campus,
                'countries' => $countries,
                'admissionSetting' => $admissionSetting,
            ]);
        }
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
            $var1 = $otp;
            $fields = array(
                "sender_id" => 'SCSCLG',
                "message" => '209775',
                "variables_values" => $var1,
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

        $applicant_email = trim((string) $email);
        $details = [
            'otp' => $otp,
        ];
        Mail::to($applicant_email)->send(new OtpMail($details));
    }

    function otpResend(Request $request)
    {
        $userId = Auth::user()->id;
        Otp::where('user_id', $userId)->where('status', 1)->update(['status' => 0]);

        $otp = StaticController::OtpGenerator($userId);
        $user = AdmissionRegistration::find($userId);
        //OTP ON NUMBER
        $phoneNo = $user->mobile_no;

        $var1 = $otp;

        $fields = array(
            "sender_id" => 'SCSCLG',
            "message" => '209775',
            "variables_values" => $var1,
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
            'password' => 'required',
        ]);
        $mobileNo = trim($request->registered_no);

        $user = AdmissionRegistration::where('mobile_no', $mobileNo)
            ->orWhere('mail_id', $mobileNo)
            ->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
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
        $userId = Auth::user()->id;
        $registrationInfo = AdmissionRegistration::with([
            'campusmaster',
            'countrymaster',
        ])->where('id', $userId)->first();

        $application = AdmissionApplication::where('registration_id', $registrationInfo->id)->first();

        // If application already submitted and paid, show success page
        if ($application && $application->payment_gateway_ref && $application->payment_gateway_status === 'success') {
            return $this->showSuccessPage();
        }

        // If application exists but not paid, redirect to payment
        if ($application) {
            return redirect()->route('admission.payment.checkout');
        }

        // Prepare common data
        $batch = $registrationInfo->batch;
        $campusId = $registrationInfo->campus_id;
        $commonData = [
            'data' => $registrationInfo,
            'bloodgroups' => BloodGroupMaster::all(),
            'religions' => ReligionMaster::all(),
            'batch' => $batch,
        ];

        // Return application form based on type
        if ($registrationInfo->application_type === 'UG') {
            $courses = ProgramGroup::whereHas('programInfo', function ($q) use ($campusId) {
                $q->where('campus_id', $campusId);
            })->where('campus_id', $campusId)->get();

            $academic_departments = Subject::where('campus_id', $campusId)
                ->where('main_program_type', 'UG')
                ->get();

            return view('admission.ug-application', array_merge($commonData, [
                'courses' => $courses,
                'academic_departments' => $academic_departments,
            ]));
        }

        if ($registrationInfo->application_type === 'UG and PG') {
            $courses = ProgramGroup::whereHas('programInfo', function ($q) use ($campusId) {
                $q->where('campus_id', $campusId);
            })->where('campus_id', $campusId)->get();

            $academic_departments = Subject::where('campus_id', $campusId)
                ->where('main_program_type', 'UG and PG')
                ->get();

            return view('admission.ug-application', array_merge($commonData, [
                'courses' => $courses,
                'academic_departments' => $academic_departments,
            ]));
        }

        // PG Application
        $academic_departments = SubjectHasStudentProgam::with('studentprograminfo')->where('campus_id', $campusId)
            ->where('program_type', 'PG')
            ->get();

        return view('admission.pg-application', array_merge($commonData, [
            'academic_departments' => $academic_departments,
        ]));
    }


    function getMainPrograms(Request $request)
    {
        $admissionSettings = AdmissionSetting::find(1);
        $ug = false;
        $pg = false;
        if ($admissionSettings->open_date_ug != null && $admissionSettings->close_date_ug != null) {

            $currentDate = Carbon::now()->format('Y-m-d');
            if ($currentDate >= $admissionSettings->open_date_ug && $currentDate <= $admissionSettings->close_date_ug) {
                //Admission is open
                $ug = true;
            }
        }

        if ($admissionSettings->open_date_pg != null && $admissionSettings->close_date_pg != null) {
            $currentDate = Carbon::now()->format('Y-m-d');
            if ($currentDate >= $admissionSettings->open_date_pg && $currentDate <= $admissionSettings->close_date_pg) {
                //Admission is open
                $pg = true;
            }
        }
        if ($pg && !$ug) {
            return MainProgram::where('campus_id', $request->campusId)->where('name', 'PG')->get();
        } else if (!$pg && $ug) {
            return MainProgram::where('campus_id', $request->campusId)->get();
        } else if (!$pg && !$ug) {
            return response()->json(['message' => 'Admission is closed for both UG and PG'], 403);
        } else {
            return MainProgram::where('campus_id', $request->campusId)->get();
        }
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
                'campusmaster',
            ])->where('application_type', $type)
                ->latest()
                ->get();
        } else {

            $registrations = AdmissionRegistration::with([
                'countrymaster',
                'programinfo',
                'applicationmaster',
            ])->where('campus_id', $campusId)->where('application_type', $type)
                ->latest()
                ->get();
        }

        return view('admin.admission.registration', ['registrations' => $registrations]);
    }

    function ugApplications()
    {


        //fetch user's campus
        $campusId =  StaticController::fetchCampusSettings();
        if ($campusId == null) {
            $data = AdmissionApplication::whereHas('registrationmaster', function ($query) {
                $query->where('application_type', 'UG');
            })->with([
                'registrationmaster.countrymaster',
                'stdCourseMaster',
                'academicdepartmentinfo',
            ])->get();
        } else {

            $data = AdmissionApplication::whereHas('registrationmaster', function ($query) use ($campusId) {
                $query->where('application_type', 'UG');
                $query->where('campus_id', $campusId);
            })->with([
                'registrationmaster.countrymaster',
                'stdCourseMaster',
                'academicdepartmentinfo',
            ])->get();
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
                    $query->where('application_code', 'like', '%' . $search . '%');
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
                    $query->where('application_code', 'like', '%' . $search . '%');
                })
                    ->whereHas('registrationmaster', function ($query) use ($campusId) {
                        $query->where('campus_id', $campusId);
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
        $generatedNo = $userId  . rand(1000, 9999);
        $application = new AdmissionApplication();

        $application->user_id = $userId;
        $application->application_code = $generatedNo;
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
        $application->father_qualification = $request->father_qualification;
        $application->mother_qualification = $request->mother_qualification;
        $application->guardian_name = $request->guardian_name;
        $application->guardian_contact = $request->guardian_contact;
        $application->income = $request->income;
        $application->permanent_address = $request->permanent_address;
        $application->district = $request->district;
        $application->city = $request->city;
        $application->pincode = $request->pincode;
        $application->state = $request->state;
        $application->local_address = $request->local_address;
        $application->local_district = $request->local_district;
        $application->local_city = $request->local_city;
        $application->local_pincode = $request->local_pincode;
        $application->local_state = $request->local_state;
        $application->has_laptop = $request->has_laptop;
        $application->from_teaestate = $request->from_teaestate;
        $application->adhaar = $request->adhaar;
        // Handle file uploads
        if (!empty($request->national_id_proof)) {
            $national_id_proof = $request->national_id_proof;
            $national_id_proofFilename = StaticController::s3_file_uploader($national_id_proof, 'admission_national_id_proofs');
        } else {
            $national_id_proofFilename = null;
        }

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

        $application->national_id_proof = $national_id_proofFilename; //other national applicant  id proof
        // Academic details
        $application->institution10 = $request->institution10;
        $application->rollno10 = $request->rollno10;
        $application->board10 = $request->board10;
        $application->passingyear10 = $request->passingyear10;

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


        $application->subject12_1 = $request->subject12_1;
        $application->score12_1 = $request->score12_1;

        $application->subject12_2 = $request->subject12_2;
        $application->score12_2 = $request->score12_2;

        $application->subject12_3 = $request->subject12_3;
        $application->score12_3 = $request->score12_3;

        $application->subject12_4 = $request->subject12_4;
        $application->score12_4 = $request->score12_4;



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
                AdmissionApplication::where('id', $applicationRecord->id)->update([
                    'application_code' => $generatedNo,
                ]);
                $data = AdmissionRegistration::with([
                    'campusmaster',
                    'applicationmaster.academicDeptMaster',
                    'applicationmaster.stdCourseMaster'
                ])->where('id', $userId)->first();

                if ($data->application_type == 'UG') {
                    $amount =  AdmissionSetting::where('id', 1)
                        ->value('application_fee_ug');
                } else {
                    $amount =  AdmissionSetting::where('id', 1)
                        ->value('application_fee_pg');
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
        $streamMain = Subject::find($deptId);
        $progam_type = Str::upper($streamMain->main_program_type);
        $batchId =  BatchMaster::where('admission_active_batch', 1)->value('id');
        return SubjectHasStudentProgam::where('subject_id', $deptId)->where('campus_id', $campusId)
            ->where('batch_id', $batchId)
            ->with('studentprograminfo')
            ->where('program_type', $progam_type)
            ->get();
    }

    function initateEaseBuzzPayment(Request $request)
    {
        $userId = Auth::user()->id;
        $applicationRegRecord = AdmissionRegistration::where('id', $userId)->first();
        $applicationRecord = AdmissionApplication::where('registration_id', $applicationRegRecord->id)->first();
        $applicationId = $applicationRecord->id;
        $invoice = $applicationRecord->application_code;
        //program split logic for easebuzz
        if ($applicationRegRecord->application_type == 'PG') {
            $payableAmount =  AdmissionSetting::where('id', 1)
                ->value('application_fee_pg');
        } else {
            $payableAmount =  AdmissionSetting::where('id', 1)
                ->value('application_fee_ug');
        }

        //banking Split
        if ($applicationRegRecord->campus_id == 1) {
            $split = json_encode([
                'SAL_SONADA' => $payableAmount
            ]);
        } else {
            $split = json_encode([
                'SAL_SILIGURI' => $payableAmount
            ]);
        }

        /* if ($applicationRegRecord->campus_id == 1) {
            $split = json_encode([
                'SC_1' => $payableAmount
            ]);
        } else {
            $split = json_encode([
                'SC_4' => $payableAmount
            ]);
        }*/


        // ---- EASEBUZZ PARAMS ----
        $key = env('EASEBUZZ_KEY');
        $salt = env('EASEBUZZ_SALT');
        $txnid = $invoice;
        $phone = $applicationRegRecord->mobile_no;
        $email = $applicationRegRecord->mail_id;
        $firstname = $applicationRegRecord->first_name;
        $productinfo = 'Salesian College Autonomous - Admission Application Fee';
        //key|txnid|amount|productinfo|firstname|email|||||||||||salt
        // Ensure exactly 16 pipes between udf1 and salt (udf1 to udf10)

        // ---- INITIATE PAYMENT ----
        $hashString = "$key|$txnid|$payableAmount|$productinfo|$firstname|$email|$userId|$applicationId|||||||||$salt";

        $hash = strtolower(hash('sha512', $hashString));

        // ---- INITIATE PAYMENT ----
        $client = new \GuzzleHttp\Client();
        $response = $client->post(env('EASEBUZZ_INITIATE_URL'), [
            'form_params' => [
                'key' => $key,
                'txnid' => $invoice,
                'amount' => $payableAmount,
                'productinfo' => $productinfo,
                'firstname' => $firstname,
                'phone' => $phone,
                'email' => $email,
                'surl' => route('admission.payment.success'),
                'furl' => route('admission.payment.failure'),
                'hash' => $hash,
                'udf1' => $userId,
                'udf2' => $applicationId,
                'split_payments' => $split

            ],
        ]);

        $apiResponse = json_decode($response->getBody(), true);


        if ($apiResponse['status'] == 1) {
            return redirect(env('EASEBUZZ_PAYMENT_URL') . $apiResponse['data']);
        }
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
                    'msg' => $msg,
                    'hash' => $hash,
                ]
            );


        //Send Email to the Applicant
        $firstname = AdmissionRegistration::where('id', $userId)->value('first_name');
        $applicantPhone = AdmissionRegistration::where('id', $userId)->value('mobile_no');
        $applicantEmail = AdmissionRegistration::where('id', $userId)->value('mail_id');
        $applicationId = AdmissionApplication::where('application_code', $txnid)->value('application_code');
        $html = View::make('emails.admission.success', ['application_code' => $txnid])->render();
        $applicant_email = trim((string) $applicantEmail);
        Mail::to($applicant_email)->send(new ApplicationSuccessMail($applicationId));

        //Send SMS to the Applicant
        $var1 = $applicationId; //dynamic variable for applicant name and application id
        $var2 = 9933402478;
        $var3 = 'admissionenquiry@salesiancollege.net';
        $fields = array(
            "sender_id" => 'SCSCLG',
            "message" => '209774',
            "variables_values" => $var1 . '|' . $var2 . '|' . $var3,
            "route" => "dlt",
            "numbers" => $applicantPhone,
        );

        StaticController::bulkSmsSender($fields);
        //log User 
        $userData = AdmissionRegistration::where('id', $userId)->first();
        Auth::login($userData, true);

        return redirect()->route('admission.apply.application')->with('success', 'Payment successful. Your application is now complete.');
    }



    function paymentFailure(Request $request)
    {
        $rec = new AdmissionApplicationPaymentLog();
        $rec->application_id = $request->udf2;
        $rec->txnid = $request->txnid;
        $rec->user_id = $request->udf1;
        $rec->easepayid = $request->easepayid;
        $rec->amount = $request->amount;
        $rec->hash = $request->hash;
        $rec->status = $request->status;
        $rec->msg = $request->error_Message;
        $rec->save();
        // Handle payment failure logic here
        $user = AdmissionRegistration::find($request->udf1);
        Auth::login($user, true);

        return redirect()->route('admission.apply.application')->with('info', 'Payment failed. Please try again.');
    }

    /**
     * Webhook: Easebuzz server->server notifications
     */
    public function webhook(Request $request)
    {
        // Validate signature if Easebuzz sends one (check docs)
        // Example: $signature = $request->header('X-Easebuzz-Signature'); verify it
        $payload = $request->all();
        $application_id = $payload['udf2'] ?? null;

        if (!$application_id) {
            return response()->json(['status' => 'error', 'message' => 'application_id missing'], 400);
        }

        $payment = AdmissionApplication::where('id', $application_id)->first();

        if (!$payment) {
            // maybe log and create a record
            ErrorLog::create([
                'details' => json_encode($payload)
            ]);
            return response()->json(['status' => 'ok']);
        }

        // Update according to webhook payload status
        $status = $payload['status'] ?? 'pending';
        $payment->update([
            'payment_gateway_status' => strtoupper($status),
        ]);

        // perform reconciliation, ledger updates etc.

        return response()->json(['status' => 'ok']);
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

        AdmissionSetting::where('id', 1)->update(
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


        AdmissionSetting::where('id', 1)->update(
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

                $data = AdmissionApplication::whereHas('registrationmaster', function ($query) use ($campusId) {
                    $query->where('application_type', 'UG');
                    $query->where('campus_id', $campusId);
                })->with([
                    'registrationmaster.countrymaster',
                    'stdCourseMaster',
                    'academicdepartmentinfo',
                ])
                    ->where('department', $departId)
                    ->where('payment_gateway_status', 'success') //paid approved applications
                    ->latest()
                    ->get();
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


    //Forgot Password
    function showForgotPasswordForm()
    {
        return view('admission.forgot-password');
    }

    function handleForgotPassword(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email',
        ]);

        $applicantmail = AdmissionRegistration::where('mail_id', $request->email)->value('mail_id');

        if ($applicantmail) {

            $code = sha1(uniqid());
            $rec = new PasswordReset();
            $rec->email = $applicantmail;
            $rec->token = $code;
            $rec->status = 1;
            $rec->save();

            $details = [
                'token' =>  $code,
            ];

            Mail::to($applicantmail)->send(new AdmissionRegistrationForgotMail($details));

            return back()->with('success', 'Password reset link has been sent to your email.');
        } else {
            return back()->with('error', 'Email not found. Please check and try again.');
        }
    }


    function showResetPasswordPage($token)
    {
        $data =  PasswordReset::where('token', $token)->where('status', 1)->first();
        if ($data) {
            return view('admission.update-password', ['data' => $data]);
        } else {
            return redirect()->route('admission.forgot.password')->with('error', 'Link Expired... Please Reset Again');
        }
    }

    function handleResetPassword(Request $request)
    {
        // Validate the reset password input
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6',
        ]);

        // Reset password logic here

        $data = PasswordReset::where('token', $request->token)->where('status', 1)->first();
        if ($data) {
            $applicant = AdmissionRegistration::where('mail_id', $data->email)->first();
            if ($applicant) {
                $applicant->password = Hash::make($request->password);
                $applicant->save();

                // Invalidate the token after successful password reset
                $data->status = 0;
                $data->save();
                return redirect()->route('new.admission.login')->with('success', 'Password has been reset successfully. You can now log in with your new password.');
            } else {
                return back()->with('error', 'Applicant not found. Please check and try again.');
            }
        } else {
            return redirect()->route('admission.forgot.password')->with('error', 'Invalid or expired token. Please try resetting your password again.');
        }
    }

    function downloadPaymentInvoice($application_code)
    {
        $applicationRecord = AdmissionApplication::with([
            'registrationmaster.campusmaster',
            'stdCourseMaster',
            'academicDeptMaster',
        ])->where('application_code', $application_code)->first();


        if ($applicationRecord && $applicationRecord->payment_gateway_status == 'success') {
            return view('pdf.admission.invoice', ['data' => $applicationRecord]);
            // $pdf = FacadePdf::loadView('pdf.admission.invoice', ['data' => $applicationRecord]);
            // return $pdf->download('invoice_' . $applicationRecord->application_code . '.pdf');
        } else {
            return back()->with('error', 'Invoice not available for this application.');
        }
    }

    function downloadApplicationForm($application_code)
    {
        $applicationRecord = AdmissionApplication::with([
            'registrationmaster.countrymaster',
            'registrationmaster.campusmaster',
            'academicDeptMaster',
            'stdCourseMaster',
            'religionmaster',
            'bloodgroupmaster'
        ])->where('application_code', $application_code)->first();
        if ($applicationRecord) {

            if ($applicationRecord->registrationmaster->application_type == 'UG') {
                return view('pdf.admission.application-ug', ['data' => $applicationRecord]);
            } else {
                return view('pdf.admission.application-pg', ['data' => $applicationRecord]);
            }

            // $pdf = FacadePdf::loadView('pdf.admission.application', ['data' => $applicationRecord]);
            // return $pdf->download('application_form_' . $applicationRecord->application_code . '.pdf');
        } else {
            return back()->with('error', 'Application form not available for this application.');
        }
    }


    function pgApplicationSubmit(Request $request)
    {

        $request->validate([
            'photo' => 'required',
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
            'collegename' => 'required|string|max:255',
            'universityname' => 'required|string|max:255',
            'graduatingyear' => 'required',
            'graduatingrollno' => 'required|string|max:255',
            'college_marksheet' => 'required',

        ]);



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

        //Department  auto filled based on the registration data for PG applicants,
        $subjectProgramData = SubjectHasStudentProgam::find($request->course);
        $deptId = $subjectProgramData->subject_id;
        $courseId = $subjectProgramData->student_program_id;

        $application = new AdmissionApplication();
        $application->user_id = $userId;
        $application->registration_id = $registrationId;
        $application->department = $deptId;
        $application->course = $courseId;
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
        $application->father_qualification = $request->father_qualification;
        $application->mother_qualification = $request->mother_qualification;
        $application->guardian_name = $request->guardian_name;
        $application->guardian_contact = $request->guardian_contact;
        $application->income = $request->income;
        $application->permanent_address = $request->permanent_address;
        $application->district = $request->district;
        $application->city = $request->city;
        $application->pincode = $request->pincode;
        $application->state = $request->state;
        $application->local_address = $request->local_address;
        $application->local_district = $request->local_district;
        $application->local_city = $request->local_city;
        $application->local_pincode = $request->local_pincode;
        $application->local_state = $request->local_state;
        $application->has_laptop = $request->has_laptop;
        $application->from_teaestate = $request->from_teaestate;
        $application->adhaar = $request->adhaar;
        // Handle file uploads
        if (!empty($request->national_id_proof)) {
            $national_id_proof = $request->national_id_proof;
            $national_id_proofFilename = StaticController::s3_file_uploader($national_id_proof, 'admission_national_id_proofs');
        } else {
            $national_id_proofFilename = null;
        }

        $photo =  $request->photo;
        $photoFilename = StaticController::s3_resize_image_uploader($photo, 'admission_photos', 300, 300);

        $college_marksheet =  $request->college_marksheet;
        $college_marksheetFilename = StaticController::s3_file_uploader($college_marksheet, 'admission_college_marksheets');

        $application->photo = $photoFilename;


        if (isset($baptismFilename)) {
            $application->baptism = $baptismFilename;
        } else {
            $application->baptism = null;
        }

        $application->national_id_proof = $national_id_proofFilename; //other national applicant  id proof
        // Academic details
        $application->college_name = $request->collegename;
        $application->university_name = $request->universityname;
        $application->graduating_year = $request->graduatingyear;
        $application->graduating_rollno = $request->graduatingrollno;
        $application->college_marksheet = $college_marksheetFilename;

        $application->sgpa1 = $request->sgpa1;
        $application->sgpa2 = $request->sgpa2;
        $application->sgpa3 = $request->sgpa3;
        $application->sgpa4 = $request->sgpa4;
        $application->sgpa5 = $request->sgpa5;
        $application->sgpa6 = $request->sgpa6;

        // Payment fields (to be filled after payment)
        $application->gateway_type = 'easebuzz';
        $application->payment_gateway_ref = null;
        $application->captured_amount = null;
        $application->hash = null;
        $application->payment_gateway_status = null;

        $application->save();

        return redirect()->route('admission.payment.checkout')->with('success', 'Application Saved successfully. Please proceed to payment.');
    }

    //PG application submit function starts here
    function pgApplications()
    {

        //fetch user's campus
        $campusId =  StaticController::fetchCampusSettings();
        if ($campusId == null) {
            $data = AdmissionApplication::whereHas('registrationmaster', function ($query) {
                $query->where('application_type', 'PG');
            })->with([
                'registrationmaster.countrymaster',
                'stdCourseMaster',
                'academicdepartmentinfo',
            ])->get();
        } else {

            $data = AdmissionApplication::whereHas('registrationmaster', function ($query) use ($campusId) {
                $query->where('application_type', 'PG');
                $query->where('campus_id', $campusId);
            })->with([
                'registrationmaster.countrymaster',
                'stdCourseMaster',
                'academicdepartmentinfo',
            ])->get();
        }

        return view('admin.admission.pg.applications', ['data' => $data]);
    }


    //Edit UG Application
    function showEditApplication($id)
    {
        $data = AdmissionApplication::with([
            'registrationmaster.countrymaster',
            'registrationmaster.campusmaster',
            'academicDeptMaster',
            'stdCourseMaster',
            'religionmaster',
            'bloodgroupmaster'
        ])->where('id', $id)->first();

        return view('admin.admission.ug.edit_application', ['application' => $data]);
    }

    function updateUgApplication(Request $request, $id)
    {
        return redirect()->back()->with('info', 'Application editing is currently disabled.Work in Progress...');
        $request->validate([
            'photo' => 'nullable|image',
            'department' => 'required',
            'course' => 'required',
            'dob' => 'required|date',
            'bloodgroup' => 'required',
            'gender' => 'required',
            'religion' => 'required',
            'mothertongue' => 'required',
            'phychallenged' => 'required',
            'caste' => 'required',
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
        ]);

        if ($request->religion == 10) {
            $request->validate(['baptism' => 'nullable|file']);
            if (!empty($request->baptism)) {
                $baptismFilename = StaticController::s3_file_uploader($request->baptism, 'admission_baptisms');
            }
        }

        $application = AdmissionApplication::findOrFail($id);

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
        $application->father_qualification = $request->father_qualification ?? null;
        $application->mother_qualification = $request->mother_qualification ?? null;
        $application->guardian_name = $request->guardian_name ?? null;
        $application->guardian_contact = $request->guardian_contact ?? null;
        $application->income = $request->income;
        $application->permanent_address = $request->permanent_address;
        $application->district = $request->district;
        $application->city = $request->city;
        $application->pincode = $request->pincode;
        $application->state = $request->state ?? null;
        $application->local_address = $request->local_address;
        $application->local_district = $request->local_district;
        $application->local_city = $request->local_city;
        $application->local_pincode = $request->local_pincode;
        $application->local_state = $request->local_state ?? null;
        $application->has_laptop = $request->has_laptop ?? null;
        $application->from_teaestate = $request->from_teaestate ?? null;
        $application->adhaar = $request->adhaar ?? null;

        if ($request->hasFile('photo')) {
            $photoFilename = StaticController::s3_resize_image_uploader($request->photo, 'admission_photos', 300, 300);
            $application->photo = $photoFilename;
        }

        if ($request->hasFile('national_id_proof')) {
            $national_id_proofFilename = StaticController::s3_file_uploader($request->national_id_proof, 'admission_national_id_proofs');
            $application->national_id_proof = $national_id_proofFilename;
        }

        if (isset($baptismFilename)) {
            $application->baptism = $baptismFilename;
        }

        $application->save();

        return back()->with('success', 'Application updated successfully.');
    }
}
