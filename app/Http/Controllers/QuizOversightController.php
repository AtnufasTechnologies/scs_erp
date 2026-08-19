<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ProgramCourseMaster;
use App\Models\StudentCourseRoster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectHasRoutine;
use App\Models\UserHasRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuizOversightController extends Controller
{
  public function index(Request $request)
  {
    $role = $this->resolveAuthorizedRole();

    $selectedDepartment = trim((string) $request->query('department', ''));
    $selectedStatus = trim((string) $request->query('status', 'all'));
    $selectedCourseCode = strtoupper(trim((string) $request->query('course_code', '')));
    $startDate = trim((string) $request->query('start_date', ''));
    $groupBy = trim((string) $request->query('group_by', 'none'));
    $allowedStatuses = ['all', 'upcoming', 'live', 'completed'];
    $allowedGroupBy = ['none', 'start_time'];
    if (!in_array($selectedStatus, $allowedStatuses, true)) {
      $selectedStatus = 'all';
    }
    if (!in_array($groupBy, $allowedGroupBy, true)) {
      $groupBy = 'none';
    }

    $startDateValue = null;
    if ($startDate !== '') {
      try {
        $startDateValue = Carbon::parse($startDate)->toDateString();
        $startDate = $startDateValue;
      } catch (\Throwable $e) {
        $startDate = '';
      }
    }

    $now = now();
    $departmentLabelColumns = collect(['name', 'title', 'subject_name'])
      ->filter(fn($column) => Schema::hasColumn('department_masters', $column))
      ->values();

    $baseQuery = $this->scopedQuizQueryForRole($role);
    if ($role === 'dept-admin-erp') {
      // Department users are always limited to their own department scope.
      $selectedDepartment = '';
    }

    $filteredBaseQuery = (clone $baseQuery)
      ->when($selectedDepartment !== '' && $departmentLabelColumns->isNotEmpty(), function ($query) use ($selectedDepartment, $departmentLabelColumns) {
        $query->whereHas('faculty.department', function ($deptQuery) use ($selectedDepartment, $departmentLabelColumns) {
          $firstColumn = $departmentLabelColumns->first();
          $deptQuery->where($firstColumn, $selectedDepartment);

          foreach ($departmentLabelColumns->slice(1) as $column) {
            $deptQuery->orWhere($column, $selectedDepartment);
          }
        });
      })
      ->when($selectedCourseCode !== '', function ($query) use ($selectedCourseCode) {
        $query->whereHas('course', function ($courseQuery) use ($selectedCourseCode) {
          $courseQuery->whereRaw('UPPER(TRIM(course_code)) LIKE ?', ['%' . $selectedCourseCode . '%']);
        });
      });

    $listingQuery = clone $filteredBaseQuery;
    $this->applyStatusFilter($listingQuery, $selectedStatus, $now);

    if ($startDateValue !== null) {
      $listingQuery->whereDate('open_at', $startDateValue);
    }

    $quizRelations = [
      'course:id,course_title,course_code',
      'subject:id,title',
      'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME,DEPARTMENT',
      'faculty.department',
      'creator:id,name',
      'questions:id,quiz_id,question_text,position',
      'attempts:id,quiz_id,student_id,status,score,submitted_at',
    ];

    $quizzes = (clone $listingQuery)->with([
      'course:id,course_title,course_code',
      'subject:id,title',
      'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME,DEPARTMENT',
      'faculty.department',
      'creator:id,name',
      'questions:id,quiz_id,question_text,position',
      'attempts:id,quiz_id,student_id,status,score,submitted_at',
    ])
      ->withCount('questions')
      ->withCount([
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ])
      ->orderByDesc('id')
      ->paginate(15)
      ->withQueryString();

    $startTimeStudentMap = [];
    $groupedQuizzesByStartTime = collect();

    $quizzes->getCollection()->transform(function ($quiz) use (&$startTimeStudentMap) {
      $studentIds = $this->expectedStudentIdsForQuiz($quiz);
      $quiz->expected_students_count = $studentIds->count();

      if ($quiz->open_at) {
        $startAtKey = $quiz->open_at->format('Y-m-d H:i:s');

        if (!array_key_exists($startAtKey, $startTimeStudentMap)) {
          $startTimeStudentMap[$startAtKey] = collect();
        }

        $startTimeStudentMap[$startAtKey] = $startTimeStudentMap[$startAtKey]
          ->merge($studentIds)
          ->unique()
          ->values();
      }

      return $quiz;
    });

    if ($groupBy === 'start_time') {
      $allFilteredQuizzes = (clone $listingQuery)
        ->with($quizRelations)
        ->withCount('questions')
        ->withCount([
          'attempts as submitted_attempts_count' => function ($query) {
            $query->where('status', 'submitted');
          }
        ])
        ->orderBy('open_at')
        ->orderByDesc('id')
        ->get();

      $startTimeStudentMap = [];

      $allFilteredQuizzes->transform(function ($quiz) use (&$startTimeStudentMap) {
        $studentIds = $this->expectedStudentIdsForQuiz($quiz);
        $quiz->expected_students_count = $studentIds->count();

        if ($quiz->open_at) {
          $startAtKey = $quiz->open_at->format('Y-m-d H:i:s');

          if (!array_key_exists($startAtKey, $startTimeStudentMap)) {
            $startTimeStudentMap[$startAtKey] = collect();
          }

          $startTimeStudentMap[$startAtKey] = $startTimeStudentMap[$startAtKey]
            ->merge($studentIds)
            ->unique()
            ->values();
        }

        return $quiz;
      });

      $groupedQuizzesByStartTime = $allFilteredQuizzes
        ->groupBy(function ($quiz) {
          return $quiz->open_at ? $quiz->open_at->format('Y-m-d H:i:s') : 'NO_START_TIME';
        })
        ->map(function ($items, $startAtKey) {
          $startAtLabel = $startAtKey === 'NO_START_TIME'
            ? 'No Start Time'
            : Carbon::parse($startAtKey)->format('d M Y h:i A');

          return [
            'start_at' => $startAtKey === 'NO_START_TIME' ? null : $startAtKey,
            'start_at_label' => $startAtLabel,
            'quiz_count' => (int) $items->count(),
            'quizzes' => $items->values(),
          ];
        })
        ->sortBy(function ($group) {
          return $group['start_at'] ?? '9999-12-31 23:59:59';
        })
        ->values();
    }

    $startTimeAnalytics = collect($startTimeStudentMap)
      ->map(function ($studentIds, $startAt) {
        return [
          'start_at' => $startAt,
          'start_at_label' => Carbon::parse($startAt)->format('d M Y h:i A'),
          'unique_students' => (int) collect($studentIds)->count(),
        ];
      })
      ->sortBy('start_at')
      ->values();

    $totalUniqueStudentsByStartTime = (int) $startTimeAnalytics->sum('unique_students');

    $departmentOptions = (clone $baseQuery)
      ->whereNotNull('faculty_id')
      ->with(['faculty.department'])
      ->get()
      ->map(function ($quiz) {
        $dept = optional($quiz->faculty)->department;
        return trim((string) ($dept->name ?? $dept->title ?? $dept->subject_name ?? ''));
      })
      ->filter()
      ->unique()
      ->sort()
      ->values();

    $statusCounts = [
      'all' => (clone $filteredBaseQuery)->count(),
      'upcoming' => $this->countByStatus($filteredBaseQuery, 'upcoming', $now),
      'live' => $this->countByStatus($filteredBaseQuery, 'live', $now),
      'completed' => $this->countByStatus($filteredBaseQuery, 'completed', $now),
    ];

    return view('quiz.oversight.index', [
      'quizzes' => $quizzes,
      'departmentOptions' => $departmentOptions,
      'selectedDepartment' => $selectedDepartment,
      'selectedStatus' => $selectedStatus,
      'selectedCourseCode' => $selectedCourseCode,
      'startDate' => $startDate,
      'groupBy' => $groupBy,
      'statusCounts' => $statusCounts,
      'startTimeAnalytics' => $startTimeAnalytics,
      'totalUniqueStudentsByStartTime' => $totalUniqueStudentsByStartTime,
      'groupedQuizzesByStartTime' => $groupedQuizzesByStartTime,
      'role' => $role,
      'canFilterDepartments' => in_array($role, ['principal', 'itcell'], true),
      'showQuestionsInMonitor' => $role !== 'itcell',
      'monitorIndexRoute' => $this->monitorIndexRouteName($role),
      'monitorResultsRoute' => $this->monitorResultsRouteName($role),
    ]);
  }

  public function results(Request $request, int $quizId)
  {
    $role = $this->resolveAuthorizedRole();
    $baseQuery = $this->scopedQuizQueryForRole($role);

    $quiz = (clone $baseQuery)
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
        'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME,DEPARTMENT',
        'faculty.department',
        'creator:id,name',
      ])
      ->withCount([
        'questions',
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ])
      ->findOrFail($quizId);

    $attempts = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->with('student:id,first_name,last_name,roll_no,register_no')
      ->orderByDesc('score')
      ->orderByDesc('submitted_at')
      ->paginate(30)
      ->withQueryString();

    $attemptedStudentCount = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->distinct('student_id')
      ->count('student_id');

    $latestSubmissionAt = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->max('submitted_at');

    $avgScore = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->avg('score');

    return view('quiz.oversight.results', [
      'quiz' => $quiz,
      'attempts' => $attempts,
      'role' => $role,
      'attemptedStudentCount' => $attemptedStudentCount,
      'latestSubmissionAt' => $latestSubmissionAt,
      'averageScore' => $avgScore,
      'monitorIndexRoute' => $this->monitorIndexRouteName($role),
    ]);
  }

  public function backfillTeachingAssignmentByCourseCode(Request $request)
  {
    $role = $this->resolveAuthorizedRole();
    if ($role !== 'itcell') {
      abort(403, 'Only ITCELL can run this operation.');
    }

    $validated = $request->validate([
      'course_code' => 'required|string|max:120',
    ]);

    $rawCourseCode = (string) $validated['course_code'];
    $normalizedCourseCode = $this->normalizeCourseCode($rawCourseCode);
    if ($normalizedCourseCode === '') {
      return redirect()->route('itcell.quizzes.index')->with('error', 'Course code is required.');
    }

    $courseIds = ProgramCourseMaster::query()
      ->get(['id', 'course_code'])
      ->filter(function ($course) use ($normalizedCourseCode) {
        $courseCode = $this->normalizeCourseCode((string) ($course->course_code ?? ''));
        if ($courseCode === '') {
          return false;
        }

        return $courseCode === $normalizedCourseCode;
      })
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($courseIds->isEmpty()) {
      return redirect()
        ->route('itcell.quizzes.index')
        ->with('error', 'No course found for code ' . trim($rawCourseCode) . '.');
    }

    $matchedQuizCount = (int) Quiz::query()
      ->whereIn('course_id', $courseIds->all())
      ->count();

    $quizzes = Quiz::query()
      ->whereIn('course_id', $courseIds->all())
      ->where(function ($query) {
        $query->whereNull('teaching_assignment_id')
          ->orWhere('teaching_assignment_id', '<=', 0);
      })
      ->orderBy('id')
      ->get();

    if ($quizzes->isEmpty()) {
      return redirect()
        ->route('itcell.quizzes.index')
        ->with('success', 'No pending quiz backfill rows found for course code ' . trim($rawCourseCode) . '. Matched quizzes=' . $matchedQuizCount . '.');
    }

    $updated = 0;
    $skipped = 0;

    foreach ($quizzes as $quiz) {
      $resolvedAssignmentId = $this->resolveAssignmentIdForBackfill($quiz);
      if ($resolvedAssignmentId <= 0) {
        $skipped++;
        continue;
      }

      Quiz::query()
        ->where('id', (int) $quiz->id)
        ->update([
          'teaching_assignment_id' => $resolvedAssignmentId,
          'updated_at' => now(),
        ]);

      $updated++;
    }

    $message = 'Backfill completed for course code ' . trim($rawCourseCode)
      . '. updated=' . $updated
      . ', skipped=' . $skipped
      . ', scanned=' . $quizzes->count() . '.';

    return redirect()->route('itcell.quizzes.index')->with('success', $message);
  }

  private function resolveAuthorizedRole(): string
  {
    $rawRole = (string) UserHasRole::where('user_id', Auth::id())->value('role_name');

    if (in_array($rawRole, ['itcell', 'admin', 'super-admin'], true)) {
      return 'itcell';
    }

    if (!in_array($rawRole, ['principal', 'dept-admin-erp'], true)) {
      abort(403, 'Unauthorized access.');
    }

    return $rawRole;
  }

  private function scopedQuizQueryForRole(string $role): Builder
  {
    $query = Quiz::query();

    if ($role !== 'dept-admin-erp') {
      return $query;
    }

    $subjectId = (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
    $facultyIds = SubjectFacultyMaster::where('subject_id', $subjectId)->pluck('faculty_id');

    if ($subjectId <= 0 || $facultyIds->isEmpty()) {
      return $query->whereRaw('1 = 0');
    }

    return $query->whereIn('faculty_id', $facultyIds->all());
  }

  private function applyStatusFilter(Builder $query, string $status, $now): void
  {
    if ($status === 'upcoming') {
      $query->whereNotNull('open_at')->where('open_at', '>', $now);
      return;
    }

    if ($status === 'live') {
      $query
        ->whereNotNull('open_at')
        ->where('open_at', '<=', $now)
        ->where(function ($closeQuery) use ($now) {
          $closeQuery->whereNull('close_at')->orWhere('close_at', '>', $now);
        });
      return;
    }

    if ($status === 'completed') {
      $query
        ->whereNotNull('close_at')
        ->where('close_at', '<=', $now);
    }
  }

  private function countByStatus(Builder $baseQuery, string $status, $now): int
  {
    $query = clone $baseQuery;
    $this->applyStatusFilter($query, $status, $now);
    return $query->count();
  }

  private function queryFacultyAssignedRoutines(int $facultyId)
  {
    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    return SubjectHasRoutine::query()
      ->where(function ($query) use ($facultyId) {
        $query->where('faculty_id', $facultyId)
          ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
            $assignmentQuery->where('faculty_id', $facultyId)
              ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
                $facultyAssignmentQuery->where('faculty_id', $facultyId);
              })
              ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
                $coFacultyQuery->where('faculties.id', $facultyId);
              });
          });
      })
      ->when($hasTeachingAllocationLink, function ($query) use ($facultyId) {
        $query->orWhereHas('teachingAllocation', function ($assignmentQuery) use ($facultyId) {
          $assignmentQuery->where('faculty_id', $facultyId)
            ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
              $facultyAssignmentQuery->where('faculty_id', $facultyId);
            })
            ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
              $coFacultyQuery->where('faculties.id', $facultyId);
            });
        });
      });
  }

  private function resolveQuizEligibleStudents(Quiz $quiz): array
  {
    $hasTeachingAssignmentColumn = Schema::hasColumn('subject_has_routines', 'teaching_assignment_id');
    $hasTeachingAllocationColumn = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    if (!$hasTeachingAssignmentColumn && !$hasTeachingAllocationColumn) {
      return [
        'source' => 'roster',
        'students' => collect(),
      ];
    }

    $routineQuery = $this->queryFacultyAssignedRoutines((int) $quiz->faculty_id)
      ->where('syllabus_id', (int) $quiz->syllabus_id);

    if (Schema::hasColumn('subject_has_routines', 'deleted_at')) {
      $routineQuery->whereNull('deleted_at');
    }

    $selectColumns = [];
    if ($hasTeachingAssignmentColumn) {
      $selectColumns[] = 'teaching_assignment_id';
    }
    if ($hasTeachingAllocationColumn) {
      $selectColumns[] = 'teaching_allocation_id';
    }

    $candidateAssignmentIds = collect($routineQuery
      ->get($selectColumns)
      ->flatMap(function ($routine) use ($hasTeachingAssignmentColumn, $hasTeachingAllocationColumn) {
        $ids = [];

        if ($hasTeachingAssignmentColumn) {
          $ids[] = (int) ($routine->teaching_assignment_id ?? 0);
        }

        if ($hasTeachingAllocationColumn) {
          $ids[] = (int) ($routine->teaching_allocation_id ?? 0);
        }

        return $ids;
      }))
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($candidateAssignmentIds->isEmpty() && method_exists($routineQuery->getModel(), 'withTrashed')) {
      $legacyRoutineQuery = $this->queryFacultyAssignedRoutines((int) $quiz->faculty_id)
        ->withTrashed()
        ->where('syllabus_id', (int) $quiz->syllabus_id);

      $candidateAssignmentIds = collect($legacyRoutineQuery
        ->get($selectColumns)
        ->flatMap(function ($routine) use ($hasTeachingAssignmentColumn, $hasTeachingAllocationColumn) {
          $ids = [];

          if ($hasTeachingAssignmentColumn) {
            $ids[] = (int) ($routine->teaching_assignment_id ?? 0);
          }

          if ($hasTeachingAllocationColumn) {
            $ids[] = (int) ($routine->teaching_allocation_id ?? 0);
          }

          return $ids;
        }))
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();
    }

    $selectedAssignmentId = (int) ($quiz->teaching_assignment_id ?? 0);

    if ($selectedAssignmentId <= 0 && $candidateAssignmentIds->count() > 1) {
      $selectedAssignmentId = $this->inferQuizAssignmentIdFromRoster($quiz, $candidateAssignmentIds);
    }

    if ($selectedAssignmentId <= 0) {
      $selectedAssignmentId = (int) ($candidateAssignmentIds->first() ?? 0);
    }

    $assignmentIds = $selectedAssignmentId > 0
      ? collect([$selectedAssignmentId])
      : collect();

    if ($assignmentIds->isEmpty()) {
      return [
        'source' => 'roster',
        'students' => collect(),
      ];
    }

    $studentIds = StudentCourseRoster::query()
      ->whereIn('ta_id', $assignmentIds->all())
      ->where('course_id', (int) $quiz->course_id)
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();

    return [
      'source' => 'roster',
      'students' => $studentIds,
    ];
  }

  private function inferQuizAssignmentIdFromRoster(Quiz $quiz, $candidateAssignmentIds): int
  {
    $candidateIds = collect($candidateAssignmentIds)
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($candidateIds->count() < 2) {
      return 0;
    }

    $attemptedStudentIds = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->pluck('student_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($attemptedStudentIds->isEmpty()) {
      return 0;
    }

    $bestAssignmentId = StudentCourseRoster::query()
      ->whereIn('ta_id', $candidateIds->all())
      ->where('course_id', (int) $quiz->course_id)
      ->whereIn('student_id', $attemptedStudentIds->all())
      ->select('ta_id', DB::raw('COUNT(DISTINCT student_id) as matched_students'))
      ->groupBy('ta_id')
      ->orderByDesc('matched_students')
      ->orderBy('ta_id')
      ->value('ta_id');

    return (int) ($bestAssignmentId ?? 0);
  }

  private function expectedStudentCountForQuiz(Quiz $quiz): int
  {
    return $this->expectedStudentIdsForQuiz($quiz)->count();
  }

  private function expectedStudentIdsForQuiz(Quiz $quiz)
  {
    $rosterData = $this->resolveQuizEligibleStudents($quiz);

    return collect($rosterData['students'] ?? [])
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();
  }

  private function resolveAssignmentIdForBackfill(Quiz $quiz): int
  {
    $selectedAssignmentId = (int) ($quiz->teaching_assignment_id ?? 0);
    if ($selectedAssignmentId > 0) {
      return $selectedAssignmentId;
    }

    $candidateAssignmentIds = $this->candidateTeachingAssignmentIdsForQuiz($quiz, false);
    if ($candidateAssignmentIds->isEmpty()) {
      $candidateAssignmentIds = $this->candidateTeachingAssignmentIdsForQuiz($quiz, true);
    }

    if ($candidateAssignmentIds->isEmpty()) {
      return 0;
    }

    if ($candidateAssignmentIds->count() > 1) {
      $selectedAssignmentId = $this->inferQuizAssignmentIdFromRoster($quiz, $candidateAssignmentIds);
      if ($selectedAssignmentId > 0) {
        return $selectedAssignmentId;
      }
    }

    return (int) $candidateAssignmentIds->sort()->first();
  }

  private function candidateTeachingAssignmentIdsForQuiz(Quiz $quiz, bool $includeTrashed)
  {
    $query = SubjectHasRoutine::query()
      ->where('syllabus_id', (int) $quiz->syllabus_id)
      ->where('faculty_id', (int) $quiz->faculty_id)
      ->whereNotNull('teaching_assignment_id');

    if ($includeTrashed) {
      $query->withTrashed();
    } elseif (Schema::hasColumn('subject_has_routines', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    return $query
      ->pluck('teaching_assignment_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();
  }

  private function normalizeCourseCode(string $courseCode): string
  {
    return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($courseCode))) ?? '';
  }

  private function monitorIndexRouteName(string $role): string
  {
    if ($role === 'principal') {
      return 'principal.quizzes.index';
    }

    if ($role === 'itcell') {
      return 'itcell.quizzes.index';
    }

    return 'department.quizzes.index';
  }

  private function monitorResultsRouteName(string $role): string
  {
    if ($role === 'principal') {
      return 'principal.quizzes.results';
    }

    if ($role === 'itcell') {
      return 'itcell.quizzes.results';
    }

    return 'department.quizzes.results';
  }
}
