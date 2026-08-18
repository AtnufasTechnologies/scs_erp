<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
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
    $startDate = trim((string) $request->query('start_date', ''));
    $allowedStatuses = ['all', 'upcoming', 'live', 'completed'];
    if (!in_array($selectedStatus, $allowedStatuses, true)) {
      $selectedStatus = 'all';
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
      });

    $listingQuery = clone $filteredBaseQuery;
    $this->applyStatusFilter($listingQuery, $selectedStatus, $now);

    if ($startDateValue !== null) {
      $listingQuery->whereDate('open_at', $startDateValue);
    }

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

    $quizzes->getCollection()->transform(function ($quiz) {
      $quiz->expected_students_count = $this->expectedStudentCountForQuiz($quiz);
      return $quiz;
    });

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
      'startDate' => $startDate,
      'statusCounts' => $statusCounts,
      'role' => $role,
      'canFilterDepartments' => $role === 'principal',
      'monitorIndexRoute' => $role === 'principal' ? 'principal.quizzes.index' : 'department.quizzes.index',
      'monitorResultsRoute' => $role === 'principal' ? 'principal.quizzes.results' : 'department.quizzes.results',
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
      'monitorIndexRoute' => $role === 'principal' ? 'principal.quizzes.index' : 'department.quizzes.index',
    ]);
  }

  private function resolveAuthorizedRole(): string
  {
    $role = (string) UserHasRole::where('user_id', Auth::id())->value('role_name');

    if (!in_array($role, ['principal', 'dept-admin-erp'], true)) {
      abort(403, 'Unauthorized access.');
    }

    return $role;
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

    $candidateAssignmentIds = $routineQuery
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
      })
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

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
    $rosterData = $this->resolveQuizEligibleStudents($quiz);
    return (int) collect($rosterData['students'] ?? [])->count();
  }
}
