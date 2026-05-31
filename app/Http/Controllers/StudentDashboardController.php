<?php

namespace App\Http\Controllers;

use App\Models\InterMark;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubUnitStudentFeedback;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Registration;
use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\Student;
use App\Models\StudentMasterUserPivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
  /**
   * Resolve the logged-in student's StudentMaster record.
   */
  private function getStudent()
  {
    return Auth::user()->student_id;
  }

  /**
   * Student dashboard — overview stats.
   */
  /*
  public function index()
  {
    $student = $this->getStudent();
    $studentId = $student->id;

    // Attendance overview
    $attendanceRaw = StudentAttendance::where('student_id', $studentId)->get();
    $totalClasses = $attendanceRaw->count();
    $presentCount = $attendanceRaw->where('status', 'present')->count();
    $attendancePct = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 0;

    // Internal marks count
    $internalMarksCount = InterMark::where('student_id', $studentId)->count();

    // Exam results
    $examStudent = ExamStudent::where('erp_student_id', $studentId)->first();
    $examResultsCount = 0;
    if ($examStudent) {
      $examResultsCount = Result::where('exam_student_id', $examStudent->id)
        ->where('is_published', true)
        ->count();
    }

    // Courses enrolled
    $coursesCount = StudentCourseInfo::where('student_id', $studentId)->count();

    // Pending feedback (completed subunits not yet rated by this student)
    $pendingFeedbackCount = $this->getPendingFeedbackCount($studentId, $student->batch);

    // Fetch timetable data
    $timetableData = $this->getTimetableData($student->batch);

    return view('student.dashboard', [
      'student'              => $student,
      'totalClasses'         => $totalClasses,
      'presentCount'         => $presentCount,
      'attendancePct'        => $attendancePct,
      'internalMarksCount'   => $internalMarksCount,
      'examResultsCount'     => $examResultsCount,
      'coursesCount'         => $coursesCount,
      'pendingFeedbackCount' => $pendingFeedbackCount,
      'timetableData'        => $timetableData,
    ]);
  }
  */
  /**
   * Student's full profile — mirrors admin std-profile view.
   */
  public function index()
  {
    $studentId = $this->getStudent();


    $student =  StudentMaster::where('id', $studentId)->with([
      'religionmaster:id,name',
      'deptmaster:id,department_code,name',
      'campusmaster:id,slug,name',
      'nationalitymaster:id,name',
      'usertype:id,name',
      'bloodgroup',
      'batchmaster:id,batch_name',
      'programgroup.programInfo',
      'feepayment.feepaymentinfo:id,quarter_title',
      'feepayment.gatewaytype',
    ])->firstOrFail();

    // Courses with semester and course type
    $studentCourses = StudentCourseInfo::with([
      'coursemaster.semestermaster:id,title',
      'coursemaster.coursetypemaster:id,title,description',
    ])
      ->where('student_id', $studentId)
      ->get();

    // Group courses by semester for semester-wise display
    $coursesBySemester = $studentCourses
      ->sortBy(fn($c) => $c->coursemaster?->semester_id ?? 999)
      ->groupBy(fn($c) => $c->coursemaster?->semestermaster?->title ?? ('Sem ' . ($c->semester ?? '?')));

    // Timetable
    $timetable = SubjectHasRoutine::where('batch_id', $student->batch)
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

    // Attendance per course
    $attendanceRaw = StudentAttendance::where('student_id', $studentId)
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

    // Internal marks
    $internalMarks = InterMark::where('student_id', $studentId)
      ->with([
        'course:id,course_title,course_code',
        'semester:id,title',
      ])
      ->get();

    // Exam results
    $examStudent = ExamStudent::where('erp_student_id', $studentId)->first();
    $examResults = collect();
    $resultsBySemester = collect();
    if ($examStudent) {
      $examResults = Result::where('exam_student_id', $examStudent->id)
        ->where('is_published', true)
        ->with(['examSession', 'resultSubjects'])
        ->orderBy('exam_session_id')
        ->get();

      // Build per-session qualified / backlog summary
      foreach ($examResults as $result) {
        $semKey = 'Semester ' . ($result->examSession?->semester ?? '?') . ' — ' . ($result->examSession?->academic_year ?? '');
        $qualified = $result->resultSubjects->where('result_status', 'pass')->values();
        $backlogs  = $result->resultSubjects->where('result_status', '!=', 'pass')->values();
        $resultsBySemester[$semKey] = [
          'result'    => $result,
          'qualified' => $qualified,
          'backlog'   => $backlogs,
        ];
      }
    }

    // Exam registrations (all, latest first)
    $examRegistrations = Registration::where('erp_student_id', $studentId)
      ->with(['examSession', 'registrationSubjects.examSubject.master'])
      ->orderByDesc('registered_at')
      ->get();

    $latestRegistration = $examRegistrations->first();

    return view('student.profile', [
      'data'               => $student,
      'studentCourses'     => $studentCourses,
      'coursesBySemester'  => $coursesBySemester,
      'timetableByDay'     => $timetableByDay,
      'attendanceSummary'  => $attendanceSummary,
      'internalMarks'      => $internalMarks,
      'examResults'        => $examResults,
      'resultsBySemester'  => $resultsBySemester,
      'examStudent'        => $examStudent,
      'examRegistrations'  => $examRegistrations,
      'latestRegistration' => $latestRegistration,
    ]);
  }

  /**
   * List all completed syllabus subunits for which the student can give feedback.
   */
  public function feedbackList()
  {
    $student = $this->getStudent();

    // Get all syllabus managers for student's batch
    $syllabusManagers = SyllabusManager::where('batch_id', $student->batch)
      ->with([
        'subject:id,title',
        'syllabusSubunits.csoSubunit',
      ])
      ->get();

    // Collect all completed subunits with student's existing feedback
    $completedSubunits = collect();
    foreach ($syllabusManagers as $manager) {
      foreach ($manager->syllabusSubunits->where('is_completed', 1) as $subunit) {
        $existing = SubUnitStudentFeedback::where('syllabus_subunit_id', $subunit->id)
          ->where('student_id', $student->id)
          ->first();

        $completedSubunits->push([
          'subunit'          => $subunit,
          'subject_title'    => $manager->subject->title ?? 'N/A',
          'existing_feedback' => $existing,
        ]);
      }
    }

    return view('student.feedback', [
      'student'          => $student,
      'completedSubunits' => $completedSubunits,
    ]);
  }

  /**
   * Submit or update feedback for a completed subunit.
   */
  public function submitFeedback(Request $request, int $subunitId)
  {
    $student = $this->getStudent();

    $request->validate([
      'rating'   => 'nullable|integer|min:1|max:5',
      'feedback' => 'nullable|string|max:1000',
    ]);

    // Verify the subunit is completed and belongs to student's batch
    $subunit = SyllabusSubunit::where('id', $subunitId)
      ->where('is_completed', 1)
      ->with('syllabusManager')
      ->firstOrFail();

    if ($subunit->syllabusManager->batch_id != $student->batch) {
      return redirect()->route('student.feedback.list')
        ->with('error', 'You are not authorized to give feedback for this subunit.');
    }

    SubUnitStudentFeedback::updateOrCreate(
      [
        'syllabus_subunit_id' => $subunitId,
        'student_id'          => $student->id,
      ],
      [
        'rating'   => $request->rating,
        'feedback' => $request->feedback,
      ]
    );

    return redirect()->route('student.feedback.list')
      ->with('success', 'Feedback submitted successfully!');
  }

  /**
   * Count completed subunits not yet reviewed by this student.
   */
  private function getPendingFeedbackCount(int $studentId, int $batchId): int
  {
    $managers = SyllabusManager::where('batch_id', $batchId)
      ->with('syllabusSubunits')
      ->get();

    $completedIds = collect();
    foreach ($managers as $manager) {
      $completedIds = $completedIds->concat(
        $manager->syllabusSubunits->where('is_completed', 1)->pluck('id')
      );
    }

    if ($completedIds->isEmpty()) {
      return 0;
    }

    $alreadyReviewed = SubUnitStudentFeedback::where('student_id', $studentId)
      ->whereIn('syllabus_subunit_id', $completedIds)
      ->count();

    return max(0, $completedIds->count() - $alreadyReviewed);
  }

  /**
   * Fetch and organize timetable data for student's batch.
   */
  private function getTimetableData(int $batchId): array
  {
    // Fetch all timetable entries for the batch
    $routines = SubjectHasRoutine::where('batch_id', $batchId)
      ->with([
        'weekdaymaster:id,title',
        'hourmaster:id,title',
        'lecturehallmaster:id,title',
        'faculty:id,FIRST_NAME,LAST_NAME',
        'subjectCourse.courseMaster:id,course_title,course_code',
        'subjectCourse.subject:id,title',
      ])
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get();

    // Get all weekdays and hours for grid structure
    $weekdays = \App\Models\Weekday::orderBy('id')->get();
    $hours = \App\Models\HourMaster::orderBy('id')->get();

    // Organize timetable by day and hour
    $timetable = [];
    foreach ($weekdays as $weekday) {
      $timetable[$weekday->title] = [];
      foreach ($hours as $hour) {
        $timetable[$weekday->title][$hour->title] = null;
      }
    }

    // Fill in the timetable data
    foreach ($routines as $routine) {
      $day = $routine->weekdaymaster->title ?? 'Unknown';
      $hour = $routine->hourmaster->title ?? 'Unknown';

      $facultyName = 'TBA';
      if ($routine->faculty) {
        $facultyName = trim(($routine->faculty->FIRST_NAME ?? '') . ' ' . ($routine->faculty->LAST_NAME ?? ''));
      }

      $courseName = $routine->subjectCourse?->courseMaster?->course_title ??
        $routine->subjectCourse?->subject?->title ?? 'N/A';

      $lectureHall = $routine->lecturehallmaster?->title ?? 'TBA';

      if (isset($timetable[$day][$hour])) {
        $timetable[$day][$hour] = [
          'course' => $courseName,
          'faculty' => $facultyName,
          'hall' => $lectureHall,
        ];
      }
    }

    return [
      'weekdays' => $weekdays,
      'hours' => $hours,
      'schedule' => $timetable,
    ];
  }
}
