<?php

namespace App\Http\Controllers;

use App\Models\InternalMarkLog;
use App\Models\ProgramCourseMaster;
use App\Models\Semester;
use Illuminate\Http\Request;

class CoeInternalMarksReviewController extends Controller
{
  public function index(Request $request)
  {
    $query = InternalMarkLog::with([
      'student:id,first_name,last_name,roll_no,register_no',
      'course',
      'changedByUser:id,name,email',
    ])->orderBy('created_at', 'desc');

    if ($request->filled('course_id')) {
      $query->where('course_id', $request->course_id);
    }
    if ($request->filled('semester')) {
      $query->where('semester', $request->semester);
    }
    if ($request->filled('date_from')) {
      $query->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
      $query->whereDate('created_at', '<=', $request->date_to);
    }
    if ($request->filled('changed_by')) {
      $query->where('changed_by', $request->changed_by);
    }

    $logs = $query->paginate(50);

    $courses = ProgramCourseMaster::orderBy('course_title')->get();
    $semesters = Semester::all();

    return view('coe.internal-marks-review.index', compact('logs', 'courses', 'semesters'));
  }
}
