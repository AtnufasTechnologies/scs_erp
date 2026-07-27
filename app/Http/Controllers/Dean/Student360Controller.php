<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\StudentMaster;
use App\Repositories\Dean\Student360Repository;
use App\Services\Dean\CampusContextService;
use Illuminate\Http\Request;

class Student360Controller extends Controller
{
  public function __construct(
    protected Student360Repository $student360Repo,
    protected CampusContextService $campusContext,
  ) {}

  public function index(Request $request)
  {
    $studentId = (int) $request->query('student_id', 0);
    $studentsQuery = StudentMaster::select('id', 'first_name', 'last_name', 'roll_no')->orderBy('first_name')->limit(1000);
    $this->campusContext->applyStudentCampus($studentsQuery);
    $students = $studentsQuery->get();

    if ($studentId <= 0) {
      return view('student-affairs.students.student-360', [
        'students' => $students,
        'profile' => null,
      ]);
    }

    $selectedStudent = StudentMaster::where('id', $studentId);
    $this->campusContext->applyStudentCampus($selectedStudent);
    if (!$selectedStudent->exists()) {
      abort(403, 'Selected student is outside your assigned campus.');
    }

    $profile = $this->student360Repo->profile($studentId);

    return view('student-affairs.students.student-360', [
      'students' => $students,
      'profile' => $profile,
    ]);
  }
}
