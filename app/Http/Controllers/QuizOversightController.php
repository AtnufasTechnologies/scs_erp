<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class QuizOversightController extends Controller
{
  public function index(Request $request)
  {
    $role = (string) UserHasRole::where('user_id', Auth::id())->value('role_name');

    if (!in_array($role, ['principal', 'dept-admin-erp'], true)) {
      abort(403, 'Unauthorized access.');
    }

    $selectedDepartment = trim((string) $request->query('department', ''));
    $departmentLabelColumns = collect(['name', 'title', 'subject_name'])
      ->filter(fn($column) => Schema::hasColumn('department_masters', $column))
      ->values();

    $baseQuery = Quiz::query();
    if ($role === 'dept-admin-erp') {
      $subjectId = (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
      $facultyIds = SubjectFacultyMaster::where('subject_id', $subjectId)->pluck('faculty_id');

      if ($subjectId <= 0 || $facultyIds->isEmpty()) {
        $baseQuery->whereRaw('1 = 0');
      } else {
        $baseQuery->whereIn('faculty_id', $facultyIds->all());
      }

      // Department users are always limited to their own department scope.
      $selectedDepartment = '';
    }

    $quizzes = (clone $baseQuery)->with([
      'course:id,course_title,course_code',
      'subject:id,title',
      'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME,DEPARTMENT',
      'faculty.department',
      'creator:id,name',
      'questions:id,quiz_id,question_text,position',
    ])
      ->withCount('questions')
      ->when($selectedDepartment !== '' && $departmentLabelColumns->isNotEmpty(), function ($query) use ($selectedDepartment, $departmentLabelColumns) {
        $query->whereHas('faculty.department', function ($deptQuery) use ($selectedDepartment, $departmentLabelColumns) {
          $firstColumn = $departmentLabelColumns->first();
          $deptQuery->where($firstColumn, $selectedDepartment);

          foreach ($departmentLabelColumns->slice(1) as $column) {
            $deptQuery->orWhere($column, $selectedDepartment);
          }
        });
      })
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

    return view('quiz.oversight.index', [
      'quizzes' => $quizzes,
      'departmentOptions' => $departmentOptions,
      'selectedDepartment' => $selectedDepartment,
      'role' => $role,
      'canFilterDepartments' => $role === 'principal',
    ]);
  }
}
