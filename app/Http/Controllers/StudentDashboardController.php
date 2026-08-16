<?php

namespace App\Http\Controllers;

use App\Models\CiaMark;
use App\Models\InterMark;
use App\Models\BatchMaster;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasStudentProgam;
use App\Models\SubUnitStudentFeedback;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\TeachingAssignment;
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
use App\Services\StudentTimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
      ->orderByDesc('id')
      ->get()
      ->unique(fn($c) => ($c->semester ?? $c->coursemaster?->semester_id ?? 'na') . '_' . $c->course_id)
      ->values();

    $semesterMap = Semester::pluck('title', 'id')->toArray();

    // Group by semester stored on enrollment row (same as student-profile/admin logic)
    $coursesBySemester = $studentCourses
      ->sortBy(fn($c) => $c->semester ?? $c->coursemaster?->semester_id ?? 999)
      ->groupBy(function ($c) use ($semesterMap) {
        $semId = $c->semester ?? $c->coursemaster?->semester_id;
        return $semesterMap[$semId] ?? ('Semester ' . ($semId ?? '?'));
      });

    $deliveryContext = $this->resolveStudentDeliveryContext($student, $studentCourses);
    $electiveOptions = $this->resolveStudentElectiveOptions($student, $studentCourses);

    $courseIds = $studentCourses
      ->pluck('course_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $semesterIds = $studentCourses
      ->map(fn($course) => (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0))
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $faMarkRows = CiaMark::where('STUDENT_ID', $studentId)
      ->when($courseIds->isNotEmpty(), function ($query) use ($courseIds) {
        $query->whereIn('COURSE_ID', $courseIds->all());
      })
      ->when($semesterIds->isNotEmpty(), function ($query) use ($semesterIds) {
        $query->whereIn('SEMESTER_ID', $semesterIds->all());
      })
      ->with(['groupinfo.grouptype:id,name'])
      ->get();

    $faComponentNames = $faMarkRows
      ->map(fn($m) => trim((string) ($m->groupinfo?->grouptype?->name ?? '')))
      ->filter(fn($name) => $name !== '')
      ->unique()
      ->values();

    $faMarksBySemesterCourse = $faMarkRows
      ->groupBy(fn($m) => (string) ((int) ($m->SEMESTER_ID ?? 0)) . '_' . (string) ((int) ($m->COURSE_ID ?? 0)))
      ->map(function ($rows) use ($faComponentNames) {
        $total = 0.0;
        $components = [];

        foreach ($faComponentNames as $componentName) {
          $components[$componentName] = null;
        }

        foreach ($rows as $cm) {
          $rawCourseMark = trim((string) ($cm->COURSE_GROUP_MARK ?? ''));
          $courseMark = is_numeric($rawCourseMark) ? (float) $rawCourseMark : 0.0;
          $groupTypeId = (int) ($cm->groupinfo?->grouptype?->id ?? 0);
          $componentName = trim((string) ($cm->groupinfo?->grouptype?->name ?? ''));

          $total += $groupTypeId === 5 ? $courseMark : ($courseMark / 2);

          if ($componentName !== '') {
            $components[$componentName] = $courseMark;
          }
        }

        return [
          'total' => (int) round($total),
          'components' => $components,
        ];
      });

    $timetable = StudentTimetableService::generate((int) $studentId);

    $timetableByDay = $timetable->groupBy(fn($r) => $r['weekday'] ?? 'Unknown');

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
      'courseDeliveryMap'                 => $deliveryContext['courseDeliveryMap'],
      'courseOfferingSubjectMap'          => $deliveryContext['courseOfferingSubjectMap'],
      'studentMajorDeliveryType'          => $deliveryContext['studentMajorDeliveryType'],
      'programOfferingSubjectTitle'       => $deliveryContext['programOfferingSubjectTitle'],
      'electiveCoursesBySemester'         => $electiveOptions,
      'faComponentNames'                  => $faComponentNames,
      'faMarksBySemesterCourse'           => $faMarksBySemesterCourse,
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

  public function confirmElectives(Request $request)
  {
    $studentId = (int) $this->getStudent();
    $student = StudentMaster::findOrFail($studentId);

    $validated = $request->validate([
      'semester_id' => 'required|integer|exists:semesters,id',
      'course_id' => 'required|integer|exists:program_course_masters,id',
    ]);

    $semesterId = (int) $validated['semester_id'];
    $requestedCourseId = (int) $validated['course_id'];

    $studentCourses = StudentCourseInfo::with(['coursemaster:id,semester_id'])
      ->where('student_id', $studentId)
      ->get();

    $electiveOptions = $this->resolveStudentElectiveOptions($student, $studentCourses);
    $allowedCourseIds = collect($electiveOptions->get($semesterId, collect()))
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique();

    if ($allowedCourseIds->isEmpty()) {
      return redirect()->route('student.console.dashboard')
        ->with('error', 'No elective choices are available for the selected semester.')
        ->withFragment('tab-courses');
    }

    if (!$allowedCourseIds->contains($requestedCourseId)) {
      return redirect()->route('student.console.dashboard')
        ->with('error', 'Please select valid elective courses for the chosen semester.')
        ->withFragment('tab-courses');
    }

    $academicYear = (string) (BatchMaster::find($student->batch)?->batch_name ?? date('Y'));

    $requestedCourse = ProgramCourseMaster::with(['coursetypemaster:id,title'])->find($requestedCourseId);
    $requestedTypeTitle = strtoupper(trim((string) ($requestedCourse?->coursetypemaster?->title ?? '')));
    $requestedTypeKey = $requestedTypeTitle !== '' ? preg_replace('/\s.*/', '', $requestedTypeTitle) : '';
    $isRequestedMdc = $requestedTypeKey === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE;

    if ($isRequestedMdc) {
      $existingSemesterMdcCourseId = StudentCourseInfo::with(['coursemaster.coursetypemaster:id,title'])
        ->where('student_id', $studentId)
        ->where('semester', $semesterId)
        ->where('academic_year', $academicYear)
        ->where('is_deleted', 0)
        ->get()
        ->first(function ($row) {
          $typeTitle = strtoupper(trim((string) ($row->coursemaster?->coursetypemaster?->title ?? '')));
          $typeKey = $typeTitle !== '' ? preg_replace('/\s.*/', '', $typeTitle) : '';
          return $typeKey === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE;
        });

      if ($existingSemesterMdcCourseId && (int) ($existingSemesterMdcCourseId->course_id ?? 0) !== $requestedCourseId) {
        return redirect()->route('student.console.dashboard')
          ->with('error', 'Only one MDC course can be selected per semester.')
          ->withFragment('tab-courses');
      }
    }

    $enrolled = 0;
    $skipped = 0;

    $exists = StudentCourseInfo::where('student_id', $studentId)
      ->where('course_id', $requestedCourseId)
      ->where('semester', $semesterId)
      ->where('academic_year', $academicYear)
      ->where('is_deleted', 0)
      ->exists();

    if ($exists) {
      $skipped++;
    } else {
      StudentCourseInfo::create([
        'student_id' => $studentId,
        'course_id' => $requestedCourseId,
        'semester' => $semesterId,
        'campus_id' => (int) ($student->campus_id ?? 0),
        'is_active' => 1,
        'academic_year' => $academicYear,
        'is_elective' => 1,
      ]);

      $enrolled++;
    }

    $message = $enrolled . ' elective course(s) confirmed successfully.';
    if ($skipped > 0) {
      $message .= ' ' . $skipped . ' already enrolled (skipped).';
    }

    return redirect()->route('student.console.dashboard')
      ->with('success', $message)
      ->withFragment('tab-courses');
  }

  private function resolveStudentElectiveOptions(StudentMaster $student, $studentCourses)
  {
    $programCombination = null;
    if (!empty($student->new_program_id) && !empty($student->batch)) {
      $programCombination = SubjectHasStudentProgam::with([
        'combomap:id,combo_id_1,combo_id_2',
      ])
        ->where('student_program_id', (int) $student->new_program_id)
        ->where('batch_id', (int) $student->batch)
        ->orderBy('id')
        ->first();
    }

    if (!$programCombination) {
      return collect();
    }

    $comboSubjectIds = collect([
      (int) ($programCombination?->combomap?->combo_id_1 ?? 0),
      (int) ($programCombination?->combomap?->combo_id_2 ?? 0),
    ])
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($comboSubjectIds->isEmpty()) {
      $fallbackSubjectId = (int) ($programCombination?->subject_id ?? 0);
      if ($fallbackSubjectId > 0) {
        $comboSubjectIds->push($fallbackSubjectId);
      }
    }

    $pathwayId = (int) ($student->academic_pathway_id ?? 0);
    $degreeTrackId = (int) ($student->degree_track_id ?? 0);

    $query = ProgramWiseSemesterCourse::query()
      ->where('batch', (int) $student->batch)
      ->whereIn('course_type', [
        ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
        'ELECTIVE',
      ]);

    if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'is_active')) {
      $query->where('is_active', 1);
    }

    if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
      $query->where(function ($sub) use ($pathwayId) {
        $sub->whereNull('academic_pathway_id');
        if ($pathwayId > 0) {
          $sub->orWhere('academic_pathway_id', $pathwayId);
        }
      });
    }

    if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
      $query->where(function ($sub) use ($degreeTrackId) {
        $sub->whereNull('degree_track_id');
        if ($degreeTrackId > 0) {
          $sub->orWhere('degree_track_id', $degreeTrackId);
        }
      });
    }

    $rows = $query->get([
      'program_combo_refid',
      'course_id',
      'semester',
      'delivery_category',
      'specialization_master_id',
      'specialization_master_ids',
    ]);

    if ($rows->isEmpty()) {
      return collect();
    }

    $studentSpecializations = collect();
    if (Schema::hasTable('student_specializations')) {
      $studentSpecializations = DB::table('student_specializations')
        ->where('student_id', (int) $student->id)
        ->where('subject_has_student_program_id', (int) $programCombination->id)
        ->where('is_active', 1)
        ->whereNull('deleted_at')
        ->orderByDesc('id')
        ->get(['specialization_id', 'semester_id']);
    }

    $studentSpecBySemester = [];
    foreach ($studentSpecializations as $specRow) {
      $semId = (int) ($specRow->semester_id ?? 0);
      $specId = (int) ($specRow->specialization_id ?? 0);
      if ($specId <= 0) {
        continue;
      }
      if (!isset($studentSpecBySemester[$semId])) {
        $studentSpecBySemester[$semId] = $specId;
      }
    }

    $enrolledPairKeys = collect($studentCourses)
      ->map(function ($course) {
        $sem = (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0);
        $courseId = (int) ($course->course_id ?? 0);
        return $sem . '_' . $courseId;
      })
      ->filter(fn($key) => $key !== '0_0')
      ->unique();

    $eligiblePairs = $rows->map(function ($row) use ($studentSpecBySemester, $enrolledPairKeys) {
      $semester = (int) ($row->semester ?? 0);
      $courseId = (int) ($row->course_id ?? 0);
      if ($semester <= 0 || $courseId <= 0) {
        return null;
      }

      $mappingSpecIds = collect($row->specialization_master_ids ?? [])
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->values();

      $singleSpecId = (int) ($row->specialization_master_id ?? 0);
      if ($singleSpecId > 0 && !$mappingSpecIds->contains($singleSpecId)) {
        $mappingSpecIds->push($singleSpecId);
      }

      if ($mappingSpecIds->isNotEmpty()) {
        $studentSpecId = (int) ($studentSpecBySemester[$semester] ?? $studentSpecBySemester[0] ?? 0);
        if ($studentSpecId <= 0 || !$mappingSpecIds->contains($studentSpecId)) {
          return null;
        }
      }

      $pairKey = $semester . '_' . $courseId;
      if ($enrolledPairKeys->contains($pairKey)) {
        return null;
      }

      return [
        'program_combo_refid' => (int) ($row->program_combo_refid ?? 0),
        'semester' => $semester,
        'course_id' => $courseId,
        'delivery_category' => strtoupper(trim((string) ($row->delivery_category ?? ''))),
      ];
    })->filter()->unique(fn($pair) => $pair['semester'] . '_' . $pair['course_id'])->values();

    if ($eligiblePairs->isEmpty()) {
      return collect();
    }

    $courseIds = $eligiblePairs->pluck('course_id')->unique()->values()->all();
    $courses = ProgramCourseMaster::with(['semestermaster:id,title', 'coursetypemaster:id,title'])
      ->whereIn('id', $courseIds)
      ->orderBy('course_title')
      ->get()
      ->keyBy('id');

    $isMdcCourse = function ($course, string $deliveryCategory = ''): bool {
      $normalizedDelivery = strtoupper(trim($deliveryCategory));
      if ($normalizedDelivery === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE) {
        return true;
      }

      if (!$course) {
        return false;
      }

      $typeTitle = strtoupper(trim((string) ($course->coursetypemaster?->title ?? '')));
      $typeKey = $typeTitle !== '' ? preg_replace('/\s.*/', '', $typeTitle) : '';
      if ($typeKey === ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE) {
        return true;
      }

      $courseCode = strtoupper(trim((string) ($course->course_code ?? '')));
      return str_contains($courseCode, ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE);
    };

    // Elective selector should only show MDC courses.
    $mdcEligiblePairs = $eligiblePairs->filter(function ($pair) use ($courses, $isMdcCourse) {
      $course = $courses->get((int) ($pair['course_id'] ?? 0));
      $deliveryCategory = (string) ($pair['delivery_category'] ?? '');
      return $isMdcCourse($course, $deliveryCategory);
    })->values();

    if ($mdcEligiblePairs->isEmpty()) {
      return collect();
    }

    // If student has already taken an MDC course, do not show it again in electives.
    $studentTakenCourseIds = collect($studentCourses)
      ->pluck('course_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $takenMdcCourseIds = collect();
    if ($studentTakenCourseIds->isNotEmpty()) {
      $takenMdcCourseIds = ProgramCourseMaster::with(['coursetypemaster:id,title'])
        ->whereIn('id', $studentTakenCourseIds->all())
        ->get()
        ->filter(fn($course) => $isMdcCourse($course))
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();
    }

    $excludedMdcPairKeys = collect();
    if ($comboSubjectIds->isNotEmpty() && !empty($student->new_program_id) && !empty($student->batch)) {
      $comboCombinationIds = SubjectHasStudentProgam::query()
        ->where('student_program_id', (int) $student->new_program_id)
        ->where('batch_id', (int) $student->batch)
        ->whereIn('subject_id', $comboSubjectIds->all())
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();

      if ($comboCombinationIds->isNotEmpty()) {
        $mdcCourseIds = $mdcEligiblePairs->pluck('course_id')->map(fn($id) => (int) $id)->unique()->values()->all();
        $mdcSemesterIds = $mdcEligiblePairs->pluck('semester')->map(fn($id) => (int) $id)->unique()->values()->all();

        $curriculumQuery = ProgramWiseSemesterCourse::query()
          ->whereIn('program_combo_refid', $comboCombinationIds->all())
          ->where('batch', (int) $student->batch)
          ->whereIn('course_id', $mdcCourseIds)
          ->whereIn('semester', $mdcSemesterIds);

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'is_active')) {
          $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'delivery_category')) {
          $curriculumQuery->whereRaw("UPPER(TRIM(COALESCE(delivery_category, ''))) = ?", [ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE]);
        }

        $excludedMdcPairKeys = $curriculumQuery
          ->get(['semester', 'course_id'])
          ->map(fn($row) => (int) ($row->semester ?? 0) . '_' . (int) ($row->course_id ?? 0))
          ->filter(fn($key) => $key !== '0_0')
          ->unique()
          ->values();

        $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
        if (Schema::hasColumn($curriculumTable, 'offering_dept')) {
          $offeringDeptExcludedKeys = ProgramWiseSemesterCourse::query()
            ->where('batch', (int) $student->batch)
            ->whereIn('course_id', $mdcCourseIds)
            ->whereIn('semester', $mdcSemesterIds)
            ->whereIn('offering_dept', $comboSubjectIds->all())
            ->when(
              Schema::hasColumn($curriculumTable, 'is_active'),
              fn($q) => $q->where('is_active', 1)
            )
            ->get(['semester', 'course_id'])
            ->map(fn($row) => (int) ($row->semester ?? 0) . '_' . (int) ($row->course_id ?? 0))
            ->filter(fn($key) => $key !== '0_0')
            ->unique()
            ->values();

          $excludedMdcPairKeys = $excludedMdcPairKeys
            ->merge($offeringDeptExcludedKeys)
            ->unique()
            ->values();
        }
      }
    }

    return $mdcEligiblePairs
      ->groupBy('semester')
      ->map(function ($pairs, $semesterId) use ($courses, $excludedMdcPairKeys, $takenMdcCourseIds) {
        return $pairs->map(function ($pair) use ($courses, $semesterId, $excludedMdcPairKeys, $takenMdcCourseIds) {
          $pairKey = (int) ($pair['semester'] ?? 0) . '_' . (int) ($pair['course_id'] ?? 0);
          if ($excludedMdcPairKeys->contains($pairKey)) {
            return null;
          }

          if ($takenMdcCourseIds->contains((int) ($pair['course_id'] ?? 0))) {
            return null;
          }

          $course = $courses->get((int) $pair['course_id']);
          if (!$course) {
            return null;
          }

          $course->student_semester_id = (int) $semesterId;
          return $course;
        })->filter()->values();
      });
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

    $combo2Id = (int) ($programCombination?->combomap?->combo_id_2 ?? 0);
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

    $courseDeliveryMap = [];
    $courseOfferingSubjectMap = [];
    $programType = strtoupper(trim((string) ($programCombination?->program_type ?? '')));
    if ($programCombination && $studentCourses) {
      $courseIds = collect($studentCourses)
        ->pluck('course_id')
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

      $semesterIds = collect($studentCourses)
        ->map(fn($course) => (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0))
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

      if (!empty($courseIds)) {
        $deliveryRowsQuery = ProgramWiseSemesterCourse::query()
          ->where('program_combo_refid', (int) $programCombination->id)
          ->where('batch', (int) $student->batch)
          ->whereIn('course_id', $courseIds);

        $pathwayId = (int) ($student->academic_pathway_id ?? 0);
        $degreeTrackId = (int) ($student->degree_track_id ?? 0);

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
          if ($pathwayId > 0) {
            $deliveryRowsQuery->where('academic_pathway_id', $pathwayId);
          } else {
            $deliveryRowsQuery->whereNull('academic_pathway_id');
          }
        }

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
          if ($degreeTrackId > 0) {
            $deliveryRowsQuery->where('degree_track_id', $degreeTrackId);
          } else {
            $deliveryRowsQuery->whereNull('degree_track_id');
          }
        }

        $deliveryRows = $deliveryRowsQuery->get(['semester', 'course_id', 'delivery_category']);
        foreach ($deliveryRows as $row) {
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

        $syllabusRows = $syllabusQuery->get(['semester_id', 'co_id', 'subject_id']);
        foreach ($syllabusRows as $row) {
          $key = (string) ((int) $row->semester_id) . '_' . (string) ((int) $row->co_id);
          $subjectTitle = trim((string) ($row->subject?->title ?? ''));
          if ($subjectTitle === '') {
            continue;
          }

          if (!isset($courseOfferingSubjectMap[$key])) {
            $courseOfferingSubjectMap[$key] = [];
          }

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
      'programCombinationId' => (int) ($programCombination?->id ?? 0),
      'studentMajorDeliveryType' => $studentMajorDeliveryType,
      'studentApplicableDeliveryTypes' => $studentApplicableDeliveryTypes,
      'courseDeliveryMap' => $courseDeliveryMap,
      'courseOfferingSubjectMap' => $courseOfferingSubjectMap,
      'programOfferingSubjectTitle' => (string) ($programCombination?->subjectmaster?->title ?? ''),
    ];
  }

  private function resolveStudentTimetableRows(StudentMaster $student, array $deliveryContext, $studentCourses)
  {
    $batchId = (int) ($student->batch ?? 0);
    if ($batchId <= 0) {
      return collect();
    }

    $programCombinationId = (int) ($deliveryContext['programCombinationId'] ?? 0);

    $studentCoursePairs = collect($studentCourses)
      ->map(function ($course) use ($deliveryContext) {
        $courseId = (int) ($course->course_id ?? 0);
        $semesterId = (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0);
        if ($courseId <= 0 || $semesterId <= 0) {
          return null;
        }

        $deliveryKey = (string) $semesterId . '_' . (string) $courseId;
        $deliveryType = strtoupper(trim((string) ($deliveryContext['courseDeliveryMap'][$deliveryKey] ?? ($deliveryContext['studentMajorDeliveryType'] ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON))));
        if ($deliveryType === '') {
          $deliveryType = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }

        return [
          'course_id' => $courseId,
          'semester_id' => $semesterId,
          'delivery_type' => $deliveryType,
        ];
      })
      ->filter()
      ->unique(fn($row) => $row['semester_id'] . '_' . $row['course_id'] . '_' . $row['delivery_type'])
      ->values();

    if ($studentCoursePairs->isEmpty() || $programCombinationId <= 0) {
      return collect();
    }

    $pathwayId = (int) ($student->academic_pathway_id ?? 0);
    $degreeTrackId = (int) ($student->degree_track_id ?? 0);

    $curriculumQuery = ProgramWiseSemesterCourse::query()
      ->where('program_combo_refid', $programCombinationId)
      ->where('batch', $batchId)
      ->whereIn('course_id', $studentCoursePairs->pluck('course_id')->unique()->values()->all())
      ->whereIn('semester', $studentCoursePairs->pluck('semester_id')->unique()->values()->all());

    if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'is_active')) {
      $curriculumQuery->where('is_active', 1);
    }

    if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
      if ($pathwayId > 0) {
        $curriculumQuery->where('academic_pathway_id', $pathwayId);
      } else {
        $curriculumQuery->whereNull('academic_pathway_id');
      }
    }

    if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
      if ($degreeTrackId > 0) {
        $curriculumQuery->where('degree_track_id', $degreeTrackId);
      } else {
        $curriculumQuery->whereNull('degree_track_id');
      }
    }

    $curriculumRows = $curriculumQuery->get(['course_id', 'semester', 'delivery_category']);
    if ($curriculumRows->isEmpty()) {
      return collect();
    }

    $applicablePairKeys = $curriculumRows
      ->map(function ($row) {
        $delivery = strtoupper(trim((string) ($row->delivery_category ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON)));
        if ($delivery === '') {
          $delivery = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }
        return (int) ($row->semester ?? 0) . '_' . (int) ($row->course_id ?? 0) . '_' . $delivery;
      })
      ->unique()
      ->values();

    $selectedPairs = $studentCoursePairs
      ->filter(fn($pair) => $applicablePairKeys->contains($pair['semester_id'] . '_' . $pair['course_id'] . '_' . $pair['delivery_type']))
      ->values();

    if ($selectedPairs->isEmpty()) {
      return collect();
    }

    $selectedCourseIds = $selectedPairs->pluck('course_id')->unique()->values();
    $selectedPairKeys = $selectedPairs
      ->map(fn($pair) => $pair['course_id'] . '_' . $pair['delivery_type'])
      ->unique()
      ->values();

    $assignments = TeachingAssignment::query()
      ->with([
        'primaryFacultyMembers:id',
        'coFacultyMembers:id',
      ])
      ->where('is_active', 1)
      ->whereIn('course_id', $selectedCourseIds->all())
      ->get(['id', 'course_id', 'faculty_id', 'delivery_type'])
      ->map(function ($assignment) {
        $deliveryType = strtoupper(trim((string) ($assignment->delivery_type ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON)));
        if ($deliveryType === '') {
          $deliveryType = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
        }
        $assignment->normalized_delivery_type = $deliveryType;
        return $assignment;
      })
      ->filter(fn($assignment) => $selectedPairKeys->contains(((int) $assignment->course_id) . '_' . $assignment->normalized_delivery_type))
      ->values();
    $assignmentIds = $assignments->pluck('id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();
    $assignmentFacultyIds = $assignments
      ->flatMap(function ($assignment) {
        $primaryIds = collect($assignment->primaryFacultyMembers ?? collect())
          ->pluck('id')
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->values();

        if ($primaryIds->isEmpty() && !empty($assignment->faculty_id)) {
          $primaryIds = collect([(int) $assignment->faculty_id]);
        }

        $coIds = collect($assignment->coFacultyMembers ?? collect())
          ->pluck('id')
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->values();

        return $primaryIds->merge($coIds)->unique()->values();
      })
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $subjectCourseIds = SubjectCourseMaster::query()
      ->whereIn('course_master_id', $selectedCourseIds->all())
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $hasTeachingAssignmentId = Schema::hasColumn('subject_has_routines', 'teaching_assignment_id');
    $hasTeachingAllocationId = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    return SubjectHasRoutine::query()
      ->where('batch_id', $batchId)
      ->where(function ($outer) use (
        $assignmentIds,
        $assignmentFacultyIds,
        $subjectCourseIds,
        $hasTeachingAssignmentId,
        $hasTeachingAllocationId
      ) {
        $matchedByAllocation = false;
        if ($assignmentIds->isNotEmpty() && $hasTeachingAssignmentId) {
          $outer->whereIn('teaching_assignment_id', $assignmentIds->all());
          $matchedByAllocation = true;
        }

        if ($assignmentIds->isNotEmpty() && $hasTeachingAllocationId) {
          if ($matchedByAllocation) {
            $outer->orWhereIn('teaching_allocation_id', $assignmentIds->all());
          } else {
            $outer->whereIn('teaching_allocation_id', $assignmentIds->all());
            $matchedByAllocation = true;
          }
        }

        if ($subjectCourseIds->isNotEmpty() && $assignmentFacultyIds->isNotEmpty()) {
          $outer->orWhere(function ($legacy) use (
            $subjectCourseIds,
            $assignmentFacultyIds,
            $hasTeachingAssignmentId,
            $hasTeachingAllocationId
          ) {
            if ($hasTeachingAssignmentId) {
              $legacy->whereNull('teaching_assignment_id');
            }
            if ($hasTeachingAllocationId) {
              $legacy->whereNull('teaching_allocation_id');
            }

            $legacy->whereIn('subject_course_id', $subjectCourseIds->all())
              ->whereIn('faculty_id', $assignmentFacultyIds->all());
          });
        }
      })
      ->with([
        'weekdaymaster:id,title',
        'hourmaster:id,title,name',
        'lecturehallmaster:id,title',
        'faculty:id,FIRST_NAME,LAST_NAME',
        'subjectCourse:id,subject_id,course_master_id',
        'subjectCourse.courseMaster:id,course_code,course_title,semester_id',
        'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAssignment.course:id,course_code,course_title,semester_id',
        'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAllocation.course:id,course_code,course_title,semester_id',
      ])
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get();
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
