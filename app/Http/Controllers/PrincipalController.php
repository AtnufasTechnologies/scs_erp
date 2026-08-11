<?php

namespace App\Http\Controllers;

use App\Models\AcademicDepartment;
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
use App\Models\SubjectCourseMaster;
use App\Models\CoHasCso;
use App\Models\SubjectFacultyMaster;
use App\Models\SyllabusManager;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Result;
use App\Models\FeesStructure;
use App\Models\StudentPayment;
use App\Models\LateFee;
use App\Models\StudentLateFeeExemption;
use App\Models\ExtraClassAttendance;
use App\Models\FacultySubstitution;
use App\Models\CiaMark;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\SpecializationMaster;
use App\Models\ShiftMaster;
use App\Models\StudentProgram;
use App\Models\SubjectHasStudentProgam;
use App\Models\TeachingAssignment;
use App\Models\EcEvent;
use App\Models\EcProgram;
use App\Models\EcFacultyDuty;
use App\Models\EcFundTransaction;
use App\Models\EcSponsor;
use App\Services\StudentTimetableService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

    // ── Programs & Curriculum overview ─────────────────────────────────────
    $studentProgramsQuery = StudentProgram::with([
      'campusmaster:id,name',
      'shiftmaster:slug,title',
      'programtypemaster:id,name',
      'combomap.combo1:id,title',
      'combomap.combo2:id,title',
    ])->orderBy('campus_id')->orderBy('name');
    if ($isVicePrincipal && $vpCampusId) {
      $studentProgramsQuery->where('campus_id', $vpCampusId);
    }
    $studentPrograms = $studentProgramsQuery->get();

    // Combinations indexed by student_program_id
    $combinationsByProgramId = SubjectHasStudentProgam::select('id', 'student_program_id', 'batch_id')
      ->get()->groupBy('student_program_id');

    // Curriculum semester coverage per combination id
    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
    $curriculumCoverage = DB::table($curriculumTable)
      ->whereNull('deleted_at')
      ->selectRaw('program_combo_refid, COUNT(DISTINCT semester) as covered')
      ->groupBy('program_combo_refid')
      ->pluck('covered', 'program_combo_refid');

    $programsOverview = $studentPrograms->map(function ($program) use ($combinationsByProgramId, $curriculumCoverage) {
      $combos = $combinationsByProgramId->get($program->id, collect());
      $totalSems = (int) $program->semester_count;
      $maxCovered = 0;
      $hasCombos = $combos->isNotEmpty();
      foreach ($combos as $combo) {
        $covered = (int) ($curriculumCoverage[$combo->id] ?? 0);
        if ($covered > $maxCovered) $maxCovered = $covered;
      }
      $program->curriculum_covered = $maxCovered;
      $program->curriculum_total   = $totalSems;
      $program->has_combos         = $hasCombos;
      return $program;
    });

    // ── Subjects overview (shifts + specializations) ───────────────────────
    $subjectsQuery = Subject::with('campusmaster:id,name');
    if ($isVicePrincipal && $vpCampusId) {
      $subjectsQuery->where('campus_id', $vpCampusId);
    }
    $subjectsList = $subjectsQuery->orderBy('campus_id')->orderBy('title')->get();

    $allSpecializations = SpecializationMaster::where('is_active', 1)
      ->get(['id', 'subject_id', 'name'])
      ->groupBy('subject_id');

    $allShifts = ShiftMaster::orderBy('sort_order')->get(['id', 'title'])->keyBy('id');

    $subjectsOverview = $subjectsList->map(function ($subject) use ($allSpecializations, $allShifts) {
      $shiftIds = $subject->shift_ids;
      if (is_string($shiftIds)) {
        $shiftIds = json_decode($shiftIds, true) ?? [];
      }
      $shiftIds = array_filter((array) $shiftIds, fn($id) => $id > 0);

      $shiftNames = [];
      if ($subject->has_shift_delivery == 1 && !empty($shiftIds)) {
        foreach ($shiftIds as $sid) {
          if (isset($allShifts[$sid])) {
            $shiftNames[] = $allShifts[$sid]->title;
          }
        }
      }

      $subject->applicable_shifts     = $shiftNames;
      $subject->uses_shifts           = $subject->has_shift_delivery == 1;
      $subject->specializations_list  = $allSpecializations->get($subject->id, collect());
      return $subject;
    });

    $departmentsQuery = DepartmentMaster::with('campusmaster:id,name')->orderBy('campus_id')->orderBy('name');
    if ($isVicePrincipal && $vpCampusId) {
      $departmentsQuery->where('campus_id', $vpCampusId);
    }
    $departments = $departmentsQuery->get(['id', 'name', 'campus_id']);

    $departmentIds = $departments->pluck('id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->values();
    $facultyByDepartment = collect();
    $shiftsByDepartment = collect();

    if ($departmentIds->isNotEmpty()) {
      $departmentFacultyLinks = SubjectFacultyMaster::with('faculty.hrDesignation:id,name')
        ->whereIn('subject_id', $departmentIds)
        ->get(['id', 'subject_id', 'faculty_id', 'access_id']);

      $facultyByDepartment = $departmentFacultyLinks->groupBy('subject_id')->map(function ($links) {
        return collect($links)->map(function ($link) {
          return $link->faculty;
        })->filter(function ($faculty) {
          return $faculty && ((int) ($faculty->IS_LEFT ?? 0) === 0);
        })->values();
      });

      $departmentSubjects = $departments->keyBy('id');

      $shiftsByDepartment = $departmentSubjects->map(function ($subject) use ($allShifts) {
        $shiftIds = collect();

        $subjectShiftIds = $subject->shift_ids;
        if (is_string($subjectShiftIds)) {
          $subjectShiftIds = json_decode($subjectShiftIds, true) ?? [];
        }

        $shiftIds = $shiftIds->merge(array_filter((array) $subjectShiftIds, fn($id) => (int) $id > 0));

        $shiftTitles = $shiftIds->unique()->values()->map(function ($shiftId) use ($allShifts) {
          return (string) (optional($allShifts->get((int) $shiftId))->title ?? 'Common');
        })->filter()->values();

        return $shiftTitles->isNotEmpty() ? $shiftTitles : collect(['Common']);
      });
    }

    $departmentSummaries = $departments->map(function ($department) use ($facultyByDepartment, $shiftsByDepartment) {
      $departmentFaculties = collect($facultyByDepartment->get($department->id, collect()));

      $incharge = $departmentFaculties->first(function ($faculty) {
        $searchText = strtolower(trim((string) ($faculty->designation ?? '') . ' ' . (string) (optional($faculty->hrDesignation)->name ?? '') . ' ' . (string) ($faculty->responsibility ?? '')));
        return str_contains($searchText, 'incharge') || str_contains($searchText, 'hod') || str_contains($searchText, 'head');
      }) ?: $departmentFaculties->first();

      $facultyList = $departmentFaculties->map(function ($faculty) {
        $nameParts = array_filter([
          trim((string) ($faculty->FIRST_NAME ?? '')),
          trim((string) ($faculty->MIDDLE_NAME ?? '')),
          trim((string) ($faculty->LAST_NAME ?? '')),
        ]);

        return [
          'name' => trim(implode(' ', $nameParts)) ?: 'Unnamed',
          'code' => (string) ($faculty->USER_CODE ?? ''),
          'designation' => (string) (optional($faculty->hrDesignation)->name ?? ($faculty->designation ?? '')),
        ];
      })->values();

      return (object) [
        'id' => (int) $department->id,
        'name' => (string) ($department->title ?? '-'),
        'code' => (string) ($department->code ?? ''),
        'campus_name' => (string) (optional($department->campusmaster)->name ?? '-'),
        'incharge_name' => $incharge ? trim((string) implode(' ', array_filter([
          $incharge->FIRST_NAME ?? '',
          $incharge->MIDDLE_NAME ?? '',
          $incharge->LAST_NAME ?? '',
        ]))) : 'Not assigned',
        'incharge_designation' => $incharge ? (string) (optional($incharge->hrDesignation)->name ?? ($incharge->designation ?? '')) : '',
        'applicable_shifts' => collect($shiftsByDepartment->get($department->id, collect()))->values()->all(),
        'faculties' => $facultyList,
      ];
    })->values();

    $hoursByShift = HourMaster::with('shiftmaster:id,title')
      ->where('status', 1)
      ->orderBy('shift_id')
      ->orderBy('hour_no')
      ->get(['id', 'shift_id', 'hour_no', 'name', 'start_time', 'end_time', 'is_teaching', 'status'])
      ->groupBy('shift_id');

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
      'totalPrograms',
      'programsOverview',
      'subjectsOverview',
      'departmentSummaries',
      'hoursByShift',
      'allShifts'
    ));
  }

  /**
   * Principal academic departments listing with shift, incharge, and faculty drilldown.
   */
  public function subjects(Request $request)
  {
    $campuses = Campus::orderBy('name')->get(['id', 'name']);

    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';
    $vpCampusId = null;

    if ($isVicePrincipal) {
      $vpCampusId = (int) UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
    }

    $departmentsQuery = Subject::with('campusmaster:id,name')->orderBy('campus_id')->orderBy('title');
    if ($isVicePrincipal && $vpCampusId) {
      $departmentsQuery->where('campus_id', $vpCampusId);
    }

    $departments = $departmentsQuery->get(['id', 'campus_id', 'code', 'title', 'has_shift_delivery', 'shift_ids']);
    $departmentIds = $departments->pluck('id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->values();

    $allShifts = ShiftMaster::orderBy('sort_order')->get(['id', 'title'])->keyBy('id');

    $facultyByDepartment = collect();
    $shiftsByDepartment = collect();

    if ($departmentIds->isNotEmpty()) {
      $departmentFacultyLinks = SubjectFacultyMaster::with('faculty.hrDesignation:id,name')
        ->whereIn('subject_id', $departmentIds)
        ->get(['id', 'subject_id', 'faculty_id', 'access_id']);

      $facultyByDepartment = $departmentFacultyLinks->groupBy('subject_id')->map(function ($links) {
        return collect($links)->map(function ($link) {
          return $link->faculty;
        })->filter(function ($faculty) {
          return $faculty && ((int) ($faculty->IS_LEFT ?? 0) === 0);
        })->values();
      });

      $shiftsByDepartment = $departments->keyBy('id')->map(function ($subject) use ($allShifts) {
        $shiftIds = collect();
        $subjectShiftIds = $subject->shift_ids;

        if (is_string($subjectShiftIds)) {
          $subjectShiftIds = json_decode($subjectShiftIds, true) ?? [];
        }

        $shiftIds = $shiftIds->merge(array_filter((array) $subjectShiftIds, fn($id) => (int) $id > 0));

        $shiftTitles = $shiftIds->unique()->values()->map(function ($shiftId) use ($allShifts) {
          return (string) (optional($allShifts->get((int) $shiftId))->title ?? 'Common');
        })->filter()->values();

        return $shiftTitles->isNotEmpty() ? $shiftTitles : collect(['Common']);
      });
    }

    $departmentSummaries = $departments->map(function ($department) use ($facultyByDepartment, $shiftsByDepartment) {
      $departmentFaculties = collect($facultyByDepartment->get($department->id, collect()));

      $incharge = $departmentFaculties->first(function ($faculty) {
        $searchText = strtolower(trim((string) ($faculty->designation ?? '') . ' ' . (string) (optional($faculty->hrDesignation)->name ?? '') . ' ' . (string) ($faculty->responsibility ?? '')));
        return str_contains($searchText, 'incharge') || str_contains($searchText, 'hod') || str_contains($searchText, 'head');
      }) ?: $departmentFaculties->first();

      $facultyList = $departmentFaculties->map(function ($faculty) {
        $nameParts = array_filter([
          trim((string) ($faculty->FIRST_NAME ?? '')),
          trim((string) ($faculty->MIDDLE_NAME ?? '')),
          trim((string) ($faculty->LAST_NAME ?? '')),
        ]);

        return [
          'name' => trim(implode(' ', $nameParts)) ?: 'Unnamed',
          'code' => (string) ($faculty->USER_CODE ?? ''),
          'designation' => (string) (optional($faculty->hrDesignation)->name ?? ($faculty->designation ?? '')),
        ];
      })->values();

      return (object) [
        'id' => (int) $department->id,
        'name' => (string) ($department->title ?? '-'),
        'code' => (string) ($department->code ?? ''),
        'campus_name' => (string) (optional($department->campusmaster)->name ?? '-'),
        'incharge_name' => $incharge ? trim((string) implode(' ', array_filter([
          $incharge->FIRST_NAME ?? '',
          $incharge->MIDDLE_NAME ?? '',
          $incharge->LAST_NAME ?? '',
        ]))) : 'Not assigned',
        'incharge_designation' => $incharge ? (string) (optional($incharge->hrDesignation)->name ?? ($incharge->designation ?? '')) : '',
        'applicable_shifts' => collect($shiftsByDepartment->get($department->id, collect()))->values()->all(),
        'faculties' => $facultyList,
      ];
    })->values();

    return view('principal.subjects.index', [
      'campuses' => $campuses,
      'departmentSummaries' => $departmentSummaries,
      'isVicePrincipal' => $isVicePrincipal,
    ]);
  }

  /**
   * Principal view of Event Controller work and progress.
   */
  public function eventControllerWork(Request $request)
  {
    $status = trim((string) $request->query('status', ''));
    $validStatuses = ['draft', 'active', 'completed', 'cancelled'];
    if (!in_array($status, $validStatuses, true)) {
      $status = '';
    }

    $eventsQuery = EcEvent::withCount(['programs', 'facultyDuties', 'sponsors'])
      ->with('creator:id,name')
      ->orderByDesc('start_date')
      ->orderByDesc('id');

    if ($status !== '') {
      $eventsQuery->where('status', $status);
    }

    $events = $eventsQuery->paginate(12)->withQueryString();

    $summary = [
      'total_events' => EcEvent::count(),
      'active_events' => EcEvent::where('status', 'active')->count(),
      'completed_events' => EcEvent::where('status', 'completed')->count(),
      'total_programs' => EcProgram::count(),
      'upcoming_programs' => EcProgram::whereDate('program_date', '>=', today())
        ->where('status', 'upcoming')
        ->count(),
      'total_faculty_duties' => EcFacultyDuty::count(),
      'total_budget' => (float) EcEvent::sum('total_budget'),
      'total_income' => (float) EcFundTransaction::where('type', 'income')->sum('amount'),
      'total_expense' => (float) EcFundTransaction::where('type', 'expense')->sum('amount'),
      'sponsorship_received' => (float) EcSponsor::where('status', 'received')->sum('received_amount'),
    ];

    $recentPrograms = EcProgram::with('event:id,title')
      ->orderByDesc('program_date')
      ->orderByDesc('id')
      ->limit(8)
      ->get(['id', 'event_id', 'name', 'program_date', 'venue', 'status', 'registration_fee']);

    $recentDuties = EcFacultyDuty::with([
      'event:id,title',
      'program:id,name',
      'faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
    ])
      ->orderByDesc('id')
      ->limit(10)
      ->get(['id', 'event_id', 'program_id', 'faculty_id', 'duty_title', 'status']);

    return view('principal.events.index', [
      'events' => $events,
      'summary' => $summary,
      'recentPrograms' => $recentPrograms,
      'recentDuties' => $recentDuties,
      'selectedStatus' => $status,
      'validStatuses' => $validStatuses,
    ]);
  }

  /**
   * Program-wise curriculum overview for principal/vice-principal.
   */
  public function curriculamProgramWise(Request $request)
  {
    $campuses = Campus::orderBy('name')->get(['id', 'name']);
    $batches = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);

    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    $selectedCampusId = (int) $request->query('campus_id', 0);
    $selectedBatchId = (int) $request->query('batch_id', 0);
    $selectedSubjectId = (int) $request->query('subject_id', 0);

    if ($isVicePrincipal) {
      $vpCampusId = (int) UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId > 0) {
        $selectedCampusId = $vpCampusId;
      }
    }

    $combinationQuery = SubjectHasStudentProgam::with([
      'batchmaster:id,batch_name',
      'subjectmaster:id,title,code',
      'studentprograminfo:id,code,name,campus_id,program_type',
      'studentprograminfo.campusmaster:id,name',
      'studentprograminfo.programtypemaster:id,name',
    ])
      ->orderBy('student_program_id')
      ->orderBy('batch_id');

    if ($selectedCampusId > 0) {
      $combinationQuery->where('campus_id', $selectedCampusId);
    }

    if ($selectedBatchId > 0) {
      $combinationQuery->where('batch_id', $selectedBatchId);
    }

    $subjectFilterIds = SubjectHasStudentProgam::query()
      ->when($selectedCampusId > 0, fn($query) => $query->where('campus_id', $selectedCampusId))
      ->when($selectedBatchId > 0, fn($query) => $query->where('batch_id', $selectedBatchId))
      ->pluck('subject_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $subjects = Subject::query()
      ->when($subjectFilterIds->isNotEmpty(), fn($query) => $query->whereIn('id', $subjectFilterIds->all()))
      ->orderBy('title')
      ->get(['id', 'title', 'code']);

    if ($selectedSubjectId > 0) {
      $combinationQuery->where('subject_id', $selectedSubjectId);
    }

    $combinations = $combinationQuery->get([
      'id',
      'student_program_id',
      'subject_id',
      'batch_id',
      'campus_id',
      'program_type',
    ]);

    $combinationIds = $combinations->pluck('id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->values();
    $subjectIds = $combinations->pluck('subject_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();

    $programIds = $combinations->pluck('student_program_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();
    $batchIds = $combinations->pluck('batch_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();

    $enrolledProgramBatchKeyMap = collect();
    if ($programIds->isNotEmpty() && $batchIds->isNotEmpty()) {
      $enrolledStudentsQuery = StudentMaster::query()
        ->whereIn('new_program_id', $programIds->all())
        ->whereIn('batch', $batchIds->all());

      if ($selectedCampusId > 0 && Schema::hasColumn((new StudentMaster())->getTable(), 'campus_id')) {
        $enrolledStudentsQuery->where('campus_id', $selectedCampusId);
      }

      $enrolledProgramBatchKeyMap = $enrolledStudentsQuery
        ->get(['new_program_id', 'batch'])
        ->map(function ($student) {
          return (int) ($student->new_program_id ?? 0) . '|' . (int) ($student->batch ?? 0);
        })
        ->filter()
        ->unique()
        ->flip();
    }

    $enrolledCombinations = $combinations->filter(function ($combination) use ($enrolledProgramBatchKeyMap) {
      if ($enrolledProgramBatchKeyMap->isEmpty()) {
        return false;
      }

      $key = (int) ($combination->student_program_id ?? 0) . '|' . (int) ($combination->batch_id ?? 0);
      return $enrolledProgramBatchKeyMap->has($key);
    })->values();

    $batchWiseCombinationCounts = $enrolledCombinations
      ->groupBy(fn($combination) => (int) ($combination->batch_id ?? 0))
      ->map(function ($batchCombinations) {
        $batchCombinations = collect($batchCombinations)->values();
        $firstCombination = $batchCombinations->first();

        return (object) [
          'batch_id' => (int) ($firstCombination->batch_id ?? 0),
          'batch_name' => (string) (optional($firstCombination?->batchmaster)->batch_name ?? '-'),
          'combination_count' => $batchCombinations->count(),
          'department_count' => $batchCombinations->pluck('subject_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->count(),
        ];
      })
      ->sortBy('batch_id')
      ->values();

    $curriculumRowsByCombination = collect();
    $assignmentsBySubjectCourse = collect();

    if ($combinationIds->isNotEmpty()) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $hasIsActiveColumn = Schema::hasColumn($curriculumTable, 'is_active');
      $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');

      $curriculumQuery = ProgramWiseSemesterCourse::with('programinfo:id,course_code,course_title')
        ->whereIn('program_combo_refid', $combinationIds)
        ->orderBy('semester');

      if ($hasDisplayOrderColumn) {
        $curriculumQuery->orderBy('display_order');
      }

      if ($hasIsActiveColumn) {
        $curriculumQuery->where('is_active', 1);
      }

      $curriculumRows = $curriculumQuery->get([
        'program_combo_refid',
        'course_id',
        'semester',
        'course_type',
        'delivery_category',
      ]);

      $curriculumRowsByCombination = $curriculumRows->groupBy(function ($row) {
        return (int) ($row->program_combo_refid ?? 0);
      });

      $courseIds = $curriculumRows->pluck('course_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      if ($courseIds->isNotEmpty()) {
        $teachingAssignmentQuery = TeachingAssignment::with([
          'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
          'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
        ])
          ->whereIn('course_id', $courseIds)
          ->when($subjectIds->isNotEmpty(), function ($query) use ($subjectIds) {
            $query->whereIn('subject_id', $subjectIds);
          });

        $teachingAssignmentTable = (new TeachingAssignment())->getTable();
        if (Schema::hasColumn($teachingAssignmentTable, 'is_active')) {
          $teachingAssignmentQuery->where('is_active', 1);
        }

        $teachingAssignments = $teachingAssignmentQuery->get([
          'id',
          'subject_id',
          'course_id',
          'faculty_id',
          'delivery_type',
          'allocation_group',
          'is_active',
        ]);

        $assignmentsBySubjectCourse = $teachingAssignments->groupBy(function ($assignment) {
          return (int) ($assignment->subject_id ?? 0) . '|' . (int) ($assignment->course_id ?? 0);
        });
      }
    }

    $combinationsByProgram = $combinations->groupBy('student_program_id');

    $programRows = $combinationsByProgram->map(function ($programCombinations) use ($curriculumRowsByCombination, $assignmentsBySubjectCourse) {
      $programCombinations = collect($programCombinations)->values();
      $firstCombination = $programCombinations->first();
      $programInfo = $firstCombination ? $firstCombination->studentprograminfo : null;

      $combinationDetails = $programCombinations->map(function ($combination) use ($curriculumRowsByCombination, $assignmentsBySubjectCourse) {
        $subjectId = (int) ($combination->subject_id ?? 0);
        $curriculumRows = collect($curriculumRowsByCombination->get((int) $combination->id, collect()));

        $curriculumCourses = $curriculumRows->map(function ($row) use ($subjectId, $assignmentsBySubjectCourse) {
          $courseId = (int) ($row->course_id ?? 0);
          $deliveryType = strtoupper(trim((string) ($row->delivery_category ?? $row->course_type ?? '-')));
          $subjectCourseKey = $subjectId . '|' . $courseId;
          $matchingAssignments = collect($assignmentsBySubjectCourse->get($subjectCourseKey, collect()));

          $normalizedDeliveryType = preg_replace('/[^A-Z0-9]/', '', $deliveryType);
          if ($matchingAssignments->isNotEmpty() && $normalizedDeliveryType !== '') {
            $deliveryMatchedAssignments = $matchingAssignments->filter(function ($assignment) use ($normalizedDeliveryType) {
              $assignmentDelivery = strtoupper(trim((string) ($assignment->delivery_type ?? '')));
              $normalizedAssignmentDelivery = preg_replace('/[^A-Z0-9]/', '', $assignmentDelivery);
              return $normalizedAssignmentDelivery === $normalizedDeliveryType;
            });

            if ($deliveryMatchedAssignments->isNotEmpty()) {
              $matchingAssignments = $deliveryMatchedAssignments;
            }
          }

          $assignedFaculty = $matchingAssignments->map(function ($assignment) {
            $primaryName = trim((string) (optional($assignment->faculty)->FIRST_NAME ?? '') . ' ' . (string) (optional($assignment->faculty)->LAST_NAME ?? ''));
            $primaryCode = trim((string) (optional($assignment->faculty)->USER_CODE ?? ''));
            $primaryLabel = trim(($primaryCode !== '' ? $primaryCode . ' - ' : '') . $primaryName);

            $coFacultyLabels = collect($assignment->coFacultyMembers ?? [])
              ->map(function ($faculty) {
                $name = trim((string) ($faculty->FIRST_NAME ?? '') . ' ' . (string) ($faculty->LAST_NAME ?? ''));
                $code = trim((string) ($faculty->USER_CODE ?? ''));
                return trim(($code !== '' ? $code . ' - ' : '') . $name);
              })
              ->filter()
              ->values();

            if ($primaryLabel === '' && $coFacultyLabels->isEmpty()) {
              return 'Not assigned yet';
            }

            if ($coFacultyLabels->isEmpty()) {
              return $primaryLabel;
            }

            return ($primaryLabel !== '' ? $primaryLabel : 'Primary not set') . ' | Co: ' . $coFacultyLabels->implode(', ');
          })
            ->filter()
            ->unique()
            ->values();

          return [
            'semester' => (int) ($row->semester ?? 0),
            'course_code' => (string) (optional($row->programinfo)->course_code ?? '-'),
            'course_title' => (string) (optional($row->programinfo)->course_title ?? '-'),
            'course_type' => (string) ($row->course_type ?? '-'),
            'delivery_type' => $deliveryType !== '' ? $deliveryType : '-',
            'assigned_faculty' => $assignedFaculty->isNotEmpty() ? $assignedFaculty->implode('; ') : 'Not assigned yet',
          ];
        })
          ->sortBy([
            ['semester', 'asc'],
            ['course_code', 'asc'],
          ])
          ->values();

        return (object) [
          'combination_id' => (int) $combination->id,
          'batch_name' => (string) (optional($combination->batchmaster)->batch_name ?? '-'),
          'subject_code' => (string) (optional($combination->subjectmaster)->code ?? ''),
          'subject_name' => (string) (optional($combination->subjectmaster)->title ?? '-'),
          'program_type' => (string) ($combination->program_type ?? ''),
          'curriculum_courses' => $curriculumCourses,
          'curriculum_count' => $curriculumCourses->count(),
        ];
      })->values();

      return (object) [
        'program_id' => (int) ($firstCombination->student_program_id ?? 0),
        'program_code' => (string) (optional($programInfo)->code ?? '-'),
        'program_name' => (string) (optional($programInfo)->name ?? '-'),
        'campus_name' => (string) (optional(optional($programInfo)->campusmaster)->name ?? '-'),
        'program_type_name' => (string) (optional(optional($programInfo)->programtypemaster)->name ?? strtoupper((string) ($firstCombination->program_type ?? ''))),
        'combinations' => $combinationDetails,
        'combination_count' => $combinationDetails->count(),
        'curriculum_count' => $combinationDetails->sum('curriculum_count'),
      ];
    })->filter(function ($row) {
      return $row->combination_count > 0;
    })->values();

    $combinationsWithCurriculum = $curriculumRowsByCombination
      ->filter(fn($rows) => collect($rows)->isNotEmpty())
      ->count();

    $totalCombinations = $combinations->count();
    $curriculumSummary = (object) [
      'total_departments' => $subjectIds->count(),
      'total_combinations' => $totalCombinations,
      'combinations_with_curriculum' => $combinationsWithCurriculum,
      'combinations_without_curriculum' => max(0, $totalCombinations - $combinationsWithCurriculum),
      'curriculum_source_table' => Schema::hasTable('curriculam_engine') ? 'curriculam_engine' : 'program_wise_semester_courses',
      'curriculum_records_found' => $curriculumRowsByCombination->flatten(1)->count() > 0,
    ];

    return view('principal.curriculam.index', [
      'campuses' => $campuses,
      'batches' => $batches,
      'subjects' => $subjects,
      'selectedCampusId' => $selectedCampusId,
      'selectedBatchId' => $selectedBatchId,
      'selectedSubjectId' => $selectedSubjectId,
      'programRows' => $programRows,
      'isVicePrincipal' => $isVicePrincipal,
      'batchWiseCombinationCounts' => $batchWiseCombinationCounts,
      'curriculumSummary' => $curriculumSummary,
    ]);
  }

  /**
   * Defaulter list for program combinations with no curriculum created.
   */
  public function curriculamDefaulters(Request $request)
  {
    $campuses = Campus::orderBy('name')->get(['id', 'name']);
    $batches = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);

    $userRole = auth()->user()->userroletype->role_name ?? null;
    $isVicePrincipal = $userRole === 'vice-principal';

    $selectedCampusId = (int) $request->query('campus_id', 0);
    $selectedBatchId = (int) $request->query('batch_id', 0);
    $selectedSubjectId = (int) $request->query('subject_id', 0);

    if ($isVicePrincipal) {
      $vpCampusId = (int) UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
      if ($vpCampusId > 0) {
        $selectedCampusId = $vpCampusId;
      }
    }

    $combinationQuery = SubjectHasStudentProgam::with([
      'batchmaster:id,batch_name',
      'subjectmaster:id,title,code',
      'studentprograminfo:id,code,name,campus_id,program_type',
      'studentprograminfo.campusmaster:id,name',
      'studentprograminfo.programtypemaster:id,name',
    ]);

    if ($selectedCampusId > 0) {
      $combinationQuery->where('campus_id', $selectedCampusId);
    }

    if ($selectedBatchId > 0) {
      $combinationQuery->where('batch_id', $selectedBatchId);
    }

    $subjectFilterIds = SubjectHasStudentProgam::query()
      ->when($selectedCampusId > 0, fn($query) => $query->where('campus_id', $selectedCampusId))
      ->when($selectedBatchId > 0, fn($query) => $query->where('batch_id', $selectedBatchId))
      ->pluck('subject_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $subjects = Subject::query()
      ->when($subjectFilterIds->isNotEmpty(), fn($query) => $query->whereIn('id', $subjectFilterIds->all()))
      ->orderBy('title')
      ->get(['id', 'title', 'code']);

    if ($selectedSubjectId > 0) {
      $combinationQuery->where('subject_id', $selectedSubjectId);
    }

    $combinations = $combinationQuery
      ->orderBy('student_program_id')
      ->orderBy('batch_id')
      ->get(['id', 'student_program_id', 'subject_id', 'batch_id', 'campus_id', 'program_type']);

    $programIds = $combinations->pluck('student_program_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();
    $batchIds = $combinations->pluck('batch_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();

    $enrolledProgramBatchKeyMap = collect();
    if ($programIds->isNotEmpty() && $batchIds->isNotEmpty()) {
      $enrolledStudentsQuery = StudentMaster::query()
        ->whereIn('new_program_id', $programIds->all())
        ->whereIn('batch', $batchIds->all());

      if ($selectedCampusId > 0 && Schema::hasColumn((new StudentMaster())->getTable(), 'campus_id')) {
        $enrolledStudentsQuery->where('campus_id', $selectedCampusId);
      }

      $enrolledProgramBatchKeyMap = $enrolledStudentsQuery
        ->get(['new_program_id', 'batch'])
        ->map(function ($student) {
          return (int) ($student->new_program_id ?? 0) . '|' . (int) ($student->batch ?? 0);
        })
        ->filter()
        ->unique()
        ->flip();
    }

    $combinations = $combinations->filter(function ($combination) use ($enrolledProgramBatchKeyMap) {
      if ($enrolledProgramBatchKeyMap->isEmpty()) {
        return false;
      }

      $key = (int) ($combination->student_program_id ?? 0) . '|' . (int) ($combination->batch_id ?? 0);
      return $enrolledProgramBatchKeyMap->has($key);
    })->values();

    $combinationIds = $combinations->pluck('id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->values();
    $subjectIds = $combinations->pluck('subject_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();

    $curriculumCounts = collect();
    $curriculumRowsByCombination = collect();
    if ($combinationIds->isNotEmpty()) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $curriculumQuery = ProgramWiseSemesterCourse::with('programinfo:id,course_code,course_title')->whereIn('program_combo_refid', $combinationIds);

      if (Schema::hasColumn($curriculumTable, 'is_active')) {
        $curriculumQuery->where('is_active', 1);
      }

      $curriculumRows = $curriculumQuery
        ->get(['program_combo_refid', 'course_id', 'semester', 'course_type', 'delivery_category'])
        ->sortBy([
          ['semester', 'asc'],
          ['course_id', 'asc'],
        ])
        ->values();

      $curriculumRowsByCombination = $curriculumRows->groupBy(function ($row) {
        return (int) ($row->program_combo_refid ?? 0);
      });

      $curriculumCounts = $curriculumRows->groupBy('program_combo_refid')->map->count();
    }

    $defaulters = $combinations->filter(function ($combination) use ($curriculumCounts) {
      return (int) ($curriculumCounts[$combination->id] ?? 0) <= 0;
    })->unique(function ($combination) {
      return implode('|', [
        (int) ($combination->student_program_id ?? 0),
        (int) ($combination->subject_id ?? 0),
        (int) ($combination->batch_id ?? 0),
        (int) ($combination->campus_id ?? 0),
        strtoupper(trim((string) ($combination->program_type ?? ''))),
      ]);
    })->map(function ($combination) {
      $programInfo = $combination->studentprograminfo;
      $subject = $combination->subjectmaster;

      return (object) [
        'combination_id' => (int) $combination->id,
        'program_name' => (string) (optional($programInfo)->name ?? '-'),
        'program_code' => (string) (optional($programInfo)->code ?? '-'),
        'subject_name' => (string) (optional($subject)->title ?? '-'),
        'subject_code' => (string) (optional($subject)->code ?? ''),
        'batch_name' => (string) (optional($combination->batchmaster)->batch_name ?? '-'),
        'campus_name' => (string) (optional(optional($programInfo)->campusmaster)->name ?? '-'),
        'program_type_name' => (string) (optional(optional($programInfo)->programtypemaster)->name ?? strtoupper((string) ($combination->program_type ?? ''))),
      ];
    })->values();

    return view('principal.curriculam.defaulters', [
      'campuses' => $campuses,
      'batches' => $batches,
      'subjects' => $subjects,
      'selectedCampusId' => $selectedCampusId,
      'selectedBatchId' => $selectedBatchId,
      'selectedSubjectId' => $selectedSubjectId,
      'programRows' => $defaulters,
      'isVicePrincipal' => $isVicePrincipal,
      'totalDefaulters' => $defaulters->count(),
    ]);
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
      'subjectCourse.courseMaster.semestermaster',
      'syllabus.semestermaster',
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
      'stdprogramenrolled',
      'feepayment.feepaymentinfo:id,quarter_title',
      'feepayment.gatewaytype',
      'academicpathway',
      'degreetrack',
      'singleselection',
      'subjectmaster',
    ])->firstOrFail();

    $studentCourses = StudentCourseInfo::with([
      'coursemaster.semestermaster:id,title',
      'coursemaster.coursetypemaster:id,title,description',
    ])
      ->where('student_id', $id)
      ->orderByDesc('id')
      ->get()
      ->unique(fn($c) => ($c->semester ?? $c->coursemaster?->semester_id ?? 'na') . '_' . $c->course_id)
      ->values();

    $semesterMap = Semester::pluck('title', 'id')->toArray();

    $coursesBySemester = $studentCourses
      ->sortBy(fn($c) => $c->semester ?? $c->coursemaster?->semester_id ?? 999)
      ->groupBy(function ($c) use ($semesterMap) {
        $semId = $c->semester ?? $c->coursemaster?->semester_id;
        return $semesterMap[$semId] ?? ('Semester ' . ($semId ?? '?'));
      });

    $faSegregatedMarks = CiaMark::where('STUDENT_ID', $id)->with([
      'studentcourseinfo.coursemaster:id,course_title,course_code,semester_id',
      'groupinfo.grouptype:id,name',
    ])->get()->groupBy(fn($c) => $c->SEMESTER_ID);

    $interMarkedCourseIds = InterMark::where('student_id', $id)->pluck('course_id')->unique()->toArray();
    $ciaMarkedCourseIds   = CiaMark::where('STUDENT_ID', $id)->pluck('COURSE_ID')->unique()->toArray();
    $saMarkedCourseIds    = DB::table('exam_marks_entries')->where('erp_student_id', $id)->pluck('erp_subject_id')->unique()->toArray();
    $lockedCourseIds      = array_unique(array_merge($interMarkedCourseIds, $ciaMarkedCourseIds, $saMarkedCourseIds));

    $enrolledCourseIds = $studentCourses->pluck('course_id')->toArray();
    $availableCourses = ProgramCourseMaster::where('is_deleted', 0)
      ->whereNotIn('id', $enrolledCourseIds)
      ->with('semestermaster:id,title', 'coursetypemaster:id,title')
      ->orderBy('semester_id')
      ->orderBy('course_title')
      ->get()
      ->groupBy(fn($c) => $c->semester_id);

    $availableSemesters = Semester::orderBy('id')->get();

    $deliveryContext = $this->resolveStudentDeliveryContext($data, $studentCourses);
    $timetable       = StudentTimetableService::generate($id);
    $timetableByDay  = $timetable->groupBy(fn($r) => $r['weekday'] ?? 'Unknown');

    $attendanceRaw = StudentAttendance::where('student_id', $id)
      ->with('courseinfo:id,course_title,course_code')
      ->get()
      ->groupBy('course_id');

    $attendanceSummary = $attendanceRaw->map(function ($records) {
      $total   = $records->count();
      $present = $records->where('status', 'present')->count();
      return [
        'course'     => $records->first()->courseinfo,
        'total'      => $total,
        'present'    => $present,
        'absent'     => $total - $present,
        'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
      ];
    })->values();

    $internalMarks = InterMark::where('student_id', $id)
      ->with(['course:id,course_title,course_code', 'semester:id,title'])
      ->where('is_deleted', 0)
      ->orderBy('semester')
      ->get();

    $faMarksByCourseSemester = $internalMarks
      ->sortByDesc('id')
      ->groupBy(fn($m) => (string) $m->semester . '_' . (string) $m->course_id)
      ->map(fn($rows) => $rows->first());

    $saMarksByCourseSemester = DB::table('exam_marks_entries as eme')
      ->join('exam_sessions as es', 'es.id', '=', 'eme.exam_session_id')
      ->where('eme.erp_student_id', $id)
      ->select('eme.erp_subject_id as course_id', 'es.semester as semester', DB::raw('MAX(eme.marks) as sa_marks'))
      ->groupBy('eme.erp_subject_id', 'es.semester')
      ->get()
      ->keyBy(fn($m) => (string) $m->semester . '_' . (string) $m->course_id);

    $ciaMarksBySemester = $studentCourses
      ->groupBy(fn($c) => (string) ($c->semester ?? $c->coursemaster?->semester_id ?? 'Unknown'))
      ->map(function ($courses, $semester) use ($faMarksByCourseSemester, $saMarksByCourseSemester, $semesterMap) {
        $rows = $courses
          ->sortBy(fn($c) => $c->coursemaster?->course_code ?? 'ZZZ')
          ->map(function ($course) use ($semester, $faMarksByCourseSemester, $saMarksByCourseSemester) {
            $key = (string) $semester . '_' . (string) $course->course_id;
            $fa  = $faMarksByCourseSemester->get($key);
            $sa  = $saMarksByCourseSemester->get($key);
            return [
              'course'   => $course->coursemaster,
              'fa_marks' => $fa?->internal_mark,
              'sa_marks' => $sa?->sa_marks,
              'semester' => $semester,
            ];
          })
          ->values();
        return [
          'label' => $semesterMap[(int) $semester] ?? ('Semester ' . $semester),
          'rows'  => $rows,
        ];
      })
      ->values();

    $examStudent = ExamStudent::where('erp_student_id', $id)->first();
    $examResults = collect();
    if ($examStudent) {
      $examResults = Result::where('exam_student_id', $examStudent->id)
        ->where('is_published', true)
        ->with(['examSession', 'resultSubjects'])
        ->orderByDesc('created_at')
        ->get();
    }

    return view('principal.students.student-profile', [
      'data'                           => $data,
      'studentCourses'                 => $studentCourses,
      'coursesBySemester'              => $coursesBySemester,
      'lockedCourseIds'                => $lockedCourseIds,
      'availableCourses'               => $availableCourses,
      'availableSemesters'             => $availableSemesters,
      'timetableByDay'                 => $timetableByDay,
      'attendanceSummary'              => $attendanceSummary,
      'internalMarks'                  => $internalMarks,
      'ciaMarksBySemester'             => $ciaMarksBySemester,
      'faSegregatedMarks'              => $faSegregatedMarks,
      'examResults'                    => $examResults,
      'examStudent'                    => $examStudent,
      'batches'                        => BatchMaster::orderBy('batch_name')->get(),
      'departments'                    => DepartmentMaster::orderBy('name')->get(),
      'campuses'                       => Campus::orderBy('name')->get(),
      'religions'                      => ReligionMaster::orderBy('name')->get(),
      'nationalities'                  => NationalityMaster::orderBy('name')->get(),
      'bloodGroups'                    => BloodGroupMaster::orderBy('name')->get(),
      'studentMajorDeliveryType'       => $deliveryContext['studentMajorDeliveryType'],
      'studentApplicableDeliveryTypes' => $deliveryContext['studentApplicableDeliveryTypes'],
      'combo1Title'                    => $deliveryContext['combo1Title'],
      'combo2Title'                    => $deliveryContext['combo2Title'],
      'courseDeliveryMap'              => $deliveryContext['courseDeliveryMap'],
      'courseOfferingSubjectMap'       => $deliveryContext['courseOfferingSubjectMap'],
      'programOfferingSubjectTitle'    => $deliveryContext['programOfferingSubjectTitle'],
    ]);
  }

  private function resolveStudentDeliveryContext(?StudentMaster $student, $studentCourses): array
  {
    $programCombination = null;
    if ($student && !empty($student->new_program_id) && !empty($student->batch)) {
      $programCombination = SubjectHasStudentProgam::with([
        'subjectmaster:id,title,code',
        'combomap.combo1:id,title,code',
        'combomap.combo2:id,title,code',
      ])
        ->where('student_program_id', (int) $student->new_program_id)
        ->where('batch_id', (int) $student->batch)
        ->orderBy('id')
        ->first();
    }

    $combo1Id = (int) ($programCombination?->combomap?->combo_id_1 ?? 0);
    if ($combo1Id <= 0) {
      $combo1Id = (int) ($programCombination?->subject_id ?? 0);
    }
    $combo2Id        = (int) ($programCombination?->combomap?->combo_id_2 ?? 0);
    $selectedComboId = (int) ($student->selected_combo_id ?? 0);

    $studentMajorDeliveryType = null;
    if ($selectedComboId > 0) {
      if ($selectedComboId === $combo1Id) {
        $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
      } elseif ($combo2Id > 0 && $selectedComboId === $combo2Id) {
        $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
      }
    } elseif ($combo1Id > 0 && $combo2Id <= 0) {
      $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
    }

    $studentApplicableDeliveryTypes = collect([
      $studentMajorDeliveryType,
      ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
      ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
    ])->filter()->unique()->values();

    $courseDeliveryMap        = [];
    $courseOfferingSubjectMap = [];
    $programType = strtoupper(trim((string) ($programCombination?->program_type ?? '')));

    if ($programCombination && $studentCourses) {
      $courseIds = collect($studentCourses)
        ->pluck('course_id')
        ->map(fn($id) => (int) $id)
        ->filter()->unique()->values()->all();

      $semesterIds = collect($studentCourses)
        ->map(fn($course) => (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0))
        ->filter(fn($id) => $id > 0)->unique()->values()->all();

      if (!empty($courseIds)) {
        $deliveryRowsQuery = ProgramWiseSemesterCourse::where('program_combo_refid', (int) $programCombination->id)
          ->where('batch', (int) $student->batch)
          ->whereIn('course_id', $courseIds);

        $pathwayId     = (int) ($student->academic_pathway_id ?? 0);
        $degreeTrackId = (int) ($student->degree_track_id ?? 0);

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
          $pathwayId > 0
            ? $deliveryRowsQuery->where('academic_pathway_id', $pathwayId)
            : $deliveryRowsQuery->whereNull('academic_pathway_id');
        }

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
          $degreeTrackId > 0
            ? $deliveryRowsQuery->where('degree_track_id', $degreeTrackId)
            : $deliveryRowsQuery->whereNull('degree_track_id');
        }

        foreach ($deliveryRowsQuery->get(['semester', 'course_id', 'delivery_category']) as $row) {
          $key = (string) ((int) $row->semester) . '_' . (string) ((int) $row->course_id);
          if (!empty($row->delivery_category)) {
            $courseDeliveryMap[$key] = (string) $row->delivery_category;
          }
        }
      }

      if (!empty($courseIds) && !empty($semesterIds)) {
        $syllabusQuery = SyllabusManager::with('subject:id,title,code')
          ->where('batch_id', (int) $student->batch)
          ->whereIn('co_id', $courseIds)
          ->whereIn('semester_id', $semesterIds);

        if (Schema::hasColumn('syllabus_managers', 'status')) {
          $syllabusQuery->where('status', 'published');
        }
        if ($programType !== '' && Schema::hasColumn('syllabus_managers', 'program_type')) {
          $syllabusQuery->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
        }

        foreach ($syllabusQuery->get(['semester_id', 'co_id', 'subject_id']) as $row) {
          $key          = (string) ((int) $row->semester_id) . '_' . (string) ((int) $row->co_id);
          $subjectTitle = trim((string) ($row->subject?->title ?? ''));
          if ($subjectTitle === '') continue;
          $courseOfferingSubjectMap[$key]   = $courseOfferingSubjectMap[$key] ?? [];
          if (!in_array($subjectTitle, $courseOfferingSubjectMap[$key], true)) {
            $courseOfferingSubjectMap[$key][] = $subjectTitle;
          }
        }
        foreach ($courseOfferingSubjectMap as $key => $subjects) {
          $courseOfferingSubjectMap[$key] = implode(' / ', $subjects);
        }
      }
    }

    return [
      'studentMajorDeliveryType'       => $studentMajorDeliveryType,
      'studentApplicableDeliveryTypes' => $studentApplicableDeliveryTypes,
      'combo1Title'                    => (string) ($programCombination?->combomap?->combo1?->title ?? ''),
      'combo2Title'                    => (string) ($programCombination?->combomap?->combo2?->title ?? ''),
      'courseDeliveryMap'              => $courseDeliveryMap,
      'courseOfferingSubjectMap'       => $courseOfferingSubjectMap,
      'programOfferingSubjectTitle'    => (string) ($programCombination?->subjectmaster?->title ?? ''),
    ];
  }

  /**
   * Courses with CSO subunits, completion status, and student feedback.
   */
  public function courses(Request $request)
  {
    $campuses = Campus::all();
    $departments = Subject::all();
    $semesters = Semester::orderBy('id')->get();
    $academicYears = BatchMaster::all();

    // Check if user is vice-principal
    $userRole = StaticController::fetchUserRole();
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
        $q->where('id', $request->department_id);
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
        // Count distinct attendance dates (actual number of classes taken)
        $syl->total_classes = StudentAttendance::where('routine_id', $routineId)
          ->groupBy('attendance_date')->count();
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
        ->groupBy('attendance_date')->count();

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
      // Use SyllabusManager to get all CSO entries
      $syllQuery = SyllabusManager::where('subject_id', $subject->id)
        ->with([
          'batch',
          'semester',
          'courseobjective.coursetypemaster',
          'syllabusSubunits.csoSubunit',
        ]);

      if ($request->filled('batch_id')) {
        $syllQuery->where('batch_id', $request->batch_id);
      }

      if ($request->filled('semester_id')) {
        $syllQuery->where('semester_id', $request->semester_id);
      }

      // Group by course (co_id) to show course-wise CSOs
      $syllabusData = $syllQuery->get()->groupBy('co_id');

      $syllabi = collect();

      foreach ($syllabusData as $coId => $csoGroup) {
        // Get course details from first CSO in group
        $firstCso = $csoGroup->first();
        $courseMaster = $firstCso->courseobjective;

        // Count total and completed subunits across all CSOs for this course
        $totalSubunits = 0;
        $completedSubunits = 0;

        foreach ($csoGroup as $csoEntry) {
          $total = $csoEntry->syllabusSubunits->count();
          $completed = $csoEntry->syllabusSubunits->where('is_completed', 1)->count();
          $totalSubunits += $total;
          $completedSubunits += $completed;
        }

        // Create a syllabus entry object for display
        $syllabusEntry = (object)[
          'id' => $firstCso->id,
          'batch_id' => $firstCso->batch_id,
          'semester_id' => $firstCso->semester_id,
          'co_id' => $coId,
          'batchmaster' => $firstCso->batch,
          'semestermaster' => $firstCso->semester,
          'course_code' => $courseMaster ? $courseMaster->course_code : '-',
          'course_title_pcm' => $courseMaster ? $courseMaster->course_title : '-',
          'course_type_name' => $courseMaster && $courseMaster->coursetypemaster
            ? $courseMaster->coursetypemaster->title
            : '-',
          'total_subunits' => $totalSubunits,
          'completed_subunits' => $completedSubunits,
          'completion_percent' => $totalSubunits > 0
            ? round(($completedSubunits / $totalSubunits) * 100, 1)
            : 0,
          'cso_count' => $csoGroup->count(),
        ];

        $syllabi->push($syllabusEntry);
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

    // Use SyllabusManager to get all CSO entries with subunits
    $syllQuery = SyllabusManager::where('subject_id', $id)
      ->with([
        'batch',
        'semester',
        'courseobjective.coursetypemaster',
        'syllabusSubunits.csoSubunit.taxomonylevel',
        'syllabusSubunits.learningResources',
        'syllabusSubunits.questions',
      ]);

    if ($request->filled('batch_id')) {
      $syllQuery->where('batch_id', $request->batch_id);
    }

    if ($request->filled('semester_id')) {
      $syllQuery->where('semester_id', $request->semester_id);
    }

    // Group by course to consolidate CSOs
    $syllabusData = $syllQuery->get()->groupBy('co_id');

    $syllabi = collect();

    foreach ($syllabusData as $coId => $csoGroup) {
      // Get course details from first CSO in group
      $firstCso = $csoGroup->first();
      $courseMaster = $firstCso->courseobjective;

      // Collect all subunits from all CSOs for this course
      $allSubunits = collect();
      foreach ($csoGroup as $csoEntry) {
        $allSubunits = $allSubunits->merge($csoEntry->syllabusSubunits);
      }

      // Calculate completion stats
      $total = $allSubunits->count();
      $completed = $allSubunits->where('is_completed', 1)->count();
      $completionPercent = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

      // Get feedback stats
      $subunitIds = $allSubunits->pluck('id');
      $feedbackCount = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->count();
      $avgRating = SubUnitStudentFeedback::whereIn('syllabus_subunit_id', $subunitIds)->avg('rating');

      // Get faculty name (from first CSO's timetable if exists)
      $facultyName = '-';

      // Try to find faculty from timetable for this course
      $timetable = SubjectHasRoutine::whereHas('syllabus.subject', function ($q) use ($subject) {
        $q->where('id', $subject->id);
      })
        ->where('subject_course_id', $courseMaster ? $courseMaster->course_code : null)
        ->with('faculty')
        ->first();
      if ($timetable && $timetable->faculty) {
        $facultyName = $timetable->faculty->FIRST_NAME . ' ' . $timetable->faculty->LAST_NAME;
      }

      // Create a syllabus entry object for display
      $syllabusEntry = (object)[
        'id' => $firstCso->id,
        'batch_id' => $firstCso->batch_id,
        'semester_id' => $firstCso->semester_id,
        'co_id' => $coId,
        'batchmaster' => $firstCso->batch,
        'semestermaster' => $firstCso->semester,
        'course_code' => $courseMaster ? $courseMaster->course_code : '-',
        'course_title_pcm' => $courseMaster ? $courseMaster->course_title : '-',
        'course_type_name' => $courseMaster && $courseMaster->coursetypemaster
          ? $courseMaster->coursetypemaster->title
          : '-',
        'academic_year_val' => $courseMaster ? $courseMaster->academic_year : '-',
        'total_subunits' => $total,
        'completed_subunits' => $completed,
        'completion_percent' => $completionPercent,
        'syllabusunits' => $allSubunits,
        'faculty_name' => $facultyName,
        'feedback_count' => $feedbackCount,
        'avg_rating' => $avgRating,
        'cso_count' => $csoGroup->count(),
      ];

      $syllabi->push($syllabusEntry);
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
