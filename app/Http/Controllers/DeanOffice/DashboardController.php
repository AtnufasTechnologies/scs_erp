<?php

namespace App\Http\Controllers\DeanOffice;

use App\Http\Controllers\Controller;
use App\Models\DeanAnnualPlan;
use App\Models\DeanComparativeReport;
use App\Models\DeanLessonTracker;
use App\Models\DeanPerformanceScorecard;
use App\Models\DeanTask;
use App\Models\DeanWeeklyProgress;
use App\Models\Faculty;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\Subject;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasStudentProgam;
use App\Models\SyllabusManager;
use App\Models\TeachingAssignment;
use App\Repositories\Dean\EventAnalyticsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
  public function __construct(protected EventAnalyticsRepository $eventRepo) {}

  public function index()
  {
    $user = Auth::user();
    $userId = (int) ($user->id ?? 0);

    $facultyId = (int) SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    $faculty = $facultyId > 0 ? Faculty::find($facultyId) : null;

    $annualPlans = DeanAnnualPlan::where('user_id', $userId)->latest()->get();
    $weeklyProgress = DeanWeeklyProgress::where('user_id', $userId)->latest('week_date')->get();
    $lessonTrackers = DeanLessonTracker::where('user_id', $userId)->latest()->get();
    $scorecards = DeanPerformanceScorecard::where('user_id', $userId)->latest()->get();
    $tasks = DeanTask::where('user_id', $userId)->latest('due_date')->get();
    $comparativeRows = DeanComparativeReport::where('user_id', $userId)->latest()->get()->keyBy('metric_code');

    $comparativeDefaults = [
      ['metric_code' => 'attendance_comparison', 'title' => 'ATTENDANCE COMPARISON'],
      ['metric_code' => 'syllabus_completion_comparison', 'title' => 'SYLLABUS COMPLETION COMPARISON'],
      ['metric_code' => 'assessment_fa_sa', 'title' => 'ASSESSMENT PERFORMANCE (FA 1/2/3 and SA)'],
      ['metric_code' => 'assessment_back_paper', 'title' => 'ASSESSMENT PERFORMANCE OF BACK PAPER'],
      ['metric_code' => 'mou_comparison_status', 'title' => 'MoU COMPARISON status'],
      ['metric_code' => 'special_department_projects', 'title' => 'SPECIAL DEPARTMENT PROJECTS'],
    ];

    $completionByCategory = [
      'academic' => $this->completionForCategory($annualPlans, 'academic'),
      'research' => $this->completionForCategory($annualPlans, 'research'),
      'administration' => $this->completionForCategory($annualPlans, 'administration'),
    ];

    $scoreSummary = $this->buildScoreSummary($scorecards);
    $hod360Rows = $this->buildHod360Snapshot($faculty);
    $hod360Followups = DeanComparativeReport::where('user_id', $userId)
      ->where('metric_code', 'like', 'hod360_%')
      ->get()
      ->keyBy('metric_code');

    return view('dean-office.dashboard.index', [
      'faculty' => $faculty,
      'annualPlans' => $annualPlans,
      'weeklyProgress' => $weeklyProgress,
      'lessonTrackers' => $lessonTrackers,
      'scorecards' => $scorecards,
      'tasks' => $tasks,
      'comparativeDefaults' => $comparativeDefaults,
      'comparativeRows' => $comparativeRows,
      'hod360Rows' => $hod360Rows,
      'hod360Followups' => $hod360Followups,
      'completionByCategory' => $completionByCategory,
      'scoreSummary' => $scoreSummary,
    ]);
  }

  public function storeAnnualPlan(Request $request)
  {
    $data = $request->validate([
      'activity_goal' => 'required|string|max:255',
      'category' => 'required|in:academic,research,administration,co-curricular,professional',
      'target' => 'nullable|string|max:255',
      'expected_completion_date' => 'nullable|date',
      'priority' => 'nullable|string|max:50',
      'semester_month' => 'nullable|string|max:100',
      'expected_outcome' => 'nullable|string',
      'achievement_actual_result' => 'nullable|string',
      'evidence_required' => 'nullable|string',
      'status' => 'nullable|string|max:50',
      'verified_by' => 'nullable|string|max:100',
    ]);

    $data['user_id'] = (int) Auth::id();
    DeanAnnualPlan::create($data);

    return back()->with('success', 'Annual plan row added.');
  }

  public function storeWeeklyProgress(Request $request)
  {
    $data = $request->validate([
      'week_date' => 'nullable|date',
      'activities_completed' => 'nullable|string',
      'activities_in_progress' => 'nullable|string',
      'pending_activities' => 'nullable|string',
      'completion_percent' => 'nullable|numeric|min:0|max:100',
      'reason_for_delay' => 'nullable|string',
      'evidence_remarks' => 'nullable|string',
    ]);

    $data['user_id'] = (int) Auth::id();
    DeanWeeklyProgress::create($data);

    return back()->with('success', 'Weekly progress row added.');
  }

  public function storeLessonTracker(Request $request)
  {
    $data = $request->validate([
      'course_subject' => 'required|string|max:255',
      'unit_module' => 'nullable|string|max:255',
      'topics_planned' => 'nullable|string',
      'plan_to_complete_date' => 'nullable|date',
      'topics_completed' => 'nullable|string',
      'completion_date' => 'nullable|date',
      'classes_planned' => 'nullable|integer|min:0',
      'assessment_conducted' => 'nullable|string|max:255',
      'syllabus_completion_percent' => 'nullable|numeric|min:0|max:100',
    ]);

    $data['user_id'] = (int) Auth::id();
    DeanLessonTracker::create($data);

    return back()->with('success', 'Lesson tracker row added.');
  }

  public function storeScorecard(Request $request)
  {
    $data = $request->validate([
      'category' => 'required|string|max:255',
      'covers' => 'nullable|string',
      'max_score' => 'nullable|numeric|min:0',
      'score_given' => 'nullable|numeric|min:0',
      'verified_by' => 'nullable|string|max:255',
      'reviewed_by' => 'nullable|string|max:255',
      'remarks' => 'nullable|string',
    ]);

    $data['user_id'] = (int) Auth::id();
    DeanPerformanceScorecard::create($data);

    return back()->with('success', 'Scorecard row added.');
  }

  public function storeTask(Request $request)
  {
    $data = $request->validate([
      'task' => 'required|string|max:255',
      'category' => 'nullable|string|max:100',
      'due_date' => 'nullable|date',
      'priority' => 'nullable|string|max:50',
      'assigned_by' => 'nullable|string|max:100',
      'status' => 'nullable|string|max:50',
      'evidence_remarks' => 'nullable|string',
    ]);

    $data['user_id'] = (int) Auth::id();
    DeanTask::create($data);

    return back()->with('success', 'Task added.');
  }

  public function upsertComparative(Request $request)
  {
    $data = $request->validate([
      'metric_code' => 'required|string|max:100',
      'title' => 'required|string|max:255',
      'remarks' => 'nullable|string',
      'status' => 'nullable|string|max:50',
    ]);

    DeanComparativeReport::updateOrCreate(
      [
        'user_id' => (int) Auth::id(),
        'metric_code' => $data['metric_code'],
      ],
      [
        'title' => $data['title'],
        'remarks' => $data['remarks'] ?? null,
        'status' => $data['status'] ?? 'open',
      ]
    );

    return back()->with('success', 'Comparative report row saved.');
  }

  public function upsertHod360Followup(Request $request)
  {
    $validMetricCodes = collect($this->buildHod360Snapshot(null))
      ->pluck('metric_code')
      ->map(fn($code) => (string) $code)
      ->values()
      ->all();

    $data = $request->validate([
      'metric_code' => 'required|string|in:' . implode(',', $validMetricCodes),
      'title' => 'required|string|max:255',
      'remarks' => 'nullable|string',
      'status' => 'nullable|string|max:50',
    ]);

    DeanComparativeReport::updateOrCreate(
      [
        'user_id' => (int) Auth::id(),
        'metric_code' => $data['metric_code'],
      ],
      [
        'title' => $data['title'],
        'remarks' => $data['remarks'] ?? null,
        'status' => $data['status'] ?? 'open',
      ]
    );

    return back()->with('success', 'HoD360 follow-up saved.');
  }

  public function destroyAnnualPlan($id)
  {
    return $this->destroyOwnedRow(DeanAnnualPlan::query(), $id, 'Annual plan row removed.');
  }

  public function destroyWeeklyProgress($id)
  {
    return $this->destroyOwnedRow(DeanWeeklyProgress::query(), $id, 'Weekly progress row removed.');
  }

  public function destroyLessonTracker($id)
  {
    return $this->destroyOwnedRow(DeanLessonTracker::query(), $id, 'Lesson tracker row removed.');
  }

  public function destroyScorecard($id)
  {
    return $this->destroyOwnedRow(DeanPerformanceScorecard::query(), $id, 'Scorecard row removed.');
  }

  public function destroyTask($id)
  {
    return $this->destroyOwnedRow(DeanTask::query(), $id, 'Task removed.');
  }

  public function eventsOverview()
  {
    return view('dean-office.events.overview', [
      'summary' => $this->eventRepo->summary(),
      'programs' => $this->eventRepo->eventProgramRows(),
      'departmentActivities' => $this->eventRepo->departmentActivityRows(),
    ]);
  }

  public function eventsCalendar()
  {
    $programs = $this->eventRepo->eventProgramRows();

    $calendarRows = $programs->map(function ($program) {
      return [
        'event_title' => $program->event->title ?? 'N/A',
        'program_title' => $program->title ?? 'N/A',
        'start_date' => $program->event->start_date ?? null,
        'end_date' => $program->event->end_date ?? null,
        'participants_count' => (int) ($program->participants_count ?? 0),
      ];
    })->values();

    return view('dean-office.events.calendar', [
      'calendarRows' => $calendarRows,
    ]);
  }

  public function eventsFeatureBoard()
  {
    return view('dean-office.events.feature-board', [
      'summary' => $this->eventRepo->summary(),
    ]);
  }

  public function departmentActivities(Request $request)
  {
    $status = trim((string) $request->query('status', ''));
    $type = trim((string) $request->query('type', ''));
    $subjectId = (int) $request->query('subject_id', 0);
    $search = strtolower(trim((string) $request->query('q', '')));

    $rows = $this->eventRepo->departmentActivityRows();

    $filteredRows = $rows->filter(function ($row) use ($status, $type, $subjectId, $search) {
      if ($status !== '' && strtolower((string) ($row->status ?? '')) !== strtolower($status)) {
        return false;
      }

      if ($type !== '' && strtolower((string) ($row->activity_type ?? '')) !== strtolower($type)) {
        return false;
      }

      if ($subjectId > 0 && (int) ($row->subject_id ?? 0) !== $subjectId) {
        return false;
      }

      if ($search !== '') {
        $haystack = strtolower(implode(' ', [
          (string) ($row->title ?? ''),
          (string) ($row->activity_type ?? ''),
          (string) ($row->description ?? ''),
          (string) ($row->venue ?? ''),
          (string) ($row->organizer_name ?? ''),
          (string) ($row->subject->title ?? ''),
        ]));

        if (!str_contains($haystack, $search)) {
          return false;
        }
      }

      return true;
    })->values();

    $summary = [
      'total' => (int) $filteredRows->count(),
      'planned' => (int) $filteredRows->where('status', 'planned')->count(),
      'ongoing' => (int) $filteredRows->where('status', 'ongoing')->count(),
      'completed' => (int) $filteredRows->where('status', 'completed')->count(),
      'cancelled' => (int) $filteredRows->where('status', 'cancelled')->count(),
      'participants' => (int) $filteredRows->sum(fn($row) => (int) ($row->actual_participants ?? 0)),
      'budget' => round((float) $filteredRows->sum(fn($row) => (float) ($row->budget ?? 0)), 2),
      'expense' => round((float) $filteredRows->sum(fn($row) => (float) ($row->actual_expense ?? 0)), 2),
    ];

    $types = $rows->pluck('activity_type')->filter()->map(fn($value) => (string) $value)->unique()->sort()->values();
    $subjects = $rows->map(function ($row) {
      return [
        'id' => (int) ($row->subject_id ?? 0),
        'title' => (string) ($row->subject->title ?? 'N/A'),
      ];
    })->filter(fn($subject) => $subject['id'] > 0)->unique('id')->sortBy('title')->values();

    return view('dean-office.department-activities.index', [
      'rows' => $filteredRows,
      'summary' => $summary,
      'types' => $types,
      'subjects' => $subjects,
      'filters' => [
        'status' => $status,
        'type' => $type,
        'subject_id' => $subjectId,
        'q' => $request->query('q', ''),
      ],
    ]);
  }

  private function completionForCategory($plans, string $category): float
  {
    $rows = $plans->filter(function ($plan) use ($category) {
      return strtolower((string) ($plan->category ?? '')) === $category;
    });

    $count = $rows->count();
    if ($count === 0) {
      return 0;
    }

    $done = $rows->filter(function ($plan) {
      return in_array(strtolower((string) ($plan->status ?? '')), ['done', 'completed', 'verified'], true);
    })->count();

    return round(($done / $count) * 100, 2);
  }

  private function buildScoreSummary($scorecards): array
  {
    $totalMax = round((float) $scorecards->sum('max_score'), 2);
    $totalGiven = round((float) $scorecards->sum('score_given'), 2);

    $groups = [
      'administrative' => ['Administrative Responsibilities', 'Professional Development', 'Report & Compliance', 'Administrative & Academic Management', 'Discipline, Governance & Leadership'],
      'academic' => ['Teaching Output', 'Learning & Evaluation', 'Co-curricular Activities', 'Research', 'Research, Innovation & Quality'],
    ];

    $adminGiven = round((float) $scorecards->whereIn('category', $groups['administrative'])->sum('score_given'), 2);
    $academicGiven = round((float) $scorecards->whereIn('category', $groups['academic'])->sum('score_given'), 2);

    return [
      'total_max' => $totalMax,
      'total_given' => $totalGiven,
      'admin_given' => $adminGiven,
      'academic_given' => $academicGiven,
    ];
  }

  private function buildHod360Snapshot(?Faculty $faculty): array
  {
    $campusId = (int) ($faculty->CAMPUS_ID ?? 0);

    $subjectsQuery = Subject::query();
    if ($campusId > 0 && Schema::hasColumn('subjects', 'campus_id')) {
      $subjectsQuery->where('campus_id', $campusId);
    }
    $totalSubjects = (int) $subjectsQuery->count();

    $combinationQuery = SubjectHasStudentProgam::query();
    if ($campusId > 0 && Schema::hasColumn('subject_has_student_progams', 'campus_id')) {
      $combinationQuery->where('campus_id', $campusId);
    }

    $programCombinationsOffered = (int) (clone $combinationQuery)->count();
    $ugCombinations = (int) (clone $combinationQuery)->whereRaw("UPPER(TRIM(COALESCE(program_type, 'UG'))) = 'UG'")->count();
    $pgCombinations = (int) (clone $combinationQuery)->whereRaw("UPPER(TRIM(COALESCE(program_type, 'UG'))) = 'PG'")->count();

    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
    $curriculumQuery = DB::table($curriculumTable);
    if (Schema::hasColumn($curriculumTable, 'deleted_at')) {
      $curriculumQuery->whereNull('deleted_at');
    }
    if (Schema::hasColumn($curriculumTable, 'is_active')) {
      $curriculumQuery->where('is_active', 1);
    }

    if ($campusId > 0 && Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
      $campusCombinationIds = SubjectHasStudentProgam::query()
        ->when(Schema::hasColumn('subject_has_student_progams', 'campus_id'), function ($query) use ($campusId) {
          $query->where('campus_id', $campusId);
        })
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->values();

      if ($campusCombinationIds->isNotEmpty()) {
        $curriculumQuery->whereIn('program_combo_refid', $campusCombinationIds->all());
      } else {
        $curriculumQuery->whereRaw('1=0');
      }
    }

    $curriculumRowsCount = (int) (clone $curriculumQuery)->count();
    $curriculumCombinationsCount = Schema::hasColumn($curriculumTable, 'program_combo_refid')
      ? (int) (clone $curriculumQuery)->distinct('program_combo_refid')->count('program_combo_refid')
      : 0;

    $teachingAssignmentQuery = TeachingAssignment::query();
    if (Schema::hasColumn((new TeachingAssignment())->getTable(), 'is_active')) {
      $teachingAssignmentQuery->where('is_active', 1);
    }

    if ($campusId > 0 && Schema::hasColumn('subjects', 'campus_id')) {
      $teachingAssignmentQuery->whereHas('subject', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }

    $teachingAssignmentsTotal = (int) (clone $teachingAssignmentQuery)->count();
    $teachingAssignmentsWithFaculty = (int) (clone $teachingAssignmentQuery)->where('faculty_id', '>', 0)->count();
    $teachingAssignmentsWithoutFaculty = max(0, $teachingAssignmentsTotal - $teachingAssignmentsWithFaculty);

    $coFacultyRows = (int) DB::table('teaching_assignment_faculties')
      ->where('teaching_role', 'Co-Faculty')
      ->count();

    $publishedSyllabusQuery = SyllabusManager::query()->where('status', 'published');
    if ($campusId > 0 && Schema::hasColumn('subjects', 'campus_id')) {
      $publishedSyllabusQuery->whereHas('subject', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }
    $publishedSyllabusRows = (int) $publishedSyllabusQuery->count();

    $routineQuery = SubjectHasRoutine::query();
    if ($campusId > 0 && Schema::hasColumn('subjects', 'campus_id')) {
      $routineQuery->whereHas('syllabus.subject', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }
    $routinesTotal = (int) $routineQuery->count();

    return [
      ['metric_code' => 'hod360_subjects_total', 'title' => 'Total Subjects Under Follow-up', 'value' => (string) $totalSubjects],
      ['metric_code' => 'hod360_program_combinations', 'title' => 'Program Combinations Offered (UG + PG)', 'value' => (string) $programCombinationsOffered],
      ['metric_code' => 'hod360_program_combinations_ug', 'title' => 'UG Program Combinations Offered', 'value' => (string) $ugCombinations],
      ['metric_code' => 'hod360_program_combinations_pg', 'title' => 'PG Program Combinations Offered', 'value' => (string) $pgCombinations],
      ['metric_code' => 'hod360_curriculum_rows', 'title' => 'Curricula Mapping Rows (Active)', 'value' => (string) $curriculumRowsCount],
      ['metric_code' => 'hod360_curriculum_combinations', 'title' => 'Program Combinations with Curriculum Coverage', 'value' => (string) $curriculumCombinationsCount],
      ['metric_code' => 'hod360_teaching_assignments_total', 'title' => 'Teaching Assignments (Active)', 'value' => (string) $teachingAssignmentsTotal],
      ['metric_code' => 'hod360_teaching_assignments_with_faculty', 'title' => 'Teaching Assignments with Primary Faculty', 'value' => (string) $teachingAssignmentsWithFaculty],
      ['metric_code' => 'hod360_teaching_assignments_without_faculty', 'title' => 'Teaching Assignments Pending Faculty Allocation', 'value' => (string) $teachingAssignmentsWithoutFaculty],
      ['metric_code' => 'hod360_teaching_assignments_co_faculty', 'title' => 'Co-Faculty Assignment Rows', 'value' => (string) $coFacultyRows],
      ['metric_code' => 'hod360_published_syllabus_rows', 'title' => 'Published Syllabus Rows', 'value' => (string) $publishedSyllabusRows],
      ['metric_code' => 'hod360_timetable_routines', 'title' => 'Timetable Routines', 'value' => (string) $routinesTotal],
    ];
  }

  private function destroyOwnedRow($query, $id, string $message)
  {
    $row = $query->where('id', (int) $id)
      ->where('user_id', (int) Auth::id())
      ->firstOrFail();

    DB::transaction(function () use ($row) {
      $row->delete();
    });

    return back()->with('success', $message);
  }
}
