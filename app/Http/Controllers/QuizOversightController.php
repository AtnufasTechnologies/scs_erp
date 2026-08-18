<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
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
    $completedFrom = trim((string) $request->query('completed_from', ''));
    $completedTo = trim((string) $request->query('completed_to', ''));
    $allowedStatuses = ['all', 'upcoming', 'live', 'completed'];
    if (!in_array($selectedStatus, $allowedStatuses, true)) {
      $selectedStatus = 'all';
    }

    $completedFromDate = null;
    $completedToDate = null;

    if ($completedFrom !== '') {
      try {
        $completedFromDate = Carbon::parse($completedFrom)->startOfDay();
      } catch (\Throwable $e) {
        $completedFrom = '';
      }
    }

    if ($completedTo !== '') {
      try {
        $completedToDate = Carbon::parse($completedTo)->endOfDay();
      } catch (\Throwable $e) {
        $completedTo = '';
      }
    }

    if ($completedFromDate && $completedToDate && $completedFromDate->gt($completedToDate)) {
      [$completedFromDate, $completedToDate] = [$completedToDate->copy()->startOfDay(), $completedFromDate->copy()->endOfDay()];
      $completedFrom = $completedFromDate->toDateString();
      $completedTo = $completedToDate->toDateString();
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
    $this->applyStatusFilter($listingQuery, $selectedStatus, $now, $completedFromDate, $completedToDate);

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
      'upcoming' => $this->countByStatus($filteredBaseQuery, 'upcoming', $now, null, null),
      'live' => $this->countByStatus($filteredBaseQuery, 'live', $now, null, null),
      'completed' => $this->countByStatus($filteredBaseQuery, 'completed', $now, null, null),
    ];

    return view('quiz.oversight.index', [
      'quizzes' => $quizzes,
      'departmentOptions' => $departmentOptions,
      'selectedDepartment' => $selectedDepartment,
      'selectedStatus' => $selectedStatus,
      'completedFrom' => $completedFrom,
      'completedTo' => $completedTo,
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

  private function applyStatusFilter(Builder $query, string $status, $now, ?Carbon $completedFromDate, ?Carbon $completedToDate): void
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
          $closeQuery->whereNull('close_at')->orWhere('close_at', '>=', $now);
        });
      return;
    }

    if ($status === 'completed') {
      $query
        ->whereNotNull('close_at')
        ->where('close_at', '<', $now);

      if ($completedFromDate) {
        $query->where('close_at', '>=', $completedFromDate);
      }

      if ($completedToDate) {
        $query->where('close_at', '<=', $completedToDate);
      }
    }
  }

  private function countByStatus(Builder $baseQuery, string $status, $now, ?Carbon $completedFromDate, ?Carbon $completedToDate): int
  {
    $query = clone $baseQuery;
    $this->applyStatusFilter($query, $status, $now, $completedFromDate, $completedToDate);
    return $query->count();
  }
}
