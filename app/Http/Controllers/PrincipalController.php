<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campus;
use App\Models\StudentMaster;
use App\Models\Faculty;
use App\Models\DepartmentMaster;
use App\Models\SubjectHasRoutine;
use App\Models\HourMaster;
use App\Models\Weekday;
use App\Models\FacultyLeaveApplication;
use App\Models\SubUnitStudentFeedback;
use App\Models\AdmissionRegistration;
use App\Models\AdmissionApplication;
use App\Models\ProgramGroup;
use App\Models\User;
use App\Models\UserHasRole;
use App\Models\UserCampusSetting;
use App\Models\WorkDiary;
use App\Models\SubjectHasSyllabus;
use App\Models\SyllabusSubunit;
use App\Models\StudentCourseInfo;
use App\Models\Semester;
use App\Models\InterMark;
use App\Models\StudentAttendance;
use App\Models\BatchMaster;
use App\Models\ReligionMaster;
use App\Models\NationalityMaster;
use App\Models\BloodGroupMaster;
use App\Models\ProgramCourseMaster;
use App\Models\Subject;
use App\Models\CoHasCso;
use App\Models\SyllabusManager;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Result;
use App\Models\FeesStructure;
use App\Models\StudentPayment;
use App\Models\LateFee;
use App\Models\StudentLateFeeExemption;
use App\Models\ExtraClassAttendance;
use App\Models\FacultySubstitution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PrincipalController extends Controller
{
  /**
   * Principal Dashboard - Bird's eye view of entire system across both campuses.
   */
  public function dashboard()
  {
    $campuses = Campus::all();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';
    $vpCampusId = null;

    // Get VP's campus if applicable
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $campuses = Campus::where('id', $vpCampusId)->get();
      }
    }

    // Student stats per campus
    $studentStats = [];
    foreach ($campuses as $campus) {
      $studentStats[$campus->id] = [
        'name' => $campus->name,
        'total' => StudentMaster::where('campus_id', $campus->id)->count(),
        'active' => StudentMaster::where('campus_id', $campus->id)->where('is_active', 1)->count(),
      ];
    }

    // Total students (filtered by campus for VP)
    $totalStudentsQuery = StudentMaster::query();
    if ($isVicePrincipal && $vpCampusId) {
      $totalStudentsQuery->where('campus_id', $vpCampusId);
    }
    $totalStudents = $totalStudentsQuery->count();

    // Faculty stats (filtered by campus for VP)
    $totalFacultyQuery = Faculty::where(function ($q) {
      $q->whereNull('IS_LEFT')->orWhere('IS_LEFT', 0);
    });
    if ($isVicePrincipal && $vpCampusId) {
      $totalFacultyQuery->where('CAMPUS_ID', $vpCampusId);
    }
    $totalFaculty = $totalFacultyQuery->count();

    // Faculty on leave today (filtered by campus for VP)
    $facultyOnLeaveTodayQuery = FacultyLeaveApplication::where('status', 'approved')
      ->whereDate('start_date', '<=', today())
      ->whereDate('end_date', '>=', today());
    if ($isVicePrincipal && $vpCampusId) {
      $deptIds = DepartmentMaster::where('campus_id', $vpCampusId)->pluck('id');
      $facultyIds = Faculty::whereIn('DEPARTMENT', $deptIds)->pluck('id');
      $facultyOnLeaveTodayQuery->whereIn('faculty_id', $facultyIds);
    }
    $facultyOnLeaveToday = $facultyOnLeaveTodayQuery->count();

    // Admission stats (filtered by campus for VP)
    $totalRegistrationsQuery = AdmissionRegistration::where('otp_verification', 1);
    if ($isVicePrincipal && $vpCampusId) {
      $totalRegistrationsQuery->where('campus_id', $vpCampusId);
    }
    $totalRegistrations = $totalRegistrationsQuery->count();

    $totalApplicationsQuery = AdmissionApplication::query();
    if ($isVicePrincipal && $vpCampusId) {
      $totalApplicationsQuery->whereHas('registrationmaster', function ($q) use ($vpCampusId) {
        $q->where('campus_id', $vpCampusId);
      });
    }
    $totalApplications = $totalApplicationsQuery->count();

    $registrationsByCampusQuery = AdmissionRegistration::where('otp_verification', 1)
      ->select('campus_id', DB::raw('count(*) as total'))
      ->groupBy('campus_id');
    if ($isVicePrincipal && $vpCampusId) {
      $registrationsByCampusQuery->where('campus_id', $vpCampusId);
    }
    $registrationsByCampus = $registrationsByCampusQuery->pluck('total', 'campus_id');

    // Today's classes count (filtered by campus for VP)
    $todayWeekday = now()->dayOfWeekIso;
    $todayClassesQuery = SubjectHasRoutine::whereHas('weekdaymaster', function ($q) use ($todayWeekday) {
      $q->where('id', $todayWeekday);
    });
    if ($isVicePrincipal && $vpCampusId) {
      $todayClassesQuery->whereHas('lecturehallmaster.acblockmaster', function ($q) use ($vpCampusId) {
        $q->where('campus_id', $vpCampusId);
      });
    }
    $todayClassesCount = $todayClassesQuery->count();

    // Pending leaves (filtered by campus for VP)
    $pendingLeavesQuery = FacultyLeaveApplication::where('status', 'pending');
    if ($isVicePrincipal && $vpCampusId) {
      $deptIds = DepartmentMaster::where('campus_id', $vpCampusId)->pluck('id');
      $facultyIds = Faculty::whereIn('DEPARTMENT', $deptIds)->pluck('id');
      $pendingLeavesQuery->whereIn('faculty_id', $facultyIds);
    }
    $pendingLeaves = $pendingLeavesQuery->count();

    // Department count (filtered by campus for VP)
    $totalDepartmentsQuery = DepartmentMaster::query();
    if ($isVicePrincipal && $vpCampusId) {
      $totalDepartmentsQuery->where('campus_id', $vpCampusId);
    }
    $totalDepartments = $totalDepartmentsQuery->count();

    // Programs count
    $totalPrograms = ProgramGroup::count();

    return view('principal.dashboard', compact(
      'campuses',
      'studentStats',
      'totalStudents',
      'totalFaculty',
      'facultyOnLeaveToday',
      'totalRegistrations',
      'totalApplications',
      'registrationsByCampus',
      'todayClassesCount',
      'pendingLeaves',
      'totalDepartments',
      'totalPrograms'
    ));
  }

  /**
   * Student listing - both campuses with campus filter.
   */
  public function students(Request $request)
  {
    $campuses = Campus::all();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    $query = StudentMaster::with([
      'religionmaster:id,name',
      'deptmaster:id,department_code,name',
      'campusmaster:id,slug,name',
      'nationalitymaster:id,name',
      'bloodgroup',
      'batchmaster:id,batch_name',
      'programgroup.programInfo'
    ]);

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $query->where('campus_id', $vpCampusId);
        $request->merge(['campus_id' => $vpCampusId]);
      }
    } elseif ($request->filled('campus_id')) {
      $query->where('campus_id', $request->campus_id);
    }

    $data = $query->get();
    $selectedCampus = $request->campus_id;

    return view('principal.students.index', compact('data', 'campuses', 'selectedCampus'));
  }

  /**
   * Faculty listing with classes, timetable, leaves, feedback info.
   */
  public function faculty(Request $request)
  {
    $campuses = Campus::all();
    $departments = DepartmentMaster::all();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    $query = Faculty::where('is_left', 0)->whereNull('deleted_at');

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $deptIds = DepartmentMaster::where('campus_id', $vpCampusId)->pluck('id');
        $query->whereIn('DEPARTMENT', $deptIds);
        $request->merge(['campus_id' => $vpCampusId]);
      }
    } elseif ($request->filled('campus_id')) {
      $deptIds = DepartmentMaster::where('campus_id', $request->campus_id)->pluck('id');
      $query->whereIn('DEPARTMENT', $deptIds);
    }

    if ($request->filled('department_id')) {
      $query->where('DEPARTMENT', $request->department_id);
    }

    $facultyList = $query->get();

    // Enrich each faculty with extra info
    foreach ($facultyList as $fac) {
      $fac->department_info = DepartmentMaster::find($fac->DEPARTMENT);
      $fac->classes_count = SubjectHasRoutine::where('faculty_id', $fac->id)->count();
      $fac->pending_leaves = FacultyLeaveApplication::where('faculty_id', $fac->id)
        ->where('status', 'pending')->count();
      $fac->approved_leaves = FacultyLeaveApplication::where('faculty_id', $fac->id)
        ->where('status', 'approved')->count();

      // Course completion stats
      $syllabusIds = SubjectHasRoutine::where('faculty_id', $fac->id)->pluck('syllabus_id')->filter();
      if ($syllabusIds->count()) {
        $totalSubunits = SyllabusSubunit::whereIn('syllabus_manager_id', $syllabusIds)->count();
        $completedSubunits = SyllabusSubunit::whereIn('syllabus_manager_id', $syllabusIds)->where('is_completed', 1)->count();
        $fac->total_subunits = $totalSubunits;
        $fac->completed_subunits = $completedSubunits;
        $fac->completion_percent = $totalSubunits > 0 ? round(($completedSubunits / $totalSubunits) * 100, 1) : 0;
      } else {
        $fac->total_subunits = 0;
        $fac->completed_subunits = 0;
        $fac->completion_percent = 0;
      }

      // Pending work diary entries
      $fac->pending_diary = WorkDiary::where('faculty_id', $fac->id)->where('status', 'pending')->count();
    }

    $selectedCampus = $request->campus_id;
    $selectedDepartment = $request->department_id;

    return view('principal.faculty.index', compact(
      'facultyList',
      'campuses',
      'departments',
      'selectedCampus',
      'selectedDepartment'
    ));
  }

  /**
   * Faculty detail - timetable, leaves, feedback.
   */
  public function facultyDetail($id)
  {
    $faculty = Faculty::findOrFail($id);
    $faculty->department_info = DepartmentMaster::find($faculty->DEPARTMENT);

    // Timetable
    $timetable = SubjectHasRoutine::with([
      'weekdaymaster',
      'hourmaster',
      'lecturehallmaster',
      'subjectCourse.courseMaster.semestermaster',
      'subjectCourse.courseMaster.coursetypemaster',
      'batch'
    ])->where('faculty_id', $id)->get();

    // Organize by weekday for display
    $weekdays = Weekday::all();
    $hours = HourMaster::all();
    $timetableGrid = [];
    foreach ($weekdays as $day) {
      $timetableGrid[$day->id] = [
        'day' => $day->title,
        'slots' => []
      ];
      foreach ($hours as $hour) {
        $slot = $timetable->first(function ($r) use ($day, $hour) {
          return $r->weekday_id == $day->id && $r->hour_id == $hour->id;
        });
        $timetableGrid[$day->id]['slots'][$hour->id] = [
          'hour' => $hour->title,
          'routine' => $slot
        ];
      }
    }

    // Leaves
    $leaves = FacultyLeaveApplication::with('leaveMaster')
      ->where('faculty_id', $id)
      ->orderBy('created_at', 'desc')
      ->limit(20)
      ->get();

    // Feedback (via routines → syllabus → subunits)
    $routineIds = SubjectHasRoutine::where('faculty_id', $id)->pluck('syllabus_id');
    $feedback = SubUnitStudentFeedback::with('student:id,first_name,last_name,roll_no')
      ->whereHas('syllabusSubunit', function ($q) use ($routineIds) {
        $q->whereIn('syllabus_manager_id', $routineIds);
      })
      ->orderBy('created_at', 'desc')
      ->limit(30)
      ->get();

    return view('principal.faculty.detail', compact(
      'faculty',
      'timetableGrid',
      'weekdays',
      'hours',
      'leaves',
      'feedback'
    ));
  }

  /**
   * Classes happening hour-wise based on campus selection.
   */
  public function classes(Request $request)
  {
    $campuses = Campus::all();
    $selectedDate = $request->date ?? today()->format('Y-m-d');

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    $selectedCampus = $request->campus_id;
    $dayOfWeek = \Carbon\Carbon::parse($selectedDate)->dayOfWeekIso;
    $hours = HourMaster::all();

    $query = SubjectHasRoutine::with([
      'weekdaymaster',
      'hourmaster',
      'lecturehallmaster.acblockmaster',
      'faculty',
      'subjectCourse',
      'batch'
    ])->whereHas('weekdaymaster', function ($q) use ($dayOfWeek) {
      $q->where('id', $dayOfWeek);
    });

    // Filter by campus through lecture hall → academic block → campus_id
    if ($request->filled('campus_id')) {
      $query->whereHas('lecturehallmaster.acblockmaster', function ($q) use ($selectedCampus) {
        $q->where('campus_id', $selectedCampus);
      });
    }

    $classes = $query->get();

    // Group by hour
    $classesByHour = [];
    foreach ($hours as $hour) {
      $classesByHour[$hour->id] = [
        'hour' => $hour->title,
        'classes' => $classes->where('hour_id', $hour->id)->values()
      ];
    }

    return view('principal.classes.index', compact(
      'campuses',
      'selectedCampus',
      'selectedDate',
      'classesByHour',
      'hours'
    ));
  }

  /**
   * Admission registrations and applications - both campuses.
   */
  public function admissions(Request $request)
  {
    $campuses = Campus::all();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    $selectedCampus = $request->campus_id;

    // Registrations
    $regQuery = AdmissionRegistration::with('campusmaster')
      ->where('otp_verification', 1);
    if ($request->filled('campus_id')) {
      $regQuery->where('campus_id', $request->campus_id);
    }
    $registrations = $regQuery->orderBy('created_at', 'desc')->get();

    // Applications
    $appQuery = AdmissionApplication::with('registrationmaster.campusmaster');
    if ($request->filled('campus_id')) {
      $appQuery->whereHas('registrationmaster', function ($q) use ($request) {
        $q->where('campus_id', $request->campus_id);
      });
    }
    $applications = $appQuery->orderBy('created_at', 'desc')->get();

    // Summary stats
    $regByCampus = AdmissionRegistration::where('otp_verification', 1)
      ->select('campus_id', DB::raw('count(*) as total'))
      ->groupBy('campus_id')
      ->pluck('total', 'campus_id');

    $appByCampus = DB::table('admission_applications')
      ->join('admission_registrations', 'admission_applications.registration_id', '=', 'admission_registrations.id')
      ->select('admission_registrations.campus_id', DB::raw('count(*) as total'))
      ->groupBy('admission_registrations.campus_id')
      ->pluck('total', 'campus_id');

    return view('principal.admissions.index', compact(
      'campuses',
      'selectedCampus',
      'registrations',
      'applications',
      'regByCampus',
      'appByCampus'
    ));
  }

  /**
   * Student profile - read-only view reusing admin student-profile view.
   */
  public function studentProfile($id, $rollno)
  {
    $data = StudentMaster::where('id', $id)->with([
      'religionmaster:id,name',
      'deptmaster:id,department_code,name',
      'campusmaster:id,slug,name',
      'nationalitymaster:id,name',
      'usertype:id,name',
      'bloodgroup',
      'batchmaster:id,batch_name',
      'programgroup.programInfo',
      'feepayment.feepaymentinfo:id,quarter_title',
      'feepayment.gatewaytype'
    ])->firstOrFail();

    $studentCourses = StudentCourseInfo::with([
      'coursemaster.semestermaster:id,title',
      'coursemaster.coursetypemaster:id,title',
    ])
      ->where('student_id', $id)
      ->whereNull('deleted_at')
      ->get();

    $semesterMap = Semester::pluck('title', 'id')->toArray();
    $coursesBySemester = $studentCourses->sortBy(fn($c) => $c->semester ?? 999)
      ->groupBy(fn($c) => $semesterMap[$c->semester] ?? ('Semester ' . ($c->semester ?? '?')));

    $faMarkedCourseIds = InterMark::where('student_id', $id)->pluck('course_id')->unique()->toArray();
    $saMarkedCourseIds = DB::table('exam_marks_entries')->where('erp_student_id', $id)->pluck('erp_subject_id')->unique()->toArray();
    $lockedCourseIds = array_unique(array_merge($faMarkedCourseIds, $saMarkedCourseIds));

    $enrolledCourseIds = $studentCourses->pluck('course_id')->toArray();
    $availableCourses = ProgramCourseMaster::where('is_deleted', 0)
      ->whereNotIn('id', $enrolledCourseIds)
      ->with('semestermaster:id,title', 'coursetypemaster:id,title')
      ->orderBy('semester_id')
      ->orderBy('course_title')
      ->get()
      ->groupBy(fn($c) => $c->semester_id);

    $availableSemesters = Semester::orderBy('id')->get();

    $timetable = SubjectHasRoutine::where('batch_id', $data->batch)
      ->with([
        'weekdaymaster:id,title',
        'hourmaster:id,title',
        'lecturehallmaster:id,title',
        'faculty:id,FIRST_NAME,LAST_NAME',
        'coursemaster:id,course_title,course_code',
      ])
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get();

    $timetableByDay = $timetable->groupBy(fn($r) => $r->weekdaymaster->title ?? 'Unknown');

    $attendanceRaw = StudentAttendance::where('student_id', $id)
      ->with('courseinfo:id,course_title,course_code')
      ->get()
      ->groupBy('course_id');

    $attendanceSummary = $attendanceRaw->map(function ($records) {
      $total = $records->count();
      $present = $records->where('status', 'present')->count();
      return [
        'course' => $records->first()->courseinfo,
        'total' => $total,
        'present' => $present,
        'absent' => $total - $present,
        'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
      ];
    })->values();

    $internalMarks = InterMark::where('student_id', $id)
      ->with(['course:id,course_title,course_code', 'semester:id,title'])
      ->orderBy('semester')
      ->get();

    $examStudent = ExamStudent::where('erp_student_id', $id)->first();
    $examResults = collect();
    if ($examStudent) {
      $examResults = Result::where('exam_student_id', $examStudent->id)
        ->where('is_published', true)
        ->with(['examSession', 'resultSubjects'])
        ->orderByDesc('created_at')
        ->get();
    }

    return view('admin.master.student-profile', [
      'data' => $data,
      'studentCourses' => $studentCourses,
      'coursesBySemester' => $coursesBySemester,
      'lockedCourseIds' => $lockedCourseIds,
      'availableCourses' => $availableCourses,
      'availableSemesters' => $availableSemesters,
      'timetableByDay' => $timetableByDay,
      'attendanceSummary' => $attendanceSummary,
      'internalMarks' => $internalMarks,
      'examResults' => $examResults,
      'examStudent' => $examStudent,
      'batches' => BatchMaster::orderBy('batch_name')->get(),
      'departments' => DepartmentMaster::orderBy('name')->get(),
      'campuses' => Campus::orderBy('name')->get(),
      'religions' => ReligionMaster::orderBy('name')->get(),
      'nationalities' => NationalityMaster::orderBy('name')->get(),
      'bloodGroups' => BloodGroupMaster::orderBy('name')->get(),
    ]);
  }

  /**
   * Courses with CSO subunits, completion status, and student feedback.
   */
  public function courses(Request $request)
  {
    $campuses = Campus::all();
    $departments = DepartmentMaster::all();
    $semesters = Semester::orderBy('id')->get();
    $academicYears = ProgramCourseMaster::select('academic_year')
      ->distinct()->orderBy('academic_year', 'desc')->pluck('academic_year');

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    $query = SubjectHasSyllabus::with([
      'subject',
      'batchmaster',
      'semestermaster',
      'syllabusunits.csoSubunit',
      'timetable.faculty',
      'courseLink.courseMaster.coursetypemaster',
    ]);

    if ($request->filled('department_id')) {
      $query->whereHas('subject', function ($q) use ($request) {
        $q->where('department_id', $request->department_id);
      });
    }

    if ($request->filled('campus_id')) {
      $query->whereHas('subject', function ($q) use ($request) {
        $q->where('campus_id', $request->campus_id);
      });
    }

    if ($request->filled('semester_id')) {
      $query->where('semester_id', $request->semester_id);
    }

    if ($request->filled('academic_year')) {
      $query->whereHas('courseLink.courseMaster', function ($q) use ($request) {
        $q->where('academic_year', $request->academic_year);
      });
    }

    $syllabi = $query->get();

    // Enrich with completion, feedback, attendance stats
    foreach ($syllabi as $syl) {
      $totalSubunits = $syl->syllabusunits->count();
      $completedSubunits = $syl->syllabusunits->where('is_completed', 1)->count();
      $syl->total_subunits = $totalSubunits;
      $syl->completed_subunits = $completedSubunits;
      $syl->completion_percent = $totalSubunits > 0 ? round(($completedSubunits / $totalSubunits) * 100, 1) : 0;

      // Faculty name from timetable
      $syl->faculty_name = $syl->timetable && $syl->timetable->faculty
        ? $syl->timetable->faculty->FIRST_NAME . ' ' . $syl->timetable->faculty->LAST_NAME
        : '-';

      // Course info from ProgramCourseMaster
      $cm = $syl->courseLink && $syl->courseLink->courseMaster ? $syl->courseLink->courseMaster : null;
      $syl->course_code = $cm ? $cm->course_code : '-';
      $syl->course_title_pcm = $cm ? $cm->course_title : '-';
      $syl->course_type_name = $cm && $cm->coursetypemaster ? $cm->coursetypemaster->title : '-';
      $syl->academic_year = $cm ? $cm->academic_year : '-';
      $syl->course_master_id = $cm ? $cm->id : null;

      // Attendance stats (via routine → student_attendances)
      if ($syl->timetable) {
        $routineId = $syl->timetable->id;
        $courseId = $syl->timetable->subject_course_id;
        $syl->total_classes = StudentAttendance::where('routine_id', $routineId)
          ->select('attendance_date', 'hour_id')->distinct()->count();
        $totalAttRecords = StudentAttendance::where('routine_id', $routineId)->count();
        $presentRecords = StudentAttendance::where('routine_id', $routineId)->where('status', 'present')->count();
        $syl->avg_attendance_percent = $totalAttRecords > 0 ? round(($presentRecords / $totalAttRecords) * 100, 1) : 0;
      } else {
        $syl->total_classes = 0;
        $syl->avg_attendance_percent = 0;
      }

      // Feedback stats per syllabus
      $subunitIds = $syl->syllabusunits->pluck('id');
      $syl->feedback_count = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->count();
      $syl->avg_rating = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->avg('rating');
    }

    $selectedCampus = $request->campus_id;
    $selectedDepartment = $request->department_id;
    $selectedSemester = $request->semester_id;
    $selectedAcademicYear = $request->academic_year;

    return view('principal.courses.index', compact(
      'syllabi',
      'campuses',
      'departments',
      'semesters',
      'academicYears',
      'selectedCampus',
      'selectedDepartment',
      'selectedSemester',
      'selectedAcademicYear'
    ));
  }

  /**
   * Course detail - analytical view of a single course/syllabus.
   */
  public function courseDetail($id)
  {
    $syllabus = SubjectHasSyllabus::with([
      'subject',
      'batchmaster',
      'semestermaster',
      'syllabusunits.csoSubunit',
      'timetable.faculty',
      'courseLink.courseMaster.coursetypemaster',
      'courseLink.courseMaster.semestermaster',
    ])->findOrFail($id);

    // Course info
    $cm = $syllabus->courseLink && $syllabus->courseLink->courseMaster ? $syllabus->courseLink->courseMaster : null;
    $faculty = $syllabus->timetable && $syllabus->timetable->faculty ? $syllabus->timetable->faculty : null;

    // Subunit completion details
    $subunits = $syllabus->syllabusunits;
    $totalSubunits = $subunits->count();
    $completedSubunits = $subunits->where('is_completed', 1)->count();
    $completionPercent = $totalSubunits > 0 ? round(($completedSubunits / $totalSubunits) * 100, 1) : 0;

    // Attendance data
    $attendanceData = collect();
    $attendanceByDate = collect();
    $studentAttendanceSummary = collect();
    $allAttendance = collect();
    $totalClassesTaken = 0;

    if ($syllabus->timetable) {
      $routineId = $syllabus->timetable->id;

      // Classes taken by date
      $attendanceByDate = StudentAttendance::where('routine_id', $routineId)
        ->select('attendance_date', DB::raw('count(distinct student_id) as students_present'))
        ->where('status', 'present')
        ->groupBy('attendance_date')
        ->orderBy('attendance_date')
        ->get();

      // Total distinct class dates
      $totalClassesTaken = StudentAttendance::where('routine_id', $routineId)
        ->select('attendance_date')->distinct()->count();

      // Student-wise attendance summary
      $studentAttendanceSummary = StudentAttendance::where('routine_id', $routineId)
        ->with('student:id,first_name,last_name,roll_no')
        ->select(
          'student_id',
          DB::raw('count(*) as total'),
          DB::raw("sum(case when status = 'present' then 1 else 0 end) as present"),
          DB::raw("sum(case when status = 'absent' then 1 else 0 end) as absent")
        )
        ->groupBy('student_id')
        ->get()
        ->map(function ($row) {
          $row->percentage = $row->total > 0 ? round(($row->present / $row->total) * 100, 1) : 0;
          return $row;
        })
        ->sortBy('percentage');

      // All individual attendance records
      $allAttendance = StudentAttendance::where('routine_id', $routineId)
        ->with('student:id,first_name,last_name,roll_no')
        ->orderBy('attendance_date', 'desc')
        ->orderBy('student_id')
        ->get();
    }

    // Feedback per subunit (summary + individual student feedback)
    $subunitFeedback = [];
    $subunitFeedbackDetails = [];
    foreach ($subunits as $su) {
      $fb = SubUnitStudentFeedback::where('syllabus_subunit_id', $su->id);
      $subunitFeedback[$su->id] = [
        'count' => $fb->count(),
        'avg_rating' => $fb->avg('rating'),
      ];
      $subunitFeedbackDetails[$su->id] = SubUnitStudentFeedback::where('syllabus_subunit_id', $su->id)
        ->with('student:id,first_name,last_name,roll_no')
        ->orderBy('created_at', 'desc')
        ->get();
    }

    // Overall feedback
    $subunitIds = $subunits->pluck('id');
    $totalFeedback = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->count();
    $avgRating = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->avg('rating');

    return view('principal.courses.detail', compact(
      'syllabus',
      'cm',
      'faculty',
      'subunits',
      'totalSubunits',
      'completedSubunits',
      'completionPercent',
      'attendanceByDate',
      'totalClassesTaken',
      'studentAttendanceSummary',
      'allAttendance',
      'subunitFeedback',
      'subunitFeedbackDetails',
      'totalFeedback',
      'avgRating'
    ));
  }

  /**
   * Subject Syllabus - show all subjects with their syllabus organized by year/semester.
   */
  public function subjectSyllabus(Request $request)
  {
    $campuses = Campus::all();
    $semesters = Semester::orderBy('id')->get();
    $batches = BatchMaster::orderBy('batch_name', 'desc')->get();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    $query = Subject::withCount('syllabus')
      ->with(['campusmaster']);

    if ($request->filled('campus_id')) {
      $query->where('campus_id', $request->campus_id);
    }

    $subjects = $query->orderBy('title')->get();

    // For each subject, get syllabus grouped by batch (year) and semester
    foreach ($subjects as $subject) {
      $syllQuery = SubjectHasSyllabus::where('subject_id', $subject->id)
        ->with([
          'batchmaster',
          'semestermaster',
          'syllabusunits.csoSubunit',
          'courseLink.courseMaster.coursetypemaster',
          'timetable.faculty',
        ]);

      if ($request->filled('batch_id')) {
        $syllQuery->where('batch_id', $request->batch_id);
      }

      if ($request->filled('semester_id')) {
        $syllQuery->where('semester_id', $request->semester_id);
      }

      $syllabi = $syllQuery->get();

      // Enrich with completion stats
      foreach ($syllabi as $syl) {
        $total = $syl->syllabusunits->count();
        $completed = $syl->syllabusunits->where('is_completed', 1)->count();
        $syl->total_subunits = $total;
        $syl->completed_subunits = $completed;
        $syl->completion_percent = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $cm = $syl->courseLink && $syl->courseLink->courseMaster ? $syl->courseLink->courseMaster : null;
        $syl->course_code = $cm ? $cm->course_code : '-';
        $syl->course_title_pcm = $cm ? $cm->course_title : '-';
        $syl->course_type_name = $cm && $cm->coursetypemaster ? $cm->coursetypemaster->title : '-';
        $syl->faculty_name = $syl->timetable && $syl->timetable->faculty
          ? $syl->timetable->faculty->FIRST_NAME . ' ' . $syl->timetable->faculty->LAST_NAME
          : '-';
      }

      // Group syllabi by batch (year)
      $subject->grouped_syllabi = $syllabi->groupBy(function ($syl) {
        return $syl->batchmaster ? $syl->batchmaster->batch_name : 'Unknown';
      });
      $subject->all_syllabi = $syllabi;
    }

    $selectedCampus = $request->campus_id;
    $selectedBatch = $request->batch_id;
    $selectedSemester = $request->semester_id;

    return view('principal.syllabus.index', compact(
      'subjects',
      'campuses',
      'semesters',
      'batches',
      'selectedCampus',
      'selectedBatch',
      'selectedSemester'
    ));
  }

  /**
   * Subject Syllabus Detail - show a single subject's full syllabus with CSO/subunits.
   */
  public function subjectSyllabusDetail($id, Request $request)
  {
    $subject = Subject::with('campusmaster')->findOrFail($id);
    $semesters = Semester::orderBy('id')->get();
    $batches = BatchMaster::orderBy('batch_name', 'desc')->get();

    $syllQuery = SubjectHasSyllabus::where('subject_id', $id)
      ->with([
        'batchmaster',
        'semestermaster',
        'syllabusunits.csoSubunit.taxomonylevel',
        'courseLink.courseMaster.coursetypemaster',
        'timetable.faculty',
      ]);

    if ($request->filled('batch_id')) {
      $syllQuery->where('batch_id', $request->batch_id);
    }

    if ($request->filled('semester_id')) {
      $syllQuery->where('semester_id', $request->semester_id);
    }

    $syllabi = $syllQuery->get();

    // Enrich each syllabus
    foreach ($syllabi as $syl) {
      $total = $syl->syllabusunits->count();
      $completed = $syl->syllabusunits->where('is_completed', 1)->count();
      $syl->total_subunits = $total;
      $syl->completed_subunits = $completed;
      $syl->completion_percent = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

      $cm = $syl->courseLink && $syl->courseLink->courseMaster ? $syl->courseLink->courseMaster : null;
      $syl->course_code = $cm ? $cm->course_code : '-';
      $syl->course_title_pcm = $cm ? $cm->course_title : '-';
      $syl->course_type_name = $cm && $cm->coursetypemaster ? $cm->coursetypemaster->title : '-';
      $syl->academic_year_val = $cm ? $cm->academic_year : '-';
      $syl->faculty_name = $syl->timetable && $syl->timetable->faculty
        ? $syl->timetable->faculty->FIRST_NAME . ' ' . $syl->timetable->faculty->LAST_NAME
        : '-';

      // Per-subunit feedback
      $subunitIds = $syl->syllabusunits->pluck('id');
      $syl->feedback_count = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->count();
      $syl->avg_rating = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->avg('rating');
    }

    // Group by batch then semester
    $groupedByBatch = $syllabi->groupBy(function ($syl) {
      return $syl->batchmaster ? $syl->batchmaster->batch_name : 'Unknown';
    })->sortKeysDesc();

    // Summary stats
    $totalCourses = $syllabi->count();
    $totalSubunits = $syllabi->sum('total_subunits');
    $completedSubunits = $syllabi->sum('completed_subunits');
    $avgCompletion = $totalCourses > 0 ? round($syllabi->avg('completion_percent'), 1) : 0;

    $selectedBatch = $request->batch_id;
    $selectedSemester = $request->semester_id;

    return view('principal.syllabus.detail', compact(
      'subject',
      'syllabi',
      'groupedByBatch',
      'semesters',
      'batches',
      'totalCourses',
      'totalSubunits',
      'completedSubunits',
      'avgCompletion',
      'selectedBatch',
      'selectedSemester'
    ));
  }

  /**
   * Faculty work diary - view a faculty member's work diary entries.
   */
  public function facultyWorkDiary($id, Request $request)
  {
    $faculty = Faculty::findOrFail($id);
    $faculty->department_info = DepartmentMaster::find($faculty->DEPARTMENT);

    $selectedMonth = $request->month ?? now()->format('Y-m');
    $startDate = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
    $endDate = $startDate->copy()->endOfMonth();

    $entries = WorkDiary::where('faculty_id', $id)
      ->whereBetween('date', [$startDate, $endDate])
      ->orderBy('date')
      ->orderBy('hour')
      ->get();

    // Group by date
    $entriesByDate = $entries->groupBy(fn($e) => $e->date->format('Y-m-d'));

    // Monthly summary
    $totalEntries = $entries->count();
    $regularClasses = $entries->where('class_type', 'regular')->count();
    $extraClasses = $entries->where('class_type', 'extra')->count();
    $substitutionClasses = $entries->where('class_type', 'substitution')->count();
    $completedEntries = $entries->where('status', 'completed')->count();
    $approvedEntries = $entries->where('status', 'approved')->count();
    $pendingEntries = $entries->where('status', 'pending')->count();

    return view('principal.faculty.work-diary', compact(
      'faculty',
      'entriesByDate',
      'selectedMonth',
      'totalEntries',
      'regularClasses',
      'extraClasses',
      'substitutionClasses',
      'completedEntries',
      'approvedEntries',
      'pendingEntries'
    ));
  }

  /**
   * Faculty timetable - view a faculty member's weekly timetable.
   */
  public function facultyTimetable($id)
  {
    $faculty = Faculty::findOrFail($id);
    $faculty->department_info = DepartmentMaster::find($faculty->DEPARTMENT);

    $timetable = SubjectHasRoutine::with([
      'weekdaymaster',
      'hourmaster',
      'lecturehallmaster',
      'subjectCourse.courseMaster.semestermaster',
      'subjectCourse.courseMaster.coursetypemaster',
      'syllabus.semestermaster',
      'batch'
    ])->where('faculty_id', $id)->get();

    $weekdays = Weekday::all();
    $hours = HourMaster::all();
    $timetableGrid = [];
    foreach ($weekdays as $day) {
      $timetableGrid[$day->id] = [
        'day' => $day->title,
        'slots' => []
      ];
      foreach ($hours as $hour) {
        $slot = $timetable->first(function ($r) use ($day, $hour) {
          return $r->weekday_id == $day->id && $r->hour_id == $hour->id;
        });
        $timetableGrid[$day->id]['slots'][$hour->id] = [
          'hour' => $hour->title,
          'routine' => $slot
        ];
      }
    }

    // Build assigned courses list
    /*
    $assignedCourses = $timetable->map(function ($r) {
      $cm = $r->subjectCourse && $r->subjectCourse->courseMaster ? $r->subjectCourse->courseMaster : null;
      $semester = $r->syllabus && $r->syllabus->semestermaster ? $r->syllabus->semestermaster->title : ($cm && $cm->semestermaster ? $cm->semestermaster->title : '-');
      return [
        'course_code' => $cm ? $cm->course_code : '-',
        'course_title' => $cm ? $cm->course_title : '-',
        'course_type' => $cm && $cm->coursetypemaster ? $cm->coursetypemaster->title : '-',
        'semester' => $semester,
        'academic_year' => $cm ? $cm->academic_year : '-',
        'batch' => $r->batch ? $r->batch->batch_name : '-',
      ];
    })->unique('course_code')->values();
    */

    return view('principal.faculty.timetable', compact(
      'faculty',
      'timetableGrid',
      'weekdays',
      'hours',

    ));
  }

  // ── Vice-Principal Management ──

  /**
   * List all vice-principal accounts.
   */
  public function vpIndex()
  {
    $vpUsers = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'vice-principal');
    })
      ->with(['campuspermission.campus'])
      ->get();

    $campuses = Campus::all();

    return view('principal.vp-management.index', compact('vpUsers', 'campuses'));
  }

  /**
   * Store a new vice-principal account.
   */
  public function vpStore(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users,email',
      'password' => 'required|string|min:6',
      'campus_id' => 'required|exists:campuses,id',
    ]);

    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'status' => 'ACTIVE',
      'otp_verification' => 1,
    ]);

    UserHasRole::create([
      'user_id' => $user->id,
      'role_name' => 'vice-principal',
    ]);

    UserCampusSetting::create([
      'user_id' => $user->id,
      'campus_id' => $request->campus_id,
    ]);

    return redirect()->route('principal.vp.index')->with('success', 'Vice-Principal account created successfully.');
  }

  /**
   * Update vice-principal campus assignment.
   */
  public function vpUpdate(Request $request, $id)
  {
    $vpUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'vice-principal');
    })->findOrFail($id);

    $request->validate([
      'campus_id' => 'required|exists:campuses,id',
    ]);

    UserCampusSetting::updateOrCreate(
      ['user_id' => $vpUser->id],
      ['campus_id' => $request->campus_id]
    );

    return redirect()->route('principal.vp.index')->with('success', 'Vice-Principal campus updated successfully.');
  }

  /**
   * Toggle VP active/inactive status.
   */
  public function vpToggleStatus($id)
  {
    $vpUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'vice-principal');
    })->findOrFail($id);

    $vpUser->status = $vpUser->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $vpUser->save();

    return redirect()->route('principal.vp.index')->with('success', 'Vice-Principal status updated.');
  }

  /**
   * Delete a VP account.
   */
  public function vpDestroy($id)
  {
    $vpUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'vice-principal');
    })->findOrFail($id);

    UserCampusSetting::where('user_id', $vpUser->id)->delete();
    UserHasRole::where('user_id', $vpUser->id)->delete();
    $vpUser->delete();

    return redirect()->route('principal.vp.index')->with('success', 'Vice-Principal account deleted.');
  }

  // ── Leave Management ──

  /**
   * List all pending leave applications for approval.
   */
  public function leaves(Request $request)
  {
    $campuses = Campus::all();
    $query = FacultyLeaveApplication::with(['faculty', 'leaveMaster'])
      ->where('forwarded_to', 'Principal')
      ->where('dept_action', 'forwarded')
      ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
      ->orderBy('created_at', 'desc');

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $deptIds = DepartmentMaster::where('campus_id', $vpCampusId)->pluck('id');
        $facultyIds = Faculty::whereIn('DEPARTMENT', $deptIds)->pluck('id');
        $query->whereIn('faculty_id', $facultyIds);
        $request->merge(['campus_id' => $vpCampusId]); // Set for view
      }
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    // For principals, allow manual campus filtering
    if (!$isVicePrincipal && $request->filled('campus_id')) {
      $deptIds = DepartmentMaster::where('campus_id', $request->campus_id)->pluck('id');
      $facultyIds = Faculty::whereIn('DEPARTMENT', $deptIds)->pluck('id');
      $query->whereIn('faculty_id', $facultyIds);
    }

    $leaveApplications = $query->get();

    // Enrich with department info
    foreach ($leaveApplications as $leave) {
      if ($leave->faculty) {
        $leave->faculty->department_info = DepartmentMaster::find($leave->faculty->DEPARTMENT);
      }
    }

    $selectedCampus = $request->campus_id;
    $selectedStatus = $request->status;

    return view('principal.leaves.index', compact(
      'leaveApplications',
      'campuses',
      'selectedCampus',
      'selectedStatus'
    ));
  }

  /**
   * Approve or reject a leave application with admin note.
   */
  public function leaveAction(Request $request, $id)
  {
    $request->validate([
      'action' => 'required|in:approved,rejected',
      'admin_remarks' => 'nullable|string|max:500',
    ]);

    $leave = FacultyLeaveApplication::where('status', 'pending')->findOrFail($id);
    $leave->status = $request->action;
    $leave->admin_remarks = $request->admin_remarks;
    $leave->approved_by = auth()->id();
    $leave->approved_at = now();
    $leave->save();

    $actionLabel = $request->action === 'approved' ? 'approved' : 'rejected';
    return redirect()->back()->with('success', "Leave application {$actionLabel} successfully.");
  }

  // ── Work Diary Approval ──

  /**
   * Approve a work diary entry.
   */
  public function approveWorkDiary(Request $request, $id)
  {
    $entry = WorkDiary::findOrFail($id);
    $entry->status = 'approved';
    $entry->save();

    return redirect()->back()->with('success', 'Work diary entry approved.');
  }

  /**
   * Bulk approve work diary entries for a faculty member in a month.
   */
  public function bulkApproveWorkDiary(Request $request)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'month' => 'required|date_format:Y-m',
    ]);

    $startDate = \Carbon\Carbon::parse($request->month . '-01')->startOfMonth();
    $endDate = $startDate->copy()->endOfMonth();

    WorkDiary::where('faculty_id', $request->faculty_id)
      ->whereBetween('date', [$startDate, $endDate])
      ->where('status', 'pending')
      ->update(['status' => 'approved']);

    return redirect()->back()->with('success', 'All pending work diary entries approved for this month.');
  }

  /**
   * Student Fee Details - view fee structures and payment status for all students.
   */
  public function studentFees(Request $request)
  {
    $campuses = Campus::all();
    $batches = BatchMaster::orderBy('batch_name')->get();
    $programs = ProgramGroup::with('programInfo')->get();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    $query = StudentMaster::with([
      'batchmaster',
      'campusmaster',
      'programgroup.programInfo',
      'stdfeestructure.feeHeads',
      'stdfeestructure.programspivot',
      'feepayment',
    ]);

    if ($request->filled('campus_id')) {
      $query->where('campus_id', $request->campus_id);
    }
    if ($request->filled('batch_id')) {
      $query->where('batch', $request->batch_id);
    }
    if ($request->filled('programme_id')) {
      $query->where('programme', $request->programme_id);
    }
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('roll_no', 'like', "%{$search}%")
          ->orWhere('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%");
      });
    }

    $data = $query->orderBy('first_name')->paginate(30)->withQueryString();

    $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount') ?? 0;

    $students = $data->through(function ($student) use ($lateFeePerDay) {
      $applicableFS = FeesStructure::with('feeHeads')
        ->where('batch_id', $student->batch)
        ->whereHas('programspivot', function ($q) use ($student) {
          $q->where('std_program_id', $student->new_program_id);
        })
        ->whereIn('std_current_year', range(1, $student->current_year))
        ->orderBy('std_current_year')
        ->get();

      $totalFee = 0;
      $totalPaid = 0;
      $totalDue = 0;
      $feeDetails = [];

      foreach ($applicableFS as $fs) {
        $baseAmount = $fs->feeHeads->sum('amount');
        $payment = $student->feepayment
          ->where('fee_structure_id', $fs->id)
          ->where('status', 'success')
          ->first();

        $lateDays = 0;
        $lateFee = 0;
        if (!$payment && $fs->due_date) {
          $dueDate = Carbon::parse($fs->due_date);
          $today = Carbon::today();
          if ($today->gt($dueDate)) {
            $lateDays = $dueDate->diffInDays($today);
            $lateFee = $lateDays * $lateFeePerDay;
          }
        }

        $totalFee += $baseAmount;
        $totalPaid += $payment ? $payment->captured_amount ?? $payment->amount : 0;
        $totalDue += $payment ? 0 : ($baseAmount + $lateFee);

        $feeDetails[] = [
          'quarter' => $fs->quarter_title,
          'year' => $fs->std_current_year,
          'amount' => $baseAmount,
          'late_days' => $lateDays,
          'late_fee' => $lateFee,
          'payable' => $baseAmount + $lateFee,
          'paid' => $payment ? true : false,
          'paid_amount' => $payment->captured_amount ?? $payment->amount ?? 0,
          'status' => $payment ? 'Paid' : ($lateFee > 0 ? 'Late' : 'Due'),
          'due_date' => $fs->due_date,
        ];
      }

      return [
        'student' => $student,
        'total_fee' => $totalFee,
        'total_paid' => $totalPaid,
        'total_due' => $totalDue,
        'fee_details' => $feeDetails,
      ];
    });

    return view('principal.fees.index', compact(
      'campuses',
      'batches',
      'programs',
      'students',
      'data'
    ));
  }

  /**
   * Fee Defaulters List - students who have missed due dates.
   */
  public function feeDefaulters(Request $request)
  {
    $campuses = Campus::all();
    $batches = BatchMaster::orderBy('batch_name')->get();
    $programs = ProgramGroup::with('programInfo')->get();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    $query = StudentMaster::with([
      'batchmaster',
      'campusmaster',
      'programgroup.programInfo',
      'stdfeestructure',
      'stdfeestructure.programspivot',
      'feepayment',
    ]);

    if ($request->filled('campus_id')) {
      $query->where('campus_id', $request->campus_id);
    }
    if ($request->filled('batch_id')) {
      $query->where('batch', $request->batch_id);
    }
    if ($request->filled('programme_id')) {
      $query->where('programme', $request->programme_id);
    }

    $students = $query->get();

    $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount') ?? 0;

    $defaulters = [];

    foreach ($students as $student) {
      $applicableFS = FeesStructure::where('batch_id', $student->batch)
        ->whereHas('programspivot', function ($q) use ($student) {
          $q->where('std_program_id', $student->new_program_id);
        })
        ->whereIn('std_current_year', range(1, $student->current_year))
        ->where('is_payable', 1)
        ->get();

      foreach ($applicableFS as $fs) {
        $payment = $student->feepayment
          ->where('fee_structure_id', $fs->id)
          ->where('status', 'success')
          ->first();

        if (!$payment && $fs->due_date) {
          $dueDate = Carbon::parse($fs->due_date);
          $today = Carbon::today();

          if ($today->gt($dueDate)) {
            $lateDays = $dueDate->diffInDays($today);
            $lateFee = $lateDays * $lateFeePerDay;
            $baseAmount = $fs->feeHeads ? $fs->feeHeads->sum('amount') : 0;

            $defaulters[] = [
              'student' => $student,
              'fee_structure' => $fs,
              'base_amount' => $baseAmount,
              'late_days' => $lateDays,
              'late_fee' => $lateFee,
              'total_due' => $baseAmount + $lateFee,
              'due_date' => $fs->due_date,
            ];
          }
        }
      }
    }

    // Sort by late days descending
    usort($defaulters, fn($a, $b) => $b['late_days'] - $a['late_days']);

    return view('principal.fees.defaulters', compact(
      'campuses',
      'batches',
      'programs',
      'defaulters'
    ));
  }

  /**
   * Monthly Work Diary Overview - all faculties with class counts and submission status.
   */
  public function workDiaryOverview(Request $request)
  {
    $campuses = Campus::all();
    $departments = DepartmentMaster::orderBy('name')->get();
    $selectedMonth = $request->month ?? now()->format('Y-m');
    $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth();
    $endDate = $startDate->copy()->endOfMonth();

    // Check if user is vice-principal
    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    // For vice-principals, automatically filter by their assigned campus
    if ($isVicePrincipal) {
      $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId) {
        $request->merge(['campus_id' => $vpCampusId]);
      }
    }

    // Fetch active faculties
    $facultyQuery = Faculty::where(function ($q) {
      $q->whereNull('IS_LEFT')->orWhere('IS_LEFT', 0);
    });

    if ($request->filled('campus_id')) {
      $facultyQuery->where('CAMPUS_ID', $request->campus_id);
    }
    if ($request->filled('department_id')) {
      $facultyQuery->where('DEPARTMENT', $request->department_id);
    }

    $faculties = $facultyQuery->orderBy('FIRST_NAME')->get();
    $facultyIds = $faculties->pluck('id');

    // Work diary entries for the month grouped by faculty
    $diaryEntries = WorkDiary::whereIn('faculty_id', $facultyIds)
      ->whereBetween('date', [$startDate, $endDate])
      ->get()
      ->groupBy('faculty_id');

    // Teaching class counts: distinct date+hour per faculty
    $teachingCounts = StudentAttendance::whereIn('faculty_id', $facultyIds)
      ->whereBetween('attendance_date', [$startDate, $endDate])
      ->select('faculty_id', DB::raw('COUNT(DISTINCT CONCAT(attendance_date, "-", hour_id)) as count'))
      ->groupBy('faculty_id')
      ->pluck('count', 'faculty_id');

    // Remedial class counts
    $extraCounts = ExtraClassAttendance::whereIn('faculty_id', $facultyIds)
      ->whereBetween('attendance_date', [$startDate, $endDate])
      ->select('faculty_id', DB::raw('COUNT(DISTINCT CONCAT(attendance_date, "-", hour_id)) as count'))
      ->groupBy('faculty_id')
      ->pluck('count', 'faculty_id');

    // Substitution class counts (as substitute)
    $substitutionCounts = FacultySubstitution::whereIn('substitute_faculty_id', $facultyIds)
      ->whereBetween('substitution_date', [$startDate, $endDate])
      ->select('substitute_faculty_id', DB::raw('count(*) as count'))
      ->groupBy('substitute_faculty_id')
      ->pluck('count', 'substitute_faculty_id');

    // Department lookup
    $deptMap = DepartmentMaster::pluck('name', 'id');

    // Build faculty data
    $submitted = [];
    $notSubmitted = [];

    foreach ($faculties as $faculty) {
      $entries = $diaryEntries->get($faculty->id, collect());
      $totalEntries = $entries->count();
      $approved = $entries->where('status', 'approved')->count();
      $pending = $entries->where('status', 'pending')->count();
      $completed = $entries->where('status', 'completed')->count();

      $row = [
        'faculty' => $faculty,
        'department' => $deptMap[$faculty->DEPARTMENT] ?? '-',
        'teaching_classes' => $teachingCounts[$faculty->id] ?? 0,
        'extra_classes' => $extraCounts[$faculty->id] ?? 0,
        'substitution_classes' => $substitutionCounts[$faculty->id] ?? 0,
        'total_entries' => $totalEntries,
        'approved' => $approved,
        'pending' => $pending,
        'completed' => $completed,
      ];

      if ($totalEntries > 0) {
        $submitted[] = $row;
      } else {
        $notSubmitted[] = $row;
      }
    }

    $totalTeaching = $teachingCounts->sum();
    $totalExtra = $extraCounts->sum();
    $totalSubstitution = $substitutionCounts->sum();

    return view('principal.work-diary.index', compact(
      'campuses',
      'departments',
      'selectedMonth',
      'submitted',
      'notSubmitted',
      'totalTeaching',
      'totalExtra',
      'totalSubstitution'
    ));
  }
}
