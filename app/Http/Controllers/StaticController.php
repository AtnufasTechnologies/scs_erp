<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\AdminNotify;
use App\Models\AdmissionApplication;
use App\Models\AdmissionRegistration;
use App\Models\AnnualSession;
use App\Models\BatchMaster;
use App\Models\CiaMark;
use App\Models\CourseCombination;
use App\Models\Department;
use App\Models\FeeHead;
use App\Models\FeeStructureGroup;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\InterMark;
use App\Models\Otp;
use App\Models\ProgramGroup;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\StdProgComboMap;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\StudentPayment;
use App\Models\StudentProgram;
use App\Models\StudentRosterRuleMapping;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasPermission;
use App\Models\UserHasRole;
use App\Models\UserMenuPermission;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class StaticController extends Controller
{

  static function wait_page_process_cashfree($response)
  {
    $order_id = $response->order_id;
    $captured_amount = $response->order_amount;


    //Online Transaction - Update User Registration Status
    AdmissionApplication::where('application_id', $order_id)
      ->update(
        [
          'payment_gateway_id' => $response->cf_order_id,
          'captured_amount' => $captured_amount,
          'payment_gateway_status' => $response->order_status,
          'captured_currency' => $response->order_currency,

        ]
      );

    //ReLogin user
    $userId = $response->customer_details->customer_id;
    $userdata = User::find($userId);
    Auth::login($userdata, true);
  }


  static function payment_failed_cashfree($response)
  {
    $order_id = $response->order_id;

    $captured_amount = $response->order_amount;

    //Online Transaction
    AdmissionApplication::where('application_id', $order_id)
      ->update(
        [
          'payment_gateway_id' => $response->cf_order_id,
          'captured_amount' => $captured_amount,
          'payment_gateway_status' => $response->order_status,
          'captured_currency' => $response->order_currency,

        ]
      );

    //ReLogin user
    $userId = $response->customer_details->customer_id;
    $userdata = User::find($userId);
    Auth::login($userdata, true);
  }



  // static function myAdminNotification($message, $message_type)
  // {
  //   $rec = new AdminNotify();
  //   $rec->message = $message;
  //   $rec->message_type = $message_type;
  //   $rec->status = 'UNREAD';
  //   $rec->save();
  // }


  //S3 Storage Functions

  static function s3_image_uploader($img, $path)
  {

    $newImageName = $img->getClientOriginalName();
    //add timestamp to stop duplication
    $imgName =  Carbon::now()->timestamp . '_' . $newImageName;
    $imgName = preg_replace('/\s+/', '', $imgName);
    $image_resize =  Image::make($img->getRealPath());
    $image_resize = $image_resize->stream();
    $filename = $path . '/' . $imgName;
    Storage::disk('s3')->put($filename, $image_resize->__toString());
    return $filename;
  }

  static function s3_resize_image_uploader($img, $path, $resizeto)
  {
    $newImageName = $img->getClientOriginalName();
    //add timestamp to stop duplication
    $imgName =  Carbon::now()->timestamp . '_' . $newImageName;
    $imgName = preg_replace('/\s+/', '', $imgName);
    $image_resize =  Image::make($img->getRealPath());

    $image_resize->widen($resizeto, function ($constraint) {
      $constraint->upsize();
    });

    $image_resize = $image_resize->stream();
    $filename = $path . '/' . $imgName;
    Storage::disk('s3')->put($filename, $image_resize->__toString());

    return $filename;
  }

  static function s3_file_uploader($file, $path)
  {

    $newImageName = $file->getClientOriginalName();
    //add timestamp to stop duplication
    $renamed =  Carbon::now()->timestamp . '_' . $newImageName;
    $docName = preg_replace('/\s+/', '', $renamed);

    $filename = $path . '/' . $docName;
    Storage::disk('s3')->put($filename, file_get_contents($file));

    return $filename;
  }


  static function s3_file_unlink($img, $path)
  {
    if (Storage::disk('s3')->exists($path . '/' . $img)) {
      Storage::disk('s3')->delete($path . '/' . $img);
    }
  }



  static function delete_with_s3_image($model, $id, $path)
  {

    $data = $model::where('id', $id)->first();
    if (Storage::disk('s3')->exists($path . $data->pic)) {
      Storage::disk('s3')->delete($path . $data->pic);
    }
    $model::where('id', $data->id)->delete();
  }


  //S3 Ends

  static function easebuzz_verifyPaymentWithHash($txnid)
  {
    $key = env('EASEBUZZ_KEY');
    $salt = env('EASEBUZZ_SALT');

    $hash_string = "$key|$txnid|$salt";
    $hash = hash("sha512", $hash_string);

    $response = Http::withHeaders([
      'Content-Type' => 'application/json'
    ])->post('https://dashboard.easebuzz.in/transaction/v2.1/retrieve', [

      'txnid' => $txnid,
      'key' => $key,
      'hash' => $hash,

    ]);
    return $response->json();
  }




  static function sendOtp_onMail($user, $otpcode)
  {


    $html = View::make('emails.otp-mail', ['otpcode' => $otpcode])->render();

    $response = Http::withToken(env('RESEND_API_KEY'))
      ->post('https://api.resend.com/emails', [
        'from' => 'salesian college autonomous <onboarding@resend.dev>', // Use verified sender
        'to' =>  $user,
        'subject' => 'Salesian College Autonomous - Otp Verification Code',
        'html' => $html,
      ]);

    return $response->json();
  }

  // static function fetchDepartment($campus, $applicationType)
  // {
  //   return  Department::where('campus_id', $campus)->where('main_program_id', $applicationType)->get();
  // }

  static function activeSessionId()
  {
    $rec =  AnnualSession::where('status', 1)->first();
    $sessionId = $rec->id;
    return $sessionId;
  }

  static function feeStructureTotal($id)
  {
    $total =  FeeStructureHasHead::where('fee_structure_id', $id)->sum('amount');
    return $total;
  }

  static function fetchProgramGroup($campusid)
  {
    $data = ProgramGroup::where('campus_id', $campusid)->with([
      'programInfo',
    ])->get();
    return $data;
  }

  static function generateInvoiceId($prefix)
  {
    $year = now()->format('Y');
    $count = StudentPayment::whereYear('created_at', $year)->count() + 1;
    $txnno =  $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
    // $txnno = $prefix . round(microtime(true) * 1000);
    return $txnno;
  }

  static function fetchProgramGroupNew()
  {
    // Now fetching student programs directly instead of program groups
    $data = StudentProgram::with([
      'campusmaster'
    ])->get();

    return $data;
  }


  static function fetchCourseMasterGroups(int $id)
  {
    // Fetch student programs linked to course master
    $data = FeeStructureGroup::with([
      'programinfo',
      'programinfo.campusmaster'
    ])->where('fee_course_master_id', $id)->get();
    return $data;
  }

  static function fetchConnectedStudentPrograms(int $id)
  {
    $data = FeeStructureGroup::with([
      'programinfo',
    ])->where('fee_course_master_id', $id)->get();
    return $data;
  }


  static function assignRoleToUser($userId, $roles)
  {

    $user = User::find($userId);
    $user->roles = json_encode($roles);
    $user->save();
  }

  static function fetchUserPermissions($permissionType)
  {
    $userId = Auth::user()->id;
    $permission = UserHasPermission::where('user_id', $userId)->where('permission_name', $permissionType)->first();
    if ($permission != null) {
      return true;
    } else {
      return false;
    }
  }

  static function OtpGenerator($userId)
  {
    //Generate OTP 
    $otp = random_int(100000, 999999);
    $otrec = new Otp();
    $otrec->user_id = $userId;
    $otrec->otp = $otp;
    $otrec->save();

    return $otp;
  }

  /**SMS Senders */
  //Fast2SMS OTP Sender 
  static function otpSender($fields)
  {
    $response = Http::withHeaders([
      'authorization' => 'J3CgcsRHf5yLoFAdwUPIGBxntp06r1z92ZmuTbqQhjvEl8kO7Nw7OiRypJkHBLan0ezA9KuCs4PS5Uc3',
      'accept' => '*/*',
      'cache-control' => 'no-cache',
      'content-type' => 'application/json',
    ])->timeout(30)->post('https://www.fast2sms.com/dev/bulkV2', $fields);

    if ($response->failed()) {
      // You can log or handle the error as needed
      echo "HTTP Error: " . $response->body();
    } else {
      $jsonResponse = $response->json();
      // Optionally return or process $jsonResponse
      return $jsonResponse;
    }
  }

  static function smsSender($fields)
  {
    $response = Http::withHeaders([
      'authorization' => env('SMSAPIKEY'),
      'accept' => '*/*',
      'cache-control' => 'no-cache',
      'content-type' => 'application/json',
    ])->timeout(30)->post('https://www.fast2sms.com/dev/bulkV2', $fields);

    if ($response->failed()) {
      // You can log or handle the error as needed
      echo "HTTP Error: " . $response->body();
    } else {
      $jsonResponse = $response->json();
      // Optionally return or process $jsonResponse
      return $jsonResponse;
    }
  }

  static function bulkSmsSender($fields)

  {
    $client = new Client();
    $response = $client->request('POST', 'https://www.fast2sms.com/dev/custom', [
      'body' => $fields['body'],
      'headers' => [
        'accept' => 'application/json',
        'authorization' => env('SMSAPIKEY'),
        'content-type' => 'application/json',
      ],
    ]);
    return $response->getBody();
  }

  static function fetchMessageData($message_id)
  {
    $client = new Client();

    $response = $client->request('GET', 'https://www.fast2sms.com/dev/dlr/' . $message_id . '?authorization=' . env('SMSAPIKEY'));

    $jsonResponse = json_decode($response->getBody());
    return $jsonResponse;
  }

  /**sms sender ends */

  static function permissionValidator($permissionName)
  {
    return UserHasPermission::where('user_id', Auth::id())
      ->where('permission_name', $permissionName)
      ->exists();
  }

  static function fetchCampusSettings()
  {
    $campus_id = UserCampusSetting::where('user_id', Auth::id())->value('campus_id');
    return $campus_id;
  }

  static function addToStudentMaster($id)
  {
    //code to add student to student master
    $data = AdmissionRegistration::with([
      'studentInfo',
      'programinfo',
      'countrymaster',
      'applicationmaster',
      'programmaster'
    ])->find($id);
    //Generate Student RollNo

    //Insert record to StudentMaster

  }

  static function subMenuRights($slug)
  {
    return UserMenuPermission::where('user_id', Auth::id())
      ->whereHas('menu_master', function ($query) use ($slug) {
        $query->where('slug', $slug);
      })
      ->exists();
  }

  static function mainMenuRights($type)
  {
    return UserMenuPermission::where('user_id', Auth::id())
      ->whereHas('menu_master', function ($query) use ($type) {
        $query->where('module_type', $type);
      })
      ->exists();
  }

  static function fetchUserRole()
  {
    $role_name = UserHasRole::where('user_id', Auth::id())->value('role_name');
    return $role_name;
  }

  /**
   * Check if the current user (COE or DCOE) can see a specific COE sidebar menu.
   * COE can see everything. DCOE sees only assigned menus.
   */
  static function coeMenuAccess(string $slug)
  {
    $role = self::fetchUserRole();
    if ($role === 'coe') {
      return true;
    }
    if ($role === 'dcoe') {
      return \App\Models\DcoeMenuPermission::where('user_id', Auth::id())
        ->where('menu_slug', $slug)
        ->exists();
    }
    return false;
  }

  /**
   * Check if current user is COE (not DCOE). Used to show COE-only sections like DCOE management.
   */
  static function isCoe()
  {
    return self::fetchUserRole() === 'coe';
  }

  /**
   * Check if current user is the Principal.
   */
  static function isPrincipal()
  {
    return self::fetchUserRole() === 'principal';
  }

  /**
   * Get the campus ID for DCOE users. Returns null for COE (sees all campuses).
   */
  static function getDcoeCampusId()
  {
    $role = self::fetchUserRole();
    if ($role === 'dcoe') {
      $setting = \App\Models\UserCampusSetting::where('user_id', Auth::id())->first();
      return $setting ? $setting->campus_id : null;
    }
    return null;
  }

  static function fetchAdmissionRegistrationByMonths()
  {
    $data = AdmissionRegistration::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as count')
      ->groupBy('month')
      ->where('otp_verification', 1)
      ->get();

    return $data;
  }

  static function fetchAdmissionApplicationsByMonths()
  {
    $data = AdmissionApplication::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as count')
      ->groupBy('month')
      ->where('payment_gateway_status', 'success')
      ->get();

    return $data;
  }


  static function mostAppliedDepartment()
  {
    $data = AdmissionApplication::selectRaw('department, COUNT(*) as count')
      ->where('payment_gateway_status', 'success')
      ->groupBy('department')
      ->orderBy('count', 'desc')
      ->with('academicDeptMaster')
      ->get();

    return $data;
  }


  static function campusWiseEnrollment()
  {

    $data['sonada'] = AdmissionRegistration::where('campus_id', 1)->where('otp_verification', 1)->count();
    $data['siliguri'] = AdmissionRegistration::where('campus_id', 2)->where('otp_verification', 1)->count();

    return $data;
  }

  static function fetchFeeStructurePrograms(int $id)
  {
    $data = FeeStructureHasManyProgram::with([
      'studentprogram.campusmaster',
    ])->where('fee_structure_id', $id)->get();
    return $data;
  }

  static function getStudentCourseMarks($studentId, $courseId)
  {
    return CiaMark::where('STUDENT_ID', $studentId)
      ->with([
        'groupinfo.grouptype:id,name',
      ])
      ->where('course_id', $courseId)
      ->get();
  }

  static function getStudentCourseMarkTotal($studentId, $courseId)
  {
    return InterMark::where('student_id', $studentId)
      ->where('course_id', $courseId)
      ->first()->internal_mark ?? 0;
  }



  static function redirectResolvedStudentsToRoster(Request $request, $students)
  {
    $studentIds = collect($students)
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $curriculumRowId = (int) ($request->input('curriculum_row_id') ?: $request->input('curriculum_id'));
    $batchId = (int) $request->input('batch_id', 0);
    $semesterId = (int) $request->input('semester_id', 0);
    $teachingGroupId = (int) $request->input('teaching_group_id', 0);
    $teachingAssignmentId = (int) $request->input('teaching_assignment_id', 0);
    $curriculumSearch = trim((string) $request->input('curriculum_search', ''));
    $programCodeFilter = strtoupper(trim((string) $request->input('program_code_filter', '')));

    $query = array_filter([
      'batch_id' => $batchId,
      'semester_id' => $semesterId,
      'curriculum_row_id' => $curriculumRowId,
      'teaching_group_id' => $teachingGroupId,
      'teaching_assignment_id' => $teachingAssignmentId,
    ], fn($value) => (int) $value > 0);

    if ($curriculumSearch !== '') {
      $query['curriculum_search'] = $curriculumSearch;
    }

    if ($programCodeFilter !== '') {
      $query['program_code_filter'] = $programCodeFilter;
    }

    if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
      $resolvedStudents = collect($students)
        ->map(function ($student) {
          return [
            'id' => (int) ($student->id ?? 0),
            'roll_no' => (string) ($student->roll_no ?? ''),
            'register_no' => (string) ($student->register_no ?? ''),
            'first_name' => (string) ($student->first_name ?? ''),
            'last_name' => (string) ($student->last_name ?? ''),
            'student_name' => trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')),
            'new_program_id' => (int) ($student->new_program_id ?? 0),
            'batch' => (int) ($student->batch ?? 0),
            'academic_pathway_id' => (int) ($student->academic_pathway_id ?? 0),
            'degree_track_id' => (int) ($student->degree_track_id ?? 0),
          ];
        })
        ->values();

      return response()->json([
        'ok' => true,
        'count' => (int) $resolvedStudents->count(),
        'student_ids' => $studentIds->all(),
        'students' => $resolvedStudents,
        'context' => [
          'curriculum_row_id' => $curriculumRowId,
          'batch_id' => $batchId,
          'semester_id' => $semesterId,
          'teaching_group_id' => $teachingGroupId,
          'teaching_assignment_id' => $teachingAssignmentId,
          'curriculum_search' => $curriculumSearch,
          'program_code_filter' => $programCodeFilter,
        ],
        'redirect_url' => route('itcell.student-roster-engine.index', $query),
      ]);
    }

    return redirect()
      ->route('itcell.student-roster-engine.index', $query)
      ->with('resolved_student_ids', $studentIds->all())
      ->with('resolved_curriculum_row_id', $curriculumRowId)
      ->with('resolved_batch_id', $batchId)
      ->with('resolved_semester_id', $semesterId);
  }
  static  function resolveStudentList(Request $request)
  {

    // return $request->all();

    $curriculam_id = $request->curriculum_row_id; //checked row
    $batch_id = $request->batch_id;
    $semester_id = $request->semester_id;
    $program_id  = $request->program_id; // use this to find the department info

    $cr = ProgramWiseSemesterCourse::with('courseinfo')->find($curriculam_id);
    // return $cr;

    if (!$cr) {
      return self::redirectResolvedStudentsToRoster($request, collect());
    }

    $academic_pathway_id = $cr->academic_pathway_id;
    $degree_track_id = $cr->degree_track_id;
    $course_id = $cr->course_id;
    $selection_type = $cr->course_type; //AUTO
    $delivery_type = $cr->delivery_category; //COMBO1

    //for SINGLE MAJOR exclusively
    $specialization_master_id = $cr->specialization_master_id;
    $specialization_master_ids = $cr->specialization_master_ids;

    //Lets Find Rule Applicable

    $ruleAppl = StudentRosterRuleMapping::where('academic_pathway_id', $academic_pathway_id)
      ->where('degree_track_id', $degree_track_id)
      ->where('selection_type', $selection_type)
      ->where('delivery_type', $delivery_type)
      ->with('rule:id,rule_code')
      ->first();

        //return $ruleAppl;

    /** Single MAJOR RULES ENGINE
     * COMBO1 -- Full COMBO1 SCOPED
     */
    if (empty($ruleAppl)) {

      //without Specialization
      if ($specialization_master_id == null) {

        $comboInfo = StdProgComboMap::where('student_program_id', $program_id)->first();
        if (!$comboInfo) {
          return collect(); // or return [];
        }
        $subjectInfo = Subject::find($comboInfo->combo_id_1);
        $campus_id = $subjectInfo->campus_id;
        $programIds = StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)
          ->pluck('student_program_id')
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->unique()
          ->values();

        $studentsQuery = StudentMaster::query()
          ->where('batch', (int) $batch_id)
          ->where('campus_id', $campus_id)
          ->whereIn('new_program_id', $programIds->all())
          ->where('new_program_id', (int) $program_id);

        if ((int) $academic_pathway_id > 0) {
          $studentsQuery->where('academic_pathway_id', (int) $academic_pathway_id);
        }

        if ((int) $degree_track_id > 0) {
          $studentsQuery->where('degree_track_id', (int) $degree_track_id);
        }

        if (Schema::hasColumn('student_masters', 'is_deleted')) {
          $studentsQuery->where('is_deleted', 0);
        }

        if (Schema::hasColumn('student_masters', 'is_left')) {
          $studentsQuery->where(function ($q) {
            $q->whereNull('is_left')->orWhere('is_left', 0);
          });
        }

        if ((int) $semester_id > 0) {
          $studentsQuery->whereHas('activeSemesterConfig', function ($q) use ($semester_id) {
            $q->where('semester_id', (string) $semester_id);
          });
        }

        $students = $studentsQuery
          ->orderBy('roll_no')
          ->orderBy('first_name')
          ->get([
            'id',
            'roll_no',
            'register_no',
            'first_name',
            'last_name',
            'new_program_id',
            'batch',
            'academic_pathway_id',
            'degree_track_id',
          ]);

        return self::redirectResolvedStudentsToRoster($request, $students);
      } else {
        //with Sepcialization
        $comboInfo = StdProgComboMap::where('student_program_id', $program_id)->first();
        if (!$comboInfo) {
          return collect(); // or return [];
        }
        $subjectInfo = Subject::find($comboInfo->combo_id_1);
        $campus_id = $subjectInfo->campus_id;
        $programIds = StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)
          ->pluck('student_program_id')
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->unique()
          ->values();

        $studentsQuery = StudentMaster::query()
          ->where('batch', (int) $batch_id)
          ->where('campus_id', $campus_id)
          ->whereIn('new_program_id', $programIds->all())
          ->where('new_program_id', (int) $program_id);

        if ((int) $academic_pathway_id > 0) {
          $studentsQuery->where('academic_pathway_id', (int) $academic_pathway_id);
        }

        if ((int) $degree_track_id > 0) {
          $studentsQuery->where('degree_track_id', (int) $degree_track_id);
        }

        if (Schema::hasColumn('student_masters', 'is_deleted')) {
          $studentsQuery->where('is_deleted', 0);
        }

        if (Schema::hasColumn('student_masters', 'is_left')) {
          $studentsQuery->where(function ($q) {
            $q->whereNull('is_left')->orWhere('is_left', 0);
          });
        }

        if ((int) $semester_id > 0) {
          $studentsQuery->whereHas('activeSemesterConfig', function ($q) use ($semester_id) {
            $q->where('semester_id', (string) $semester_id);
          });
        }

        $rawSpecializationIds = $specialization_master_ids;
        if (is_string($rawSpecializationIds) && trim($rawSpecializationIds) !== '') {
          $decodedSpecializationIds = json_decode($rawSpecializationIds, true);
          if (is_array($decodedSpecializationIds)) {
            $rawSpecializationIds = $decodedSpecializationIds;
          }
        }

        $requiredSpecializationIds = collect([(int) $specialization_master_id])
          ->merge(collect((array) $rawSpecializationIds)->map(fn($id) => (int) $id))
          ->filter(fn($id) => $id > 0)
          ->unique()
          ->values();

        if ($requiredSpecializationIds->isNotEmpty()) {
          if (Schema::hasTable('student_specializations')) {
            $studentsQuery->whereExists(function ($query) use ($requiredSpecializationIds, $semester_id) {
              $query->select(DB::raw(1))
                ->from('student_specializations as ss')
                ->whereColumn('ss.student_id', 'student_masters.id')
                ->whereIn('ss.specialization_id', $requiredSpecializationIds->all());

              if (Schema::hasColumn('student_specializations', 'semester_id') && (int) $semester_id > 0) {
                $query->where(function ($q) use ($semester_id) {
                  $q->whereNull('ss.semester_id')->orWhere('ss.semester_id', (int) $semester_id);
                });
              }

              if (Schema::hasColumn('student_specializations', 'is_active')) {
                $query->where('ss.is_active', 1);
              }

              if (Schema::hasColumn('student_specializations', 'deleted_at')) {
                $query->whereNull('ss.deleted_at');
              }
            });
          } elseif (Schema::hasColumn('student_masters', 'specialization_master_id')) {
            $studentsQuery->whereIn('specialization_master_id', $requiredSpecializationIds->all());
          } elseif (Schema::hasColumn('student_masters', 'specialization_id')) {
            $studentsQuery->whereIn('specialization_id', $requiredSpecializationIds->all());
          }
        }

        $students = $studentsQuery
          ->orderBy('roll_no')
          ->orderBy('first_name')
          ->get([
            'id',
            'roll_no',
            'register_no',
            'first_name',
            'last_name',
            'new_program_id',
            'batch',
            'academic_pathway_id',
            'degree_track_id',
          ]);

        return self::redirectResolvedStudentsToRoster($request, $students);
      }
    }

    /** DUAL MAJOR RULES ENGINE
     * COMBO1 -- Done
     * COMBO2 -- Done
     * COMMON_AUTO -- DONE
     * COMMON_STUDENT_CHOICE
     * MDC_AUTO -- Done
     * MDC_STUDENT_STUDENT_CHOICE -- DONE
     */

    //COMBO1 -- Done
    if ($ruleAppl->roster_source == 'COMBO1' && $ruleAppl->delivery_type == 'COMBO1' && $selection_type == 'AUTO') {
      //find department 
      $comboInfo = StdProgComboMap::where('student_program_id', $program_id)->first();

      //now send the programs to find the student having batch,semester, academic_pathway, Degree to add them in the roster

      if (!$comboInfo) {
        return collect(); // or return [];
      }
      //campus_info 
      $subjectInfo = Subject::find($comboInfo->combo_id_1);
      $campus_id = $subjectInfo->campus_id;
      $programIds = StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)
        ->pluck('student_program_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      $studentsQuery = StudentMaster::query()
        ->where('batch', (int) $batch_id)
        ->where('campus_id', $campus_id)
        ->whereIn('new_program_id', $programIds->all());


      if ((int) $academic_pathway_id > 0) {
        $studentsQuery->where('academic_pathway_id', (int) $academic_pathway_id);
      }

      if ((int) $degree_track_id > 0) {
        $studentsQuery->where('degree_track_id', (int) $degree_track_id);
      }

      if (Schema::hasColumn('student_masters', 'is_deleted')) {
        $studentsQuery->where('is_deleted', 0);
      }

      if (Schema::hasColumn('student_masters', 'is_left')) {
        $studentsQuery->where(function ($q) {
          $q->whereNull('is_left')->orWhere('is_left', 0);
        });
      }

      if ((int) $semester_id > 0) {
        $studentsQuery->whereHas('activeSemesterConfig', function ($q) use ($semester_id) {
          $q->where('semester_id', (string) $semester_id);
        });
      }

      $students = $studentsQuery
        ->orderBy('roll_no')
        ->orderBy('first_name')
        ->get([
          'id',
          'roll_no',
          'register_no',
          'first_name',
          'last_name',
          'new_program_id',
          'batch',
          'academic_pathway_id',
          'degree_track_id',
        ]);

      return self::redirectResolvedStudentsToRoster($request, $students);
    }

    //COMBO2 -- Done
    if ($ruleAppl->roster_source == 'COMBO2' && $ruleAppl->delivery_type == 'COMBO2' && $selection_type == 'AUTO') {
      //find department 
      $comboInfo = StdProgComboMap::where('student_program_id', $program_id)->first();
      //   $programs =  StdProgComboMap::where('combo_id_2', $comboInfo->combo_id_2)->with('stdprograms')->get();
      //now send the programs to find the student having batch,semester, academic_pathway, Degree to add them in the roster
      if (!$comboInfo) {
        return collect(); // or return [];
      }
      //campus_info 
      $subjectInfo = Subject::find($comboInfo->combo_id_2);
      $campus_id = $subjectInfo->campus_id;
      $programIds = StdProgComboMap::where('combo_id_2', $comboInfo->combo_id_2)
        ->pluck('student_program_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      $studentsQuery = StudentMaster::query()
        ->where('campus_id', $campus_id)
        ->where('batch', (int) $batch_id)
        ->whereIn('new_program_id', $programIds->all());


      if ((int) $academic_pathway_id > 0) {
        $studentsQuery->where('academic_pathway_id', (int) $academic_pathway_id);
      }

      if ((int) $degree_track_id > 0) {
        $studentsQuery->where('degree_track_id', (int) $degree_track_id);
      }

      if (Schema::hasColumn('student_masters', 'is_deleted')) {
        $studentsQuery->where('is_deleted', 0);
      }

      if (Schema::hasColumn('student_masters', 'is_left')) {
        $studentsQuery->where(function ($q) {
          $q->whereNull('is_left')->orWhere('is_left', 0);
        });
      }

      if ((int) $semester_id > 0) {
        $studentsQuery->whereHas('activeSemesterConfig', function ($q) use ($semester_id) {
          $q->where('semester_id', (string) $semester_id);
        });
      }

      $students = $studentsQuery
        ->orderBy('roll_no')
        ->orderBy('first_name')
        ->get([
          'id',
          'roll_no',
          'register_no',
          'first_name',
          'last_name',
          'new_program_id',
          'batch',
          'academic_pathway_id',
          'degree_track_id',
        ]);

      return self::redirectResolvedStudentsToRoster($request, $students);
    }

    //COMMON_AUTO -- Done
    if ($ruleAppl->roster_source == 'COMBO1' && $ruleAppl->delivery_type == 'COMMON' && $selection_type == 'AUTO') {
      //find department 
      $comboInfo = StdProgComboMap::where('student_program_id', $program_id)->first();
      $programs =  StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)->with('stdprograms')->get();
      //now send the programs to find the student having batch,semester, academic_pathway, Degree to add them in the roster
      if (!$comboInfo) {
        return collect(); // or return [];
      }
      //campus_info 
      $subjectInfo = Subject::find($comboInfo->combo_id_1);
      $campus_id = $subjectInfo->campus_id;
      $programIds = StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)
        ->pluck('student_program_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      $studentsQuery = StudentMaster::query()
        ->where('campus_id', $campus_id)
        ->where('batch', (int) $batch_id)
        ->whereIn('new_program_id', $programIds->all());


      if ((int) $academic_pathway_id > 0) {
        $studentsQuery->where('academic_pathway_id', (int) $academic_pathway_id);
      }

      if ((int) $degree_track_id > 0) {
        $studentsQuery->where('degree_track_id', (int) $degree_track_id);
      }

      if (Schema::hasColumn('student_masters', 'is_deleted')) {
        $studentsQuery->where('is_deleted', 0);
      }

      if (Schema::hasColumn('student_masters', 'is_left')) {
        $studentsQuery->where(function ($q) {
          $q->whereNull('is_left')->orWhere('is_left', 0);
        });
      }

      if ((int) $semester_id > 0) {
        $studentsQuery->whereHas('activeSemesterConfig', function ($q) use ($semester_id) {
          $q->where('semester_id', (string) $semester_id);
        });
      }

      $students = $studentsQuery
        ->orderBy('roll_no')
        ->orderBy('first_name')
        ->get([
          'id',
          'roll_no',
          'register_no',
          'first_name',
          'last_name',
          'new_program_id',
          'batch',
          'academic_pathway_id',
          'degree_track_id',
        ]);

      return self::redirectResolvedStudentsToRoster($request, $students);
    }

    //MDC_AUTO -- Done
    if ($ruleAppl->roster_source == 'COMBO1' && $ruleAppl->delivery_type == 'MDC' && $selection_type == 'AUTO') {
      //find department 
      $comboInfo = StdProgComboMap::where('student_program_id', $program_id)->first();
      $programs =  StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)->with('stdprograms')->get();
      //now send the programs to find the student having batch,semester, academic_pathway, Degree to add them in the roster
      if (!$comboInfo) {
        return collect(); // or return [];
      }
      //campus_info 
      $subjectInfo = Subject::find($comboInfo->combo_id_1);
      $campus_id = $subjectInfo->campus_id;
      $programIds = StdProgComboMap::where('combo_id_1', $comboInfo->combo_id_1)
        ->pluck('student_program_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      $studentsQuery = StudentMaster::query()
        ->where('campus_id', $campus_id)
        ->where('batch', (int) $batch_id)
        ->whereIn('new_program_id', $programIds->all());


      if ((int) $academic_pathway_id > 0) {
        $studentsQuery->where('academic_pathway_id', (int) $academic_pathway_id);
      }

      if ((int) $degree_track_id > 0) {
        $studentsQuery->where('degree_track_id', (int) $degree_track_id);
      }

      if (Schema::hasColumn('student_masters', 'is_deleted')) {
        $studentsQuery->where('is_deleted', 0);
      }

      if (Schema::hasColumn('student_masters', 'is_left')) {
        $studentsQuery->where(function ($q) {
          $q->whereNull('is_left')->orWhere('is_left', 0);
        });
      }

      if ((int) $semester_id > 0) {
        $studentsQuery->whereHas('activeSemesterConfig', function ($q) use ($semester_id) {
          $q->where('semester_id', (string) $semester_id);
        });
      }

      $students = $studentsQuery
        ->orderBy('roll_no')
        ->orderBy('first_name')
        ->get([
          'id',
          'roll_no',
          'register_no',
          'first_name',
          'last_name',
          'new_program_id',
          'batch',
          'academic_pathway_id',
          'degree_track_id',
        ]);

      return self::redirectResolvedStudentsToRoster($request, $students);
    }

    //MDC_STUDENT_CHOICE
    if ($ruleAppl->roster_source == 'STUDENT_SELECTION' && $ruleAppl->delivery_type == 'MDC' && $selection_type == 'STUDENT_CHOICE') {
      $comboInfo = StdProgComboMap::query()->where('student_program_id', $program_id)->first();
      if (!$comboInfo) {
        return collect();
      }

      $subjectInfo = Subject::query()->find((int) $comboInfo->combo_id_1);
      if (!$subjectInfo) {
        return collect();
      }

      $batchInfo = BatchMaster::query()->find((int) $batch_id);
      if (!$batchInfo) {
        return collect();
      }

      $campus_id = (int) ($subjectInfo->campus_id ?? 0);

      $programIds = StudentCourseInfo::query()
        ->where('course_id', $course_id)
        ->where('semester', $semester_id)
        ->where('campus_id', $campus_id)
        ->where('academic_year', (string) $batchInfo->batch_name)
        ->pluck('student_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->values();

      $studentsQuery = StudentMaster::query()
        ->whereIn('id', $programIds->all())
        ->where('new_program_id', (int) $program_id);

      $students = $studentsQuery
        ->orderBy('roll_no')
        ->orderBy('first_name')
        ->get([
          'id',
          'roll_no',
          'register_no',
          'first_name',
          'last_name',
          'new_program_id',
          'batch',
          'academic_pathway_id',
          'degree_track_id',
        ]);

      return self::redirectResolvedStudentsToRoster($request, $students);
    }

    //COMMON_STUDENT_CHOICE
    if ($ruleAppl->roster_source == 'STUDENT_SELECTION' && $ruleAppl->delivery_type == 'COMMON' && $selection_type == 'STUDENT_CHOICE') {
      $comboInfo = StdProgComboMap::query()->where('student_program_id', $program_id)->first();
      if (!$comboInfo) {
        return collect();
      }

      $subjectInfo = Subject::query()->find((int) $comboInfo->combo_id_1);
      if (!$subjectInfo) {
        return collect();
      }

      $batchInfo = BatchMaster::query()->find((int) $batch_id);
      if (!$batchInfo) {
        return collect();
      }

      $campus_id = (int) ($subjectInfo->campus_id ?? 0);

      $programIds = StudentCourseInfo::query()
        ->where('course_id', $course_id)
        ->where('semester', $semester_id)
        ->where('campus_id', $campus_id)
        ->where('academic_year', (string) $batchInfo->batch_name)
        ->pluck('student_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->values();

      $studentsQuery = StudentMaster::query()
        ->whereIn('id', $programIds->all())
        ->where('new_program_id', (int) $program_id);

      $students = $studentsQuery
        ->orderBy('roll_no')
        ->orderBy('first_name')
        ->get([
          'id',
          'roll_no',
          'register_no',
          'first_name',
          'last_name',
          'new_program_id',
          'batch',
          'academic_pathway_id',
          'degree_track_id',
        ]);

      return self::redirectResolvedStudentsToRoster($request, $students);
    }
  }
}
