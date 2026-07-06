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
use App\Models\MentorshipGroup;
use App\Models\MentorshipGroupStudent;
use App\Models\MentorshipSession;
use App\Models\MentorshipSessionAttendance;
use App\Models\MentorshipAssignment;
use App\Models\MentorshipAssignmentSubmission;
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

    // Timetable: prefer student's mapped shift, then fallback to common/null
    $studentShift = strtolower(trim((string) ($student->programgroup?->programInfo?->shift ?? 'common')));
    if ($studentShift === '') {
      $studentShift = 'common';
    }

    $timetable = SubjectHasRoutine::where('batch_id', $student->batch)
      ->where(function ($q) use ($studentShift) {
        $q->where('shift', $studentShift);
        if ($studentShift !== 'common') {
          $q->orWhere('shift', 'common')->orWhereNull('shift');
        } else {
          $q->orWhereNull('shift');
        }
      })
      ->with([
        'weekdaymaster:id,title',
        'hourmaster:id,title',
        'lecturehallmaster:id,title',
        'faculty:id,FIRST_NAME,LAST_NAME',
        'syllabus',
      ])
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get()
      ->sortBy(function ($r) use ($studentShift) {
        $priority = $r->shift === $studentShift ? 0 : (($r->shift === 'common' || $r->shift === null) ? 1 : 2);
        return ($priority * 10000) + ((int) ($r->weekday_id ?? 0) * 100) + (int) ($r->hour_id ?? 0);
      })
      ->unique(fn($r) => ($r->weekday_id ?? 'x') . '_' . ($r->hour_id ?? 'x'))
      ->values();

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

    // Mentorship data
    $mentorshipGroup = MentorshipGroupStudent::where('student_id', $studentId)
      ->with([
        'group.faculty',
        'group.sessions' => function ($q) {
          $q->orderByDesc('session_date');
        },
        'group.assignments' => function ($q) {
          $q->orderByDesc('created_at');
        }
      ])
      ->first();

    $mentorName = null;
    $mentorshipSessions = collect();
    $mentorshipAttendances = collect();
    $mentorshipAssignments = collect();
    $mentorshipAssignmentSubmissions = collect();
    $mentorshipStats = [
      'total_sessions' => 0,
      'attended_sessions' => 0,
      'total_assignments' => 0,
      'completed_assignments' => 0,
    ];

    if ($mentorshipGroup && $mentorshipGroup->group) {
      $group = $mentorshipGroup->group;

      // Get mentor name
      if ($group->faculty) {
        $mentorName = trim($group->faculty->FIRST_NAME . ' ' . $group->faculty->LAST_NAME);
      }

      // Get sessions with feedback
      $mentorshipSessions = $group->sessions()
        ->where('status', 'completed')
        ->with(['attendances' => function ($q) use ($studentId) {
          $q->where('student_id', $studentId);
        }])
        ->orderByDesc('session_date')
        ->get();

      // Get attendance records
      $mentorshipAttendances = MentorshipSessionAttendance::where('student_id', $studentId)
        ->whereHas('session', function ($q) use ($group) {
          $q->where('mentorship_group_id', $group->id)
            ->where('status', 'completed');
        })
        ->with('session')
        ->orderByDesc('created_at')
        ->get();

      // Get assignments with submissions
      $mentorshipAssignments = $group->assignments()
        ->with(['submissions' => function ($q) use ($studentId) {
          $q->where('student_id', $studentId);
        }])
        ->orderByDesc('created_at')
        ->get();

      // Get all submissions
      $mentorshipAssignmentSubmissions = MentorshipAssignmentSubmission::where('student_id', $studentId)
        ->whereHas('assignment', function ($q) use ($group) {
          $q->where('mentorship_group_id', $group->id);
        })
        ->with('assignment')
        ->get();

      // Calculate stats
      $mentorshipStats['total_sessions'] = $mentorshipSessions->count();
      $mentorshipStats['attended_sessions'] = $mentorshipAttendances->where('status', 'present')->count();
      $mentorshipStats['total_assignments'] = $mentorshipAssignments->count();
      $mentorshipStats['completed_assignments'] = $mentorshipAssignmentSubmissions->whereIn('status', ['submitted', 'graded'])->count();
    }

    return view('student.dashboard', [
      'data'                              => $student,
      'studentCourses'                    => $studentCourses,
      'coursesBySemester'                 => $coursesBySemester,
      'timetableByDay'                    => $timetableByDay,
      'attendanceSummary'                 => $attendanceSummary,
      'internalMarks'                     => $internalMarks,
      'examResults'                       => $examResults,
      'resultsBySemester'                 => $resultsBySemester,
      'examStudent'                       => $examStudent,
      'examRegistrations'                 => $examRegistrations,
      'latestRegistration'                => $latestRegistration,
      'mentorName'                        => $mentorName,
      'mentorshipSessions'                => $mentorshipSessions,
      'mentorshipAttendances'             => $mentorshipAttendances,
      'mentorshipAssignments'             => $mentorshipAssignments,
      'mentorshipAssignmentSubmissions'   => $mentorshipAssignmentSubmissions,
      'mentorshipStats'                   => $mentorshipStats,
    ]);
  }

  /**
   * List all completed syllabus subunits for which the student can give feedback.
   */
  public function feedbackList()
  {
    $student = $this->getStudent();

    // Get all syllabus managers for student's batch
    return  $syllabusManagers = SyllabusManager::where('batch_id', $student->batch)
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
  private function getTimetableData(int $batchId, string $studentShift = 'common'): array
  {
    $studentShift = strtolower(trim($studentShift));
    if ($studentShift === '') {
      $studentShift = 'common';
    }

    // Fetch timetable entries for the student's shift with common fallback
    $routines = SubjectHasRoutine::where('batch_id', $batchId)
      ->where(function ($q) use ($studentShift) {
        $q->where('shift', $studentShift);
        if ($studentShift !== 'common') {
          $q->orWhere('shift', 'common')->orWhereNull('shift');
        } else {
          $q->orWhereNull('shift');
        }
      })
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
      ->get()
      ->sortBy(function ($r) use ($studentShift) {
        $priority = $r->shift === $studentShift ? 0 : (($r->shift === 'common' || $r->shift === null) ? 1 : 2);
        return ($priority * 10000) + ((int) ($r->weekday_id ?? 0) * 100) + (int) ($r->hour_id ?? 0);
      })
      ->unique(fn($r) => ($r->weekday_id ?? 'x') . '_' . ($r->hour_id ?? 'x'))
      ->values();

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

  /**
   * Upload assignment submission for mentorship program.
   */
  public function uploadAssignment(Request $request, int $assignmentId)
  {
    $studentId = $this->getStudent();

    // Validate the upload
    $request->validate([
      'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:10240', // 10MB max
      'response' => 'nullable|string|max:1000',
    ]);

    // Verify the assignment exists and student is enrolled in the mentorship group
    $assignment = MentorshipAssignment::findOrFail($assignmentId);

    $isEnrolled = MentorshipGroupStudent::where('mentorship_group_id', $assignment->mentorship_group_id)
      ->where('student_id', $studentId)
      ->exists();

    if (!$isEnrolled) {
      return redirect()->route('student.console.dashboard')
        ->with('error', 'You are not enrolled in this mentorship group.');
    }

    // Check if assignment is still active
    if ($assignment->status !== 'active') {
      return redirect()->route('student.console.dashboard')
        ->with('error', 'This assignment is no longer accepting submissions.');
    }

    // Handle file upload
    $file = $request->file;
    $filename = StaticController::s3_file_uploader($file, 'mentorship_assignments');

    // Create or update submission
    MentorshipAssignmentSubmission::updateOrCreate(
      [
        'mentorship_assignment_id' => $assignmentId,
        'student_id' => $studentId,
      ],
      [
        'submission_path' => $filename,
        'response' => $request->response,
        'status' => 'submitted',
        'submitted_at' => now(),
      ]
    );

    return redirect()->route('student.console.dashboard')
      ->with('success', 'Assignment submitted successfully!');
  }
}
