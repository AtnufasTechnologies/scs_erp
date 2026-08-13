<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InterMark;
use App\Models\InternalMarkLog;
use App\Models\ProgramCourseMaster;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\SupCiaComponent;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InternalMarksController extends Controller
{
  private function applyFacultyRoutineAccess($query, int $facultyId)
  {
    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    return $query->where(function ($nested) use ($facultyId) {
      $nested->where('faculty_id', $facultyId)
        ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
          $assignmentQuery->where('faculty_id', $facultyId)
            ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
              $facultyAssignmentQuery->where('faculty_id', $facultyId);
            })
            ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
              $coFacultyQuery->where('faculties.id', $facultyId);
            });
        });
    })->when($hasTeachingAllocationLink, function ($builder) use ($facultyId) {
      $builder->orWhereHas('teachingAllocation', function ($assignmentQuery) use ($facultyId) {
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

  /**
   * Show course/semester/year selection form
   */
  public function index()
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    $syllabusAssignments = $this->applyFacultyRoutineAccess(SubjectHasRoutine::query(), (int) $facultyId)
      ->with([
        'syllabus.subject:id,title,campus_id',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
      ])
      ->get();

    $faComponents = SupCiaComponent::query()
      ->where('IS_DELETED', 0)
      ->orderBy('id')
      ->get()
      ->filter(function ($component) {
        $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $component->name));
        return in_array($normalized, ['FA2', 'FAII', 'FA3', 'FAIII'], true);
      })
      ->values();

    return view('faculty.internal-marks.index', compact('syllabusAssignments', 'faComponents'));
  }

  /**
   * Show the marks entry form for a specific course/semester
   */
  public function enter(Request $request)
  {
    $request->validate([
      'rec_id'        => 'required|integer|exists:subject_has_routines,id',
      'syllabus_id'   => 'required|integer|exists:subject_has_syllabi,id',
      'component_id'  => 'required|integer|exists:sup_cia_components,id',
      'academic_year' => 'nullable|integer',
    ]);

    $facultyId = SubjectFacultyMaster::where('access_id', Auth::id())->value('faculty_id');
    $routineId = (int) $request->input('rec_id');
    $syllabusId = (int) $request->input('syllabus_id');

    $routine = $this->applyFacultyRoutineAccess(
      SubjectHasRoutine::query()->where('id', $routineId),
      (int) $facultyId
    )->with([
      'syllabus.subject:id,title,campus_id',
      'syllabus.courseLink.courseMaster:id,course_title,course_code',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.semestermaster:id,title',
      'teachingAssignment:id,allocation_group',
      'teachingAllocation:id,allocation_group',
    ])->first();

    if (!$routine || !$routine->syllabus || (int) $routine->syllabus_id !== $syllabusId) {
      return redirect()->route('faculty.internal-marks.index')->with('error', 'Invalid faculty assignment selected.');
    }

    $syllabusAssignment = $routine->syllabus;
    $courseId = (int) $syllabusAssignment->course_id;
    $semester = (int) $syllabusAssignment->semester_id;
    $batchId = (int) $syllabusAssignment->batch_id;
    $componentId = (int) $request->input('component_id');
    $academicYear = $request->input('academic_year');

    $course = ProgramCourseMaster::with(['semestermaster', 'departmentmaster'])->findOrFail($courseId);
    $component = SupCiaComponent::where('id', $componentId)->where('IS_DELETED', 0)->firstOrFail();

    $students = $this->resolveAssignedStudentsForMarks($syllabusAssignment, $routine, $courseId, $semester, $batchId);
    $studentIds = $students->pluck('id');

    $existingMarks = InterMark::where('course_id', $courseId)
      ->where('semester', $semester)
      ->where('semester_type', $componentId)
      ->where('is_deleted', 0)
      ->whereIn('student_id', $studentIds)
      ->get()
      ->keyBy('student_id');

    return view('faculty.internal-marks.enter', compact(
      'course',
      'students',
      'existingMarks',
      'semester',
      'batchId',
      'routineId',
      'syllabusId',
      'syllabusAssignment',
      'component',
      'componentId',
      'academicYear'
    ));
  }

  /**
   * Store/update internal marks
   */
  public function store(Request $request)
  {
    $request->validate([
      'course_id'            => 'required|integer',
      'semester'             => 'required',
      'batch_id'             => 'nullable|integer',
      'component_id'         => 'required|integer|exists:sup_cia_components,id',
      'marks'                => 'required|array',
      'marks.*.student_id'   => 'required|exists:student_masters,id',
      'marks.*.internal_mark' => 'nullable|string|max:45',
    ]);

    $courseId = $request->course_id;
    $semester = $request->semester;
    $componentId = (int) $request->component_id;
    $saved = 0;
    $user = Auth::user();

    $studentIds = collect($request->marks)->pluck('student_id')->map(fn($id) => (int) $id)->values();
    $studentBatchMap = StudentMaster::whereIn('id', $studentIds)->pluck('batch', 'id');
    $requestedBatchId = (int) ($request->batch_id ?? 0);

    $faMarksTableExists = Schema::hasTable('fa_marks');

    foreach ($request->marks as $entry) {
      if ($entry['internal_mark'] === null || $entry['internal_mark'] === '') {
        continue;
      }

      // Check if mark already exists
      $existing = InterMark::where('student_id', $entry['student_id'])
        ->where('course_id', $courseId)
        ->where('semester', $semester)
        ->where('semester_type', $componentId)
        ->first();

      if ($existing && $existing->internal_mark !== $entry['internal_mark']) {
        // Log the change
        InternalMarkLog::create([
          'internal_mark_id' => $existing->id,
          'student_id'       => $entry['student_id'],
          'course_id'        => $courseId,
          'semester'         => $semester,
          'old_mark'         => $existing->internal_mark,
          'new_mark'         => $entry['internal_mark'],
          'changed_by'       => $user->id,
          'changed_by_name'  => $user->name,
        ]);
      }

      InterMark::updateOrCreate(
        [
          'student_id' => $entry['student_id'],
          'course_id'  => $courseId,
          'semester'   => $semester,
          'semester_type' => $componentId,
        ],
        [
          'internal_mark'  => $entry['internal_mark'],
          'academic_year'  => $request->academic_year ?? null,
          'semester_type'  => $componentId,
          'is_deleted'     => 0,
        ]
      );

      if ($faMarksTableExists) {
        DB::table('fa_marks')->upsert(
          [[
            'student_id' => (int) $entry['student_id'],
            'course_id' => (int) $courseId,
            'batch_id' => $requestedBatchId > 0
              ? $requestedBatchId
              : ((int) ($studentBatchMap[(int) $entry['student_id']] ?? 0) ?: null),
            'semester_id' => (int) $semester,
            'component_id' => $componentId,
            'score' => (float) $entry['internal_mark'],
            'attempt' => 1,
            'created_at' => now(),
            'updated_at' => now(),
          ]],
          ['student_id', 'course_id', 'batch_id', 'semester_id', 'component_id', 'attempt'],
          ['score', 'updated_at']
        );
      }

      $saved++;
    }

    return redirect()->route('faculty.internal-marks.index')
      ->with('success', "Internal marks saved for {$saved} students.");
  }

  /**
   * View submitted marks for a course/semester
   */
  public function view(Request $request)
  {
    $request->validate([
      'rec_id' => 'required|integer|exists:subject_has_routines,id',
      'syllabus_id' => 'required|integer|exists:subject_has_syllabi,id',
      'component_id' => 'required|integer|exists:sup_cia_components,id',
    ]);

    $facultyId = SubjectFacultyMaster::where('access_id', Auth::id())->value('faculty_id');
    $routineId = (int) $request->input('rec_id');
    $syllabusId = (int) $request->input('syllabus_id');

    $routine = $this->applyFacultyRoutineAccess(
      SubjectHasRoutine::query()->where('id', $routineId),
      (int) $facultyId
    )->with([
      'syllabus.subject:id,title,campus_id',
      'syllabus.courseLink.courseMaster:id,course_title,course_code',
      'syllabus.batchmaster:id,batch_name',
      'syllabus.semestermaster:id,title',
    ])->first();

    if (!$routine || !$routine->syllabus || (int) $routine->syllabus_id !== $syllabusId) {
      return redirect()->route('faculty.internal-marks.index')->with('error', 'Invalid faculty assignment selected.');
    }

    $syllabusAssignment = $routine->syllabus;
    $courseId = (int) $syllabusAssignment->course_id;
    $semester = (int) $syllabusAssignment->semester_id;
    $batchId = (int) $syllabusAssignment->batch_id;
    $componentId = (int) $request->input('component_id');

    $course = ProgramCourseMaster::with(['semestermaster', 'departmentmaster'])->findOrFail($courseId);
    $component = SupCiaComponent::where('id', $componentId)->where('IS_DELETED', 0)->firstOrFail();

    $marks = InterMark::where('course_id', $courseId)
      ->where('semester', $semester)
      ->where('semester_type', $componentId)
      ->where('is_deleted', 0)
      ->with('student:id,first_name,last_name,roll_no,register_no')
      ->orderBy('created_at', 'desc')
      ->get();

    return view('faculty.internal-marks.view', compact(
      'course',
      'marks',
      'semester',
      'component',
      'componentId',
      'batchId',
      'routineId',
      'syllabusId',
      'syllabusAssignment'
    ));
  }

  private function resolveAssignedStudentsForMarks(SubjectHasSyllabus $syllabus, SubjectHasRoutine $routine, int $courseId, int $semesterId, int $batchId)
  {
    $campusId = (int) ($syllabus->subject->campus_id ?? 0);
    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));
    $routineAllocationGroup = (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->leftJoin('subject_has_student_progams as shp', function ($join) use ($syllabus, $batchId) {
        $join->on('shp.student_program_id', '=', 'sm.new_program_id')
          ->where('shp.subject_id', '=', (int) ($syllabus->subject_id ?? 0))
          ->where('shp.batch_id', '=', $batchId);
      })
      ->select('sm.id')
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $batchId)
      ->where('sci.course_id', $courseId)
      ->where('sci.semester', $semesterId)
      ->where('sci.is_deleted', 0)
      ->distinct();

    if ($campusId > 0 && Schema::hasColumn('student_course_infos', 'campus_id')) {
      $baseQuery->where('sci.campus_id', $campusId);
    }

    if ($routineAllocationGroup > 0 && Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      $baseQuery->where('sci.allocation_group_id', $routineAllocationGroup);
    }

    $studentIds = (clone $baseQuery)
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift])
      ->pluck('sm.id');

    if ($studentIds->isEmpty()) {
      $studentIds = $baseQuery->pluck('sm.id');
    }

    return StudentMaster::whereIn('id', $studentIds)
      ->where('is_deleted', 0)
      ->where('is_left', 0)
      ->orderBy('roll_no')
      ->orderBy('first_name')
      ->get(['id', 'first_name', 'last_name', 'roll_no', 'register_no']);
  }
}
