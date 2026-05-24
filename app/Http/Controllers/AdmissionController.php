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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;
use App\Models\ApplicantProgramChangeInfo;
use App\Models\StudentMaster;
use Illuminate\Bus\Batch;
use App\Services\BillDeskService;

class AdmissionController extends Controller
{
    function dashboard()
    {
        $campusId = StaticController::fetchCampusSettings();

        $regQuery = AdmissionRegistration::query();
        if ($campusId) {
            $regQuery->where('campus_id', $campusId);
        }

        // Registration counts
        $totalUgRegistrations  = (clone $regQuery)->where('application_type', 'UG')->count();
        $totalPgRegistrations  = (clone $regQuery)->where('application_type', 'PG')->count();
        $todayUgRegistrations  = (clone $regQuery)->where('application_type', 'UG')->whereDate('created_at', today())->count();
        $todayPgRegistrations  = (clone $regQuery)->where('application_type', 'PG')->whereDate('created_at', today())->count();

        // Applications submitted
        $appQuery = AdmissionApplication::whereHas('registrationmaster', function ($q) use ($campusId) {
            if ($campusId) {
                $q->where('campus_id', $campusId);
            }
        });
        $totalUgApplications = (clone $appQuery)->whereHas('registrationmaster', fn($q) => $q->where('application_type', 'UG'))->count();
        $totalPgApplications = (clone $appQuery)->whereHas('registrationmaster', fn($q) => $q->where('application_type', 'PG'))->count();

        // Phase 1 & Phase 2 (final) selections
        $phase1Count  = AdmissionFirstPhase::count();
        $phase2Count  = AdmissionFinalPhase::count();
        $enrolledCount = AdmissionFinalPhase::where('enroll_status', 1)->count();

        // Application fee collected
        $totalAppFeeCollected = AdmissionApplicationPaymentLog::where('status', 'success')->sum('amount');
        $todayAppFeeCollected = AdmissionApplicationPaymentLog::where('status', 'success')
            ->whereDate('created_at', today())
            ->sum('amount');

        // Registration trend — last 14 days
        $regTrend = AdmissionRegistration::where('created_at', '>=', now()->subDays(13))
            ->selectRaw('DATE(created_at) as date, application_type, COUNT(*) as count')
            ->groupBy('date', 'application_type')
            ->orderBy('date')
            ->get();

        // Recent 10 registrations
        $recentRegistrations = (clone $regQuery)
            ->with('campusmaster')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.admission.dashboard', compact(
            'totalUgRegistrations',
            'totalPgRegistrations',
            'todayUgRegistrations',
            'todayPgRegistrations',
            'totalUgApplications',
            'totalPgApplications',
            'phase1Count',
            'phase2Count',
            'enrolledCount',
            'totalAppFeeCollected',
            'todayAppFeeCollected',
            'regTrend',
            'recentRegistrations'
        ));
    }

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
        $rec->mobile_no = trim($request->mobile_no);
        $rec->mail_id = Str::lower($request->mail_id);
        $rec->campus_id = $request->campus;
        $rec->application_type = $request->applicationType;
        $rec->country = $request->country;
        $rec->password = Hash::make($request->password);
        $rec->account_status = 1;
        $rec->save();

        $user = AdmissionRegistration::find($rec->id);
        if (!$user) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'Registration failed. Please try again.']);
        }
        Auth::login($user, true);
        return redirect()->route('otp.verification.page')->with('success', 'Registration successful. Please verify OTP sent to your registered email and mobile number.');
    }


    function showOtpVerificationPage(Request $request)
    {

        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }
        if (!$user) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'Session expired. Please login again.']);
        }
        Auth::login($user, true);
        $userId = $user->id;
        $isOtpVerified = Otp::where('user_id', $userId)->where('status', 1)->first();
        if (!$isOtpVerified) {
            //OTP on EMAIL

            $otp = StaticController::OtpGenerator($userId);
            $user = AdmissionRegistration::find($userId);
            //OTP ON NUMBER
            $phoneNo = $user->mobile_no;
            $var1 = $otp;
            $var2 = 2;
            $fields = array(
                "sender_id" => 'SCSCLG',
                "message" => '209861',
                "variables_values" => $var1 . '|' . $var2,
                "route" => "dlt",
                "numbers" => $phoneNo,
            );

            //Send Otp on Whatsapp

            /**Pending Approval from Client */

            //Send Otp on Phone
            StaticController::smsSender($fields);
            //Send Otp on Email
            $usermail = $user->mail_id;
            $this->sendOTPEmail($otp, $usermail);
        }
        return view('admission.otp-verification', [
            'userId' => $userId,
        ]);
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

        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;
        $userId = $user ? $user->id : null;
        if (!$userId) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
        }
        Otp::where('user_id', $userId)->where('status', 1)->update(['status' => 0]);

        $otp = StaticController::OtpGenerator($userId);
        $user = AdmissionRegistration::find($userId);
        //OTP ON NUMBER
        $phoneNo = $user->mobile_no;

        $var1 = $otp;
        $var2 = 2;
        $fields = array(
            "sender_id" => 'SCSCLG',
            "message" => '209861',
            "variables_values" => $var1 . '|' . $var2,
            "route" => "dlt",
            "numbers" => $phoneNo,
        );
        StaticController::smsSender($fields);
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
                $name = $user->first_name . ' ' . $user->last_name;
                if ($user->otp_verification == 0) {
                    return redirect()->route('otp.verification.page', ['id' => $user->id])->with('info', 'Please verify OTP sent to your registered email and mobile number.');
                } else {
                    return redirect()->route('admission.apply.application');
                }
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

        $record = Otp::where('otp', $request->otp)
            ->where('status', 1)
            ->first();
        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        $id = $record->user_id;
        $user =  AdmissionRegistration::find($id);
        if (!$user) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
        }
        Auth::login($user, true);
        $userId = $user->id;
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

            $name = Str::slug(trim($user->first_name . ' ' . $user->last_name));
            return redirect()->route('admission.apply.application');
        } else {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }
    }


    function showApplicationPage(Request $request)
    {
        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;

        if (!$user && Auth::check()) {
            $user = Auth::user();
        }
        if (!$user) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'Session expired. Please login again.']);
        }

        Auth::login($user, true);
        $userId = $user->id;
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
            ])->latest()->get();
        } else {

            $data = AdmissionApplication::whereHas('registrationmaster', function ($query) use ($campusId) {
                $query->where('application_type', 'UG');
                $query->where('campus_id', $campusId);
            })->with([
                'registrationmaster.countrymaster',
                'stdCourseMaster',
                'academicdepartmentinfo',
            ])->latest()->get();
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
                    'programChangeInfo.oldProgram',
                    'programChangeInfo.newProgram',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_code', 'like', '%' . $search . '%');
                })->latest()->get();
            } else {
                $data =   AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                    'programChangeInfo.oldProgram',
                    'programChangeInfo.newProgram',
                ])->latest()->get();
            }
        } else {
            if (!empty($request->search)) {
                $search = $request->search;
                $data =   AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                    'programChangeInfo.oldProgram',
                    'programChangeInfo.newProgram',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_code', 'like', '%' . $search . '%');
                })
                    ->whereHas('registrationmaster', function ($query) use ($campusId) {
                        $query->where('campus_id', $campusId);
                    })->latest()->get();
            } else {

                $data = AdmissionFirstPhase::with([
                    'registrationmaster',
                    'applicationinfo',
                    'programChangeInfo.oldProgram',
                    'programChangeInfo.newProgram',
                ])->whereHas('registrationmaster', function ($query) use ($campusId) {
                    $query->where('campus_id', $campusId);
                })->latest()->get();
            }
        }

        // Separate transferred applicants
        $transferredApplicants = $data->filter(function ($item) {
            return $item->programChangeInfo !== null;
        });

        // Get transferred applicants with pending department interviews
        $transferredPendingInterview = $transferredApplicants->filter(function ($item) {
            return $item->dept_interview == 0;
        });

        return view('admin.admission.ug.phase1', [
            'data' => $data,
            'transferredApplicants' => $transferredApplicants,
            'transferredPendingInterview' => $transferredPendingInterview
        ]);
    }

    public function exportPhase1AllApplicants()
    {
        $campusId = StaticController::fetchCampusSettings();

        if ($campusId == null) {
            $data = AdmissionFirstPhase::with([
                'registrationmaster',
                'applicationinfo.stdprogramMaster',
            ])->latest()->get();
        } else {
            $data = AdmissionFirstPhase::with([
                'registrationmaster',
                'applicationinfo.stdprogramMaster',
            ])->whereHas('registrationmaster', function ($query) use ($campusId) {
                $query->where('campus_id', $campusId);
            })->latest()->get();
        }

        $export = new GenericExport($data, 'admin.admission.ug.phase1-export');
        return Excel::download($export, 'phase1-all-applicants-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPhase1SelectedApplicants()
    {
        $campusId = StaticController::fetchCampusSettings();

        if ($campusId == null) {
            $data = AdmissionFirstPhase::with([
                'registrationmaster',
                'applicationinfo.stdprogramMaster',
            ])->where('final_status', 1)->latest()->get();
        } else {
            $data = AdmissionFirstPhase::with([
                'registrationmaster',
                'applicationinfo.stdprogramMaster',
            ])->where('final_status', 1)
                ->whereHas('registrationmaster', function ($query) use ($campusId) {
                    $query->where('campus_id', $campusId);
                })->latest()->get();
        }

        $export = new GenericExport($data, 'admin.admission.ug.phase1-export');
        return Excel::download($export, 'phase1-selected-applicants-' . date('Y-m-d') . '.xlsx');
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
        //bypass logic
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

        /*

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
            */
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
            ->whereIn('course', $programs)
            ->where('payment_gateway_status', 'success') // paid applications only
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'No applicants found for the selected programs.');
        }

        //bypass logic
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
        return back()->with('success', 'Phase 1 Interview List created for selected applicants.');
        /*

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
            */
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
        $comboInfo =  SubjectHasStudentProgam::where('student_program_id', $new_program)->firstOrFail();

        $phase1Record = AdmissionFirstPhase::findOrFail($id);
        $application = AdmissionApplication::findOrFail($phase1Record->application_id);
        $old_program_id = $application->course;;

        // Update the programme_id in the application
        $application->department = $comboInfo->subject_id;
        $application->course = $new_program;
        $application->save();

        //Add Program Change 
        ApplicantProgramChangeInfo::create([
            'registration_id' => $request->registration_id,
            'application_id' => $request->application_id,
            'old_program_id' => $old_program_id,
            'new_program_id' => $new_program,
            'changed_by' => Auth::user()->id,
            'reason' => $request->reason ?? 'Not specified',
        ]);

        return back()->with('success', 'Applicant Program Shifted Successfully.');
    }

    function transferUgProgram(Request $request)
    {

        $request->validate([
            'new_program' => 'required',
            'application_id' => 'required',
            'registration_id' => 'required',
        ]);
        //Find Student Program Groupd
        $new_program = $request->new_program;
        $comboInfo =  SubjectHasStudentProgam::where('student_program_id', $new_program)->firstOrFail();
        $id = $request->id;
        $phase1Record = AdmissionFirstPhase::find($id);
        $application = AdmissionApplication::find($request->application_id);
        $old_program_id = $application->course;;

        // Update the programme_id in the application
        $application->department = $comboInfo->subject_id;
        $application->course = $new_program;
        $application->save();

        //Add Program Change 
        ApplicantProgramChangeInfo::create([
            'registration_id' => $request->registration_id,
            'application_id' => $request->application_id,
            'old_program_id' => $old_program_id,
            'new_program_id' => $new_program,
            'changed_by' => Auth::user()->id,
            'reason' => $request->reason ?? 'Not specified',
        ]);

        return back()->with('success', 'Applicant Program Shifted Successfully.');
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
                    'applicationinfo.stdCourseMaster',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_id', 'like', '%' . $search . '%');
                })->latest()->get();
            } else {
                $data =   AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo.stdCourseMaster',
                ])->latest()->get();
            }
        } else {


            if (!empty($request->search)) {
                $search = $request->search;
                $data =   AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo.stdCourseMaster',
                ])->whereHas('applicationinfo', function ($query) use ($search) {
                    $query->where('application_id', 'like', '%' . $search . '%');
                })
                    ->whereHas('registrationmaster', function ($query) use ($campusId) {
                        $query->where('campus_id', $campusId);
                    })->latest()->get();
            } else {
                $data =  AdmissionFinalPhase::with([
                    'registrationmaster',
                    'applicationinfo.stdCourseMaster',
                ])->whereHas('registrationmaster', function ($query) use ($campusId) {
                    $query->where('campus_id', $campusId);
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
            'institution10' => 'required|string|max:255',
            'rollno10' => 'required|string|max:255',
            'board10' => 'required|string|max:255',
            'passingyear10' => 'required|integer|min:2000|max:' . date('Y'),
            'certificate10' => 'required|file|max:5120',
            //Class 10th Subjects
            'subject10_1' => 'required|string|max:255',
            'score10_1' => 'required|integer|min:0|max:100',
            'subject10_2' => 'required|string|max:255',
            'score10_2' => 'required|integer|min:0|max:100',
            'subject10_3' => 'required|string|max:255',
            'score10_3' => 'required|integer|min:0|max:100',
            'subject10_4' => 'required|string|max:255',
            'score10_4' => 'required|integer|min:0|max:100',
            'subject10_5' => 'required|string|max:255',
            'score10_5' => 'required|integer|min:0|max:100',

            //Class 12 Details
            'institution12' => 'required|string|max:255',
            'rollno12' => 'required|string|max:255',
            'board12' => 'required|string|max:255',
            'passingyear12' => 'required|integer|min:2000|max:' . date('Y'),
            'certificate12' => 'required|file|max:5120',
            //Class 12th Subjects
            'subject12_1' => 'required|string|max:255',
            'score12_1' => 'required|integer|min:0|max:100',
            'subject12_2' => 'required|string|max:255',
            'score12_2' => 'required|integer|min:0|max:100',
            'subject12_3' => 'required|string|max:255',
            'score12_3' => 'required|integer|min:0|max:100',
            'subject12_4' => 'required|string|max:255',
            'score12_4' => 'required|integer|min:0|max:100',

            //Baptism Certificate
        ]);



        // Robust file upload handling
        try {
            if ($request->religion == 10) {
                $request->validate([
                    'baptism' => 'required',
                ]);
                $baptism =  $request->baptism;
                if ($baptism && $baptism->isValid()) {
                    $baptismFilename = StaticController::s3_file_uploader($baptism, 'admission_baptisms');
                } else {
                    return back()->withErrors(['baptism' => 'Invalid or missing baptism certificate file.'])->withInput();
                }
            }

            // Save application
            $id = $request->id ?? Auth::id();
            $user = $id ? AdmissionRegistration::find($id) : null;
            $userId = $user ? $user->id : null;
            if (!$userId) {
                return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
            }
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

            // Handle file uploads with checks
            $photoFilename = null;
            $certificate10Filename = null;
            $certificate12Filename = null;
            $adhaarFilename = null;
            $national_id_proofFilename = null;

            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photo = $request->file('photo');
                $photoFilename = StaticController::s3_resize_image_uploader($photo, 'admission_photos', 300, 300);
            } else {
                return back()->withErrors(['photo' => 'Invalid or missing photo file.'])->withInput();
            }

            if ($request->hasFile('certificate10') && $request->file('certificate10')->isValid()) {
                $certificate10 = $request->file('certificate10');
                $certificate10Filename = StaticController::s3_file_uploader($certificate10, 'admission_cerificates10');
            } else {
                return back()->withErrors(['certificate10' => 'Invalid or missing Class 10 certificate file.'])->withInput();
            }

            if ($request->hasFile('certificate12') && $request->file('certificate12')->isValid()) {
                $certificate12 = $request->file('certificate12');
                $certificate12Filename = StaticController::s3_file_uploader($certificate12, 'admission_cerificates12');
            } else {
                return back()->withErrors(['certificate12' => 'Invalid or missing Class 12 certificate file.'])->withInput();
            }

            if ($request->hasFile('adhaar_doc') && $request->file('adhaar_doc')->isValid()) {
                $adhaarDoc = $request->file('adhaar_doc');
                $adhaarFilename = StaticController::s3_file_uploader($adhaarDoc, 'admission_adhaar_docs');
            }

            if ($request->hasFile('national_id_proof') && $request->file('national_id_proof')->isValid()) {
                $national_id_proof = $request->file('national_id_proof');
                $national_id_proofFilename = StaticController::s3_file_uploader($national_id_proof, 'admission_national_id_proofs');
            }

            $application->photo = $photoFilename;
            $application->certificate10 = $certificate10Filename;
            $application->certificate12 = $certificate12Filename;
            $application->adhaar_doc = $adhaarFilename;
            $application->national_id_proof = $national_id_proofFilename;
            if (isset($baptismFilename)) {
                $application->baptism = $baptismFilename;
            } else {
                $application->baptism = null;
            }
        } catch (\Exception $e) {
            \Log::error('File upload error: ' . $e->getMessage());
            return back()->withErrors(['file_upload' => 'There was a problem uploading your documents. Please try again or contact support.'])->withInput();
        }
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


        if (!empty($request->adhaar_doc)) {
            $adhaarDoc = $request->adhaar_doc;
            $adhaarFilename = StaticController::s3_file_uploader($adhaarDoc, 'admission_adhaar_docs');
        } else {
            $adhaarFilename = null;
        }

        $application->photo = $photoFilename;
        $application->certificate10 = $certificate10Filename;
        $application->certificate12 = $certificate12Filename;
        $application->adhaar_doc = $adhaarFilename;

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

    function paymentCheckout(Request $request)
    {
        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;
        $userId = $user ? $user->id : null;
        if (!$userId) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
        }

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

    /**
     * Route payment to appropriate gateway
     */
    function processPayment(Request $request)
    {
        $gateway = $request->input('gateway', 'easebuzz');

        if ($gateway === 'billdesk') {
            return $this->initiateBillDeskPayment($request);
        } else {
            return $this->initateEaseBuzzPayment($request);
        }
    }

    function initateEaseBuzzPayment(Request $request)
    {
        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;
        $userId = $user ? $user->id : null;
        if (!$userId) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
        }
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
        $applicationId = $request->udf2;
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

        //Create Payment Log
        AdmissionApplicationPaymentLog::create([

            'application_id' => $applicationId,
            'txnid' => $txnid,
            'easepayid' => $easepayid,
            'user_id' => $userId,
            'amount' => $amount,
            'hash' => $hash,
            'msg' => $msg,
            'status' => $status,

        ]);

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
            "message" => '209860',
            "variables_values" => $var1 . '|' . $var2 . '|' . $var3,
            "route" => "dlt",
            "numbers" => $applicantPhone,
        );

        StaticController::smsSender($fields);
        //log User 
        $userData = AdmissionRegistration::where('id', $userId)->first();
        if ($userData) {
            Auth::login($userData, true);
        }

        $name = $userData ? Str::slug(trim($userData->first_name . ' ' . $userData->last_name)) : null;
        return redirect()->route('admission.apply.application', ['id' => $userId, 'name' => $name])->with('success', 'Payment successful. Your application is now complete.');
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
        if ($user) {
            Auth::login($user, true);
            $name = Str::slug(trim($user->first_name . ' ' . $user->last_name));
            return redirect()->route('admission.apply.application', ['id' => $user->id, 'name' => $name])->with('info', 'Payment failed. Please try again.');
        }

        return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'Session expired. Please login again.']);
    }

    /**
     * Webhook: Easebuzz server->server notifications
     */
    public function webhookEasebuzz(Request $request)
    {
        // Validate signature if Easebuzz sends one (check docs)
        // Example: $signature = $request->header('X-Easebuzz-Signature'); verify it
        $payload = $request->all();
        $application_code = $payload['udf2'] ?? null;

        if (!$application_code) {
            return response()->json(['status' => 'error', 'message' => 'application_code missing'], 400);
        }
        $payment = AdmissionApplication::where('application_code', $application_code)->first();

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
            'payment_gateway_ref' => $payload['easepayid'] ?? null,
            'status' => $status,
            'msg' => $payload['error_Message'] ?? null,
            'hash' => $payload['hash'] ?? null,
            'captured_amount' => $payload['amount'] ?? null,
        ]);

        // perform reconciliation, ledger updates etc.

        return response()->json(['status' => 'ok']);
    }

    // ================== BillDesk Payment Methods ==================

    /**
     * Initiate BillDesk Payment
     */
    function initiateBillDeskPayment(Request $request)
    {
        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;
        $userId = $user ? $user->id : null;

        if (!$userId) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
        }

        $applicationRegRecord = AdmissionRegistration::where('id', $userId)->first();
        $applicationRecord = AdmissionApplication::where('registration_id', $applicationRegRecord->id)->first();
        $applicationId = $applicationRecord->id;
        $invoice = $applicationRecord->application_code;

        // Calculate payable amount
        if ($applicationRegRecord->application_type == 'PG') {
            $payableAmount = AdmissionSetting::where('id', 1)->value('application_fee_pg');
        } else {
            $payableAmount = AdmissionSetting::where('id', 1)->value('application_fee_ug');
        }

        try {
            $billDeskService = new BillDeskService();

            $orderId = $invoice;
            $customerName = $applicationRegRecord->first_name . ' ' . $applicationRegRecord->last_name;
            $returnUrl = route('admission.payment.billdesk.response');

            $additionalInfo = [
                'info1' => 'Admission Application Fee',
                'info2' => $customerName,
                'info3' => $applicationRegRecord->mobile_no,
                'info4' => $applicationRegRecord->mail_id,
                'info5' => $userId,
                'info6' => $applicationId,
            ];

            $response = $billDeskService->createOrder($orderId, $payableAmount, $customerName, $returnUrl, $additionalInfo);

            if ($response['success']) {
                // Update application with BillDesk details
                $applicationRecord->gateway_type = 'billdesk';
                $applicationRecord->save();

                // Get payment page URL from links
                $paymentUrl = null;
                foreach ($response['links'] ?? [] as $link) {
                    if (isset($link['method']) && $link['method'] === 'GET' && isset($link['href'])) {
                        $paymentUrl = $link['href'];
                        break;
                    }
                }

                Log::info('BillDesk Payment View Data', [
                    'orderId' => $orderId,
                    'bdOrderId' => $response['bdOrderId'],
                    'paymentUrl' => $paymentUrl,
                    'linksCount' => count($response['links'] ?? [])
                ]);

                // Return view with BillDesk payment details
                return view('admission.billdesk-payment', [
                    'merchantId' => $response['merchantId'],
                    'bdOrderId' => $response['bdOrderId'],
                    'authToken' => $response['authToken'],
                    'returnUrl' => $returnUrl,
                    'orderId' => $orderId,
                    'amount' => $payableAmount,
                    'customerName' => $customerName,
                    'paymentUrl' => $paymentUrl,
                    'links' => $response['links'] ?? []
                ]);
            } else {
                Log::error('BillDesk Order Creation Failed', [
                    'orderId' => $orderId,
                    'error' => $response['error'] ?? 'Unknown',
                    'error_code' => $response['error_code'] ?? null,
                    'response' => $response['response'] ?? null
                ]);

                $errorMessage = $response['error'] ?? 'Unknown error';

                // Check for IP whitelisting issue
                if (isset($response['error_code']) && $response['error_code'] === 'GNAUE0006') {
                    $errorMessage = 'Server IP not authorized. Please contact administrator.';
                }

                return back()->withErrors(['payment' => 'Failed to initiate payment: ' . $errorMessage]);
            }
        } catch (\Exception $e) {
            Log::error('BillDesk Payment Error: ' . $e->getMessage());
            return back()->withErrors(['payment' => 'Payment initialization failed. Please try again.']);
        }
    }

    /**
     * BillDesk Payment Response Handler
     */
    public function billDeskResponse(Request $request)
    {
        $transactionId = $request->transaction_id ?? $request->txnid;
        $status = $request->transaction_status ?? $request->status;
        $orderId = $request->orderid ?? $request->txnid;
        $amount = $request->transaction_amount ?? $request->amount;

        // Find application by order ID
        $application = AdmissionApplication::where('application_code', $orderId)->first();

        if (!$application) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'Application not found.']);
        }

        $userId = $application->registration_id;
        $applicationId = $application->id;

        if ($status == 'SUCCESS' || $status == 'success') {
            // Update application payment details
            $application->update([
                'payment_gateway_ref' => $transactionId,
                'captured_amount' => $amount,
                'payment_gateway_status' => $status,
                'hash' => $request->signature ?? null,
            ]);

            // Create payment log
            AdmissionApplicationPaymentLog::create([
                'application_id' => $applicationId,
                'txnid' => $orderId,
                'easepayid' => $transactionId,
                'user_id' => $userId,
                'amount' => $amount,
                'hash' => $request->signature ?? null,
                'status' => $status,
                'msg' => 'BillDesk payment successful',
            ]);

            // Send email and SMS
            $registrationData = AdmissionRegistration::where('id', $userId)->first();
            $applicantEmail = $registrationData->mail_id;
            $applicantPhone = $registrationData->mobile_no;

            Mail::to($applicantEmail)->send(new ApplicationSuccessMail($orderId));

            // Send SMS
            $var1 = $orderId;
            $var2 = 9933402478;
            $var3 = 'admissionenquiry@salesiancollege.net';
            $fields = array(
                "sender_id" => 'SCSCLG',
                "message" => '209860',
                "variables_values" => $var1 . '|' . $var2 . '|' . $var3,
                "route" => "dlt",
                "numbers" => $applicantPhone,
            );
            StaticController::smsSender($fields);

            // Login user
            if ($registrationData) {
                Auth::login($registrationData, true);
            }

            $name = $registrationData ? Str::slug(trim($registrationData->first_name . ' ' . $registrationData->last_name)) : null;
            return redirect()->route('admission.apply.application', ['id' => $userId, 'name' => $name])
                ->with('success', 'Payment successful. Your application is now complete.');
        } else {
            // Payment failed
            AdmissionApplicationPaymentLog::create([
                'application_id' => $applicationId,
                'txnid' => $orderId,
                'easepayid' => $transactionId,
                'user_id' => $userId,
                'amount' => $amount,
                'hash' => $request->signature ?? null,
                'status' => $status,
                'msg' => 'BillDesk payment failed: ' . ($request->error_desc ?? 'Unknown error'),
            ]);

            $user = AdmissionRegistration::find($userId);
            if ($user) {
                Auth::login($user, true);
                $name = Str::slug(trim($user->first_name . ' ' . $user->last_name));
                return redirect()->route('admission.apply.application', ['id' => $user->id, 'name' => $name])
                    ->with('info', 'Payment failed. Please try again.');
            }

            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'Payment failed.']);
        }
    }

    /**
     * BillDesk Webhook Handler
     */
    public function webhookBillDesk(Request $request)
    {
        $payload = $request->all();
        Log::info('BillDesk Webhook Received', $payload);

        $orderId = $payload['orderid'] ?? null;

        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'orderid missing'], 400);
        }

        $application = AdmissionApplication::where('application_code', $orderId)->first();

        if (!$application) {
            ErrorLog::create(['details' => json_encode($payload)]);
            return response()->json(['status' => 'ok']);
        }

        // Update application with webhook data
        $status = $payload['transaction_status'] ?? 'pending';
        $application->update([
            'payment_gateway_ref' => $payload['transaction_id'] ?? null,
            'payment_gateway_status' => $status,
            'captured_amount' => $payload['transaction_amount'] ?? null,
            'hash' => $payload['signature'] ?? null,
        ]);

        return response()->json(['status' => 'ok']);
    }

    // ================== End BillDesk Payment Methods ==================



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
                'phase1_inst_ug' => $request->phase1_inst_ug,
                'phase2_inst_ug' => $request->phase2_inst_ug,

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
                'phase1_inst_pg' => $request->phase1_inst_pg,
                'phase2_inst_pg' => $request->phase2_inst_pg,
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
    function deptApplicationSingle($id)
    {
        $userId = Auth::user()->id;
        if (StaticController::fetchUserRole() !== 'dept-admin-erp') {
            return back()->with('info', 'Unauthorized access.');
        }

        $campusId = UserCampusSetting::where('user_id', $userId)->value('campus_id');
        $departId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');

        $data = AdmissionApplication::with([
            'registrationmaster.countrymaster',
        ])->where('id', $id)
            ->where('department', $departId)
            ->whereHas('registrationmaster', function ($query) use ($campusId) {
                $query->when($campusId, fn($q) => $q->where('campus_id', $campusId));
            })
            ->whereHas('registrationmaster.programinfo', function ($query) {
                $query->where('name', 'UG');
            })
            ->firstOrFail();

        return view('admin.admission.ug.application-single', ['data' => $data]);
    }

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
                        'applicationinfo.stdCourseMaster',
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
                        'applicationinfo.stdCourseMaster',
                    ])->whereHas('applicationinfo', function ($query) use ($departId) {
                        $query->where('department', $departId);
                    })
                        ->whereHas('registrationmaster', function ($query) use ($campusId) {
                            $query->where('campus_id', $campusId);
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
        $id = $request->id ?? Auth::id();
        $user = $id ? AdmissionRegistration::find($id) : null;
        $userId = $user ? $user->id : null;
        if (!$userId) {
            return redirect()->route('new.admission.login')->withErrors(['registered_no' => 'User not found. Please login again.']);
        }
        $registrationId = AdmissionRegistration::where('id', $userId)->value('id');
        $generatedNo = $userId  . rand(1000, 9999);

        //Department  auto filled based on the registration data for PG applicants,
        $subjectProgramData = SubjectHasStudentProgam::find($request->course);
        $deptId = $subjectProgramData->subject_id;
        $courseId = $subjectProgramData->student_program_id;

        $application = new AdmissionApplication();
        $application->user_id = $userId;
        $application->application_code = $generatedNo;
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
        $request->validate([

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


        $application = AdmissionApplication::find($id);
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


        if (!empty($request->photo)) {
            $photoFilename = StaticController::s3_resize_image_uploader($request->photo, 'admission_photos', 300, 300);
            $application->photo = $photoFilename;
        }

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


        if (!empty($request->certificate10)) {
            $certificate10 =  $request->certificate10;
            $certificate10Filename = StaticController::s3_file_uploader($certificate10, 'admission_cerificates10');
            $application->certificate10 = $certificate10Filename;
        }

        if (!empty($request->certificate12)) {
            $certificate12 =  $request->certificate12;
            $certificate12Filename = StaticController::s3_file_uploader($certificate12, 'admission_cerificates12');
            $application->certificate12 = $certificate12Filename;
        }

        $application->save();

        return back()->with('success', 'Application updated successfully.');
    }

    function editRegistration($id)
    {
        $registration = AdmissionRegistration::with(['campusmaster', 'countrymaster', 'applicationmaster'])->findOrFail($id);
        $campuses = Campus::all();
        $countries = Country::all();
        return view('admin.admission.edit-registration', [
            'registration' => $registration,
            'campuses'     => $campuses,
            'countries'    => $countries,
        ]);
    }

    function updateRegistration(Request $request, $id)
    {
        $request->validate([
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'mail_id'          => 'required|email|max:255|unique:admission_registrations,mail_id,' . $id,
            'mobile_no'        => 'required|digits:10|unique:admission_registrations,mobile_no,' . $id,
            'campus_id'        => 'required',
            'application_type' => 'required',
            'country'          => 'required',
            'account_status'   => 'required',
        ]);

        AdmissionRegistration::where('id', $id)->update([
            'first_name'       => $request->first_name,
            'last_name'        => $request->last_name,
            'mail_id'          => Str::lower($request->mail_id),
            'mobile_no'        => trim($request->mobile_no),
            'campus_id'        => $request->campus_id,
            'application_type' => $request->application_type,
            'country'          => $request->country,
            'account_status'   => $request->account_status,
            'otp_verification' => $request->otp_verification ?? 0,
        ]);

        return back()->with('success', 'Registration updated successfully.');
    }

    function updateOtpStatus($id)
    {
        AdmissionRegistration::where('id', $id)->update([
            'otp_verification' => '1',
            'account_status' => 1
        ]);

        return back()->with('success', 'OTP and Account status updated successfully.');
    }

    function getDepartmentsByCampusProgram($id)
    {
        $data = MainProgram::find($id);

        $campusId = $data->campus_id;
        $program = $data->name;

        return  Subject::where('campus_id', $campusId)->where('main_program_type', $program)->get();
    }


    function getCoursesByDepartment($deptId, $mainProgramid)
    {
        $batchId = BatchMaster::where('admission_active_batch', 1)->value('id');
        $data = MainProgram::find($mainProgramid);
        $campusId = $data->campus_id;

        return SubjectHasStudentProgam::where('subject_id', $deptId)->where('batch_id', $batchId)
            ->whereHas('studentprograminfo.campusmaster', function ($query) use ($campusId) {
                $query->where('id', $campusId);
            })->with('studentprograminfo')->get();
    }

    function applicantCampusShift(Request $request)
    {

        $request->validate([
            'application_id' => 'required',
            'campus' => 'required',
            'department' => 'required',
            'course' => 'required',
        ]);

        $data = AdmissionApplication::with('registrationmaster')->find($request->application_id);
        $mainProgram = MainProgram::find($request->campus);
        AdmissionRegistration::where('id', $data->registration_id)->update([
            'campus_id' => $mainProgram->campus_id,
            'application_type' => $mainProgram->name,
        ]);

        AdmissionApplication::where('id', $request->application_id)->update([
            'department' => $request->department,
            'course' => $request->course,
        ]);


        return redirect()->back()->with('success', 'Applicant campus shift successful.');
    }

    function verifyPayment($id)
    {
        $applicationRecord = AdmissionApplication::find($id);
        if (!$applicationRecord) {
            return back()->with('error', 'Application not found.');
        }
        $txnid = $applicationRecord->application_code;
        // Call the payment gateway API to verify payment status
        // This is a placeholder. You need to implement actual API call and response handling based on your payment gateway's documentation.

        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        if ($response['status'] == false) {
            return back()->with('error', $response['msg']);
        }
        $data =  $response['msg']['0'];
        return view('admin.admission.ez-payment-verification', ['data' => $data]);
    }

    function updateApplicationPayment(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'payment_gateway_ref' => 'required',
            'captured_amount' => 'required',
            'hash' => 'required',
            'payment_gateway_status' => 'required',
            'msg' => 'required',
        ]);

        $application  = AdmissionApplication::where('id', $request->id)->first();

        //Updating payment details in application record
        $application->update([
            'payment_gateway_ref' => $request->payment_gateway_ref,
            'captured_amount' => $request->captured_amount,
            'hash' => $request->hash,
            'payment_gateway_status' => $request->payment_gateway_status,
            'msg' => $request->msg,
        ]);

        //Update payment log
        $paymentLog =  AdmissionApplicationPaymentLog::where('application_id', $request->id)->first();
        if ($paymentLog) {
            $paymentLog->update([
                'application_id' => $request->id,
                'txnid' => $application->application_code,
                'easepayid' => $request->payment_gateway_ref,
                'user_id' => $application->user_id,
                'amount' => $request->captured_amount,
                'hash' => $request->hash,
                'msg' => $request->msg,
                'status' => $request->payment_gateway_status,
            ]);
        } else {
            AdmissionApplicationPaymentLog::create([

                'application_id' => $request->id,
                'txnid' => $application->application_code,
                'easepayid' => $request->payment_gateway_ref,
                'user_id' => $application->user_id,
                'amount' => $request->captured_amount,
                'hash' => $request->hash,
                'msg' => $request->msg,
                'status' => $request->payment_gateway_status,

            ]);
        }

        return back()->with('success', 'Payment status updated successfully.');
    }


    function adminFillStudentApplicationUg($id)
    {
        $registrationInfo = AdmissionRegistration::with([
            'campusmaster',
            'countrymaster',
        ])->where('id', $id)->first();

        if ($registrationInfo->application_status == 1) {
            return back()->with('info', 'Application already filled for this student.');
        } else {

            return view('admin.admission.ug.manual-application', ['data' => $registrationInfo]);
        }
    }


    function technicalMode()
    {
        return view('admission.technical-mode');
    }

    function testInchargeDashboard()
    {
        $campusId =  StaticController::fetchCampusSettings();
        $batch = BatchMaster::where('admission_active_batch', 1)->value('batch_name');

        $data = AdmissionFirstPhase::with([
            'registrationmaster',
            'applicationinfo',
        ])->whereHas('registrationmaster', function ($query) use ($campusId, $batch) {
            $query->where('batch', $batch);
            $query->where('campus_id', $campusId);
        })->latest()->get();


        return view('admin.admission.test-incharge.dashboard', ['data' => $data]);
    }

    function overrideUgPhase1Status($id)
    {

        $data = AdmissionFirstPhase::find($id);
        if (!$data) {
            return back()->with('error', 'Record not found.');
        }
        AdmissionFirstPhase::where('id', $id)->update([
            'document_verified' => 1,
            'proficiency_test_status' => 1,
            'dept_interview' => 1,
            'mgt_interview_status' => 1,
            'final_status' => 1,
        ]);

        //shift candidate to Phase 2 table
        AdmissionFinalPhase::create([
            'application_id' => $data->application_id,
            'reg_id' => $data->reg_id,
        ]);

        return back()->with('success', 'UG Phase 1 status updated successfully.');
    }

    function bulkOverrideUgPhase1Status(Request $request)
    {
        $applicantIds = $request->input('applicant_ids', []);

        if (empty($applicantIds)) {
            return back()->with('error', 'No applicants selected.');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($applicantIds as $id) {
            $data = AdmissionFirstPhase::find($id);

            if (!$data) {
                $errorCount++;
                continue;
            }

            // Update all statuses to approved/completed
            AdmissionFirstPhase::where('id', $id)->update([
                'document_verified' => 1,
                'proficiency_test_status' => 1,
                'dept_interview' => 1,
                'mgt_interview_status' => 1,
                'final_status' => 1,
            ]);

            // Check if already exists in Phase 2 before creating
            $existsInPhase2 = AdmissionFinalPhase::where('application_id', $data->application_id)->exists();

            if (!$existsInPhase2) {
                // Shift candidate to Phase 2 table
                AdmissionFinalPhase::create([
                    'application_id' => $data->application_id,
                    'reg_id' => $data->reg_id,
                ]);
            }

            $successCount++;
        }

        if ($errorCount > 0) {
            return back()->with('warning', "Bulk override completed. {$successCount} applicant(s) processed successfully, {$errorCount} failed.");
        }

        return back()->with('success', "Bulk override completed successfully for {$successCount} applicant(s).");
    }

    function activateApplicationPayment($id)
    {

        $studnetData = AdmissionRegistration::with('applicationmaster.stdCourseMaster')->find($id);
        //add student to master table
        $applicationData = $studnetData->applicationmaster;
        if (!$applicationData) {
            return back()->with('error', 'Application data not found for this student.');
        }

        // Check if student already exists
        $existingStudent = StudentMaster::where('user_code', $applicationData->application_code)->first();
        if ($existingStudent) {
            return back()->with('error', 'Student already exists in the master table with this application code.');
        }


        //generate Rollno based on the last roll no of the department and course
        if ($studnetData->campus_id == 1) {
            $prefix = "USO";
        } else {
            $prefix = "USL";
        }

        $batch = $studnetData->batch;
        $batch_id =  BatchMaster::where('batch_name', $batch)->value('id');
        $programCode = $applicationData->stdCourseMaster->code;
        //now fetch the last roll no for the same department and course
        $lastRollNo = StudentMaster::where('academic_dept_id', $applicationData->department)
            ->where('new_program_id', $applicationData->course)
            ->whereHas('campusmaster', function ($query) use ($studnetData) {
                $query->where('id', $studnetData->campus_id);
            })
            ->orderBy('roll_no', 'desc')
            ->value('roll_no');
        if ($lastRollNo == null) {
            $newRollNo = $prefix . $batch . $programCode . '001';
        } else {
            $lastRollNoNumber = (int) substr($lastRollNo, -3);
            $newRollNoNumber = $lastRollNoNumber + 1;
            $newRollNo = $prefix . $batch . $programCode  . str_pad($newRollNoNumber, 3, '0', STR_PAD_LEFT);
        }

        StudentMaster::create([
            'user_code' => $applicationData->application_code,
            'first_name' => $studnetData->first_name,
            'last_name' => $studnetData->last_name,
            'gender' => $applicationData->gender == 'male' ? 1 : 2,
            'dob' => date('d/m/Y', strtotime($applicationData->dob)),
            'user_type' => 'student',
            'nationality' => $applicationData->country,
            'caste' => strtolower($applicationData->caste),
            'religion' => $applicationData->religion,
            'department' => $applicationData->department,
            'academic_dept_id' => $applicationData->department,
            'new_program_id' => $applicationData->course,
            'batch' => $batch_id,
            'mobile_no' => $studnetData->mobile_no,
            'mail_id' => $studnetData->mail_id,
            'aadhar_no' => $applicationData->adhaar,
            'campus_id' => $studnetData->campus_id,
            'photo_path' => $applicationData->photo,
            'address' => $applicationData->permanent_address . ' ' . $applicationData->city . ' ' . $applicationData->state . ' ' . $applicationData->zip,
            'admission_date' => now(),
            'roll_no' => $newRollNo,
            'father_name' =>  $applicationData->father_name,
            'mother_name' => $applicationData->mother_name,
            'guardian_name' => $applicationData->guardian_name,
            'blood_group_id' => $applicationData->bloodgroup,
            'is_physically_challenged' => $applicationData->phychallenged,
            'mother_tongue' => $applicationData->mothertongue,
            'fr_mobile_no' => $applicationData->father_contact,
            'mr_mobile_no' => $applicationData->mother_contact,
            'guardian_mobile_no' => $applicationData->guardian_contact,
            'fr_occupation' => $applicationData->father_occupation,
            'mr_occupation' => $applicationData->mother_occupation,
            'annual_income' => $applicationData->income,
            'is_roman_catholic' => $applicationData->religion == 10 ? 1 : 0,
            'current_year' => 1,
            'user_type' => 'student',

        ]);

        //update registration  status as is_enrolled
        AdmissionRegistration::where('id', $id)->update([
            'is_enrolled' => 1, //1 indicates enrolled, 0 indicates not enrolled
        ]);


        return back()->with('success', 'Application payment activated and student added to master table successfully.');
    }
}
