<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BatchMaster;
use App\Models\ExtraClassAttendance;
use App\Models\HourMaster;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\SyllabusHasFaculty;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use App\Models\ShiftMaster;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
  private function getShiftTeachingHours(string $shiftSlug)
  {
    $shiftSlug = strtolower(trim($shiftSlug));
    if ($shiftSlug === '') {
      return collect();
    }

    $shiftId = (int) ShiftMaster::where('slug', $shiftSlug)->value('id');
    if ($shiftId <= 0) {
      return collect();
    }

    $hourTable = (new HourMaster())->getTable();

    $hourQuery = HourMaster::query()
      ->where('shift_id', $shiftId);

    if (Schema::hasColumn($hourTable, 'status')) {
      $hourQuery->where('status', 1);
    }

    if (Schema::hasColumn($hourTable, 'is_teaching')) {
      $hourQuery->where('is_teaching', 1);
    }

    if (Schema::hasColumn($hourTable, 'hour_no')) {
      $hourQuery->orderBy('hour_no');
    } else {
      $hourQuery->orderBy('id');
    }

    $selectColumns = ['id'];
    foreach (['hour_no', 'name', 'title', 'start_time', 'end_time'] as $column) {
      if (Schema::hasColumn($hourTable, $column)) {
        $selectColumns[] = $column;
      }
    }

    return $hourQuery
      ->get($selectColumns)
      ->map(function ($hour) {
        $label = (string) ($hour->title ?? $hour->name ?? '');
        if ($label === '') {
          $label = 'Hour ' . (int) ($hour->hour_no ?? $hour->id ?? 0);
        }

        if (!empty($hour->start_time) && !empty($hour->end_time)) {
          $label .= ' (' . $hour->start_time . ' - ' . $hour->end_time . ')';
        }

        return (object) [
          'id' => (int) $hour->id,
          'label' => $label,
        ];
      })
      ->values();
  }

  public function getHoursByShift(Request $request)
  {
    try {
      $recId = (int) $request->get('rec_id', 0);
      $shiftSlug = '';

      if ($recId > 0) {
        $routineShift = SubjectHasRoutine::query()
          ->where('id', $recId)
          ->value('shift');

        $shiftSlug = strtolower(trim((string) ($routineShift ?? '')));
      }

      if ($shiftSlug === '') {
        $shiftSlug = strtolower(trim((string) $request->get('shift', '')));
      }

      if ($shiftSlug === '') {
        return response()->json([
          'success' => false,
          'message' => 'Shift is required.'
        ], 422);
      }

      $hours = $this->getShiftTeachingHours($shiftSlug);
      if ($hours->isEmpty()) {
        return response()->json([
          'success' => false,
          'message' => 'No teaching hours available for selected shift.'
        ], 422);
      }

      $hoursPayload = $hours->map(fn($hour) => [
        'id' => (int) $hour->id,
        'label' => (string) $hour->label,
      ])->values();

      return response()->json([
        'success' => true,
        'data' => $hoursPayload,
      ]);
    } catch (\Throwable $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to fetch hours: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Display the attendance interface
   */
  public function index()
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    // Get all subjects assigned to this faculty
    $syllabusAssignments = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->orderBy('syllabus_id')
      ->orderBy('shift')
      ->get()
      ->unique(fn($routine) => (string) ($routine->syllabus_id ?? '0') . '_' . strtolower(trim((string) ($routine->shift ?? 'common'))))
      ->values();

    return view('faculty.attendance.index', [
      'syllabusAssignments' => $syllabusAssignments
    ]);
  }


  /**
   * Store attendance records
   */
  public function storeAttendance(Request $request)
  {
    $request->validate([
      'routine_id' => 'required|exists:subject_has_routines,id',
      'attendance_date' => 'required|date',
      'attendance' => 'required|array',
      'attendance.*' => 'in:present,absent,late,excused',
      'course_id' => 'required',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      $userId = Auth::user()->id;
      $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
      foreach ($request->attendance as $studentId => $status) {
        StudentAttendance::updateOrCreate(
          [
            'routine_id' => $request->routine_id,
            'student_id' => $studentId,
            'attendance_date' => $request->attendance_date,
            'course_id' => $request->course_id,
            'hour_id' => $request->hour_id,
            'faculty_id' => $facultyId,
            'semester_id' => $request->semester_id,
            'batch' => $request->batch,
          ],
          [
            'status' => $status,
            'attendance_method' => 'manual',
          ]
        );
      }

      DB::commit();
      return redirect()
        ->route('faculty.attendance.index')
        ->with('success', 'Attendance recorded successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', 'Failed to record attendance. Please try again.');
    }
  }

  /**
   * View attendance records
   */
  public function viewAttendance(Request $request)
  {

    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    // Get all subjects assigned to this faculty
    $syllabusAssignments = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->get()
      ->unique('syllabus_id');


    $query = StudentAttendance::orderBy('attendance_date', 'desc')
      ->orderBy('hour_id', 'asc')
      ->where('faculty_id', $facultyId);

    if (!empty($request->attendance_date)) {
      $query->where('attendance_date', $request->attendance_date);
    }
    if (!empty($request->course_filter)) {
      $query->where('course_id', $request->course_filter);
    }

    $data = $query->get();


    return view('faculty.attendance.view', [
      'syllabusAssignments' => $syllabusAssignments,
      'attendanceRecords' => $data,

    ]);
  }

  /**
   * Get enrolled students for a subject
   */
  private function getEnrolledStudents($syllabusId)
  {
    return StudentMaster::whereHas('studentsyllabusinfo', function ($query) use ($syllabusId) {
      $query->where('course_id', $syllabusId);
      $query->where('is_deleted', 0);
    })->where('is_deleted', 0)->get();
  }

  /**
   * Delete attendance record
   */
  public function deleteAttendance($id)
  {
    try {
      $attendance = StudentAttendance::findOrFail($id);
      $attendance->delete();

      return back()->with('success', 'Attendance record deleted successfully!');
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to delete attendance record.');
    }
  }

  /**
   * Show attendance creation form for selected subject, hour, and date
   */
  public function getStudentList(Request $request)
  {
    $id = $request->input('rec_id');
    $syllabus_id = $request->input('syllabus_id');
    $hourId = $request->input('hour_id');
    $attendanceDate = $request->input('attendance_date', date('Y-m-d'));
    $semesterId = (int) $request->input('semester_id');
    $batchId = (int) $request->input('batch_id');

    $record =  SubjectHasSyllabus::find($syllabus_id); // Validate syllabus_id
    if (!$record) {
      return back()->with('error', 'Invalid syllabus selected.');
    }

    $routine = SubjectHasRoutine::with(['teachingAssignment:id,allocation_group', 'teachingAllocation:id,allocation_group'])->find($id);
    if (!$routine) {
      return back()->with('error', 'Invalid routine selected.');
    }

    $routineShift = strtolower(trim((string) ($routine->shift ?? 'common')));

    $course_id = $record->course_id;
    $campusId = $record->subject->campus_id;
    $effectiveBatchId = !empty($batchId) ? $batchId : (int) ($record->batch_id ?? 0);
    $effectiveSemesterId = $semesterId > 0 ? $semesterId : (int) ($record->semester_id ?? 0);

    if (empty($effectiveBatchId) || empty($effectiveSemesterId)) {
      return back()->with('error', 'Invalid batch/semester selected.');
    }

    $routineAllocationGroup = (int) (
      $routine->teachingAssignment->allocation_group
      ?? $routine->teachingAllocation->allocation_group
      ?? 0
    );

    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', 'sm.id', '=', 'sci.student_id')
      ->join('student_program as sp', 'sm.new_program_id', '=', 'sp.id')
      ->leftJoin('subject_has_student_progams as shp', function ($join) use ($record, $effectiveBatchId) {
        $join->on('shp.student_program_id', '=', 'sm.new_program_id')
          ->where('shp.subject_id', '=', (int) ($record->subject_id ?? 0))
          ->where('shp.batch_id', '=', $effectiveBatchId);
      })
      ->select(
        'sm.id',
        'sm.roll_no',
        'sm.first_name',
        'sm.last_name',
        'sci.id as course_info_id',
        'sp.shift as program_shift',
        'sm.new_program_id',
        'sci.allocation_group_id',
        'shp.id as program_combo_id'
      )
      ->where('sm.is_left', 0)
      ->where('sm.is_deleted', 0)
      ->where('sm.batch', $effectiveBatchId)
      ->where('sci.course_id', $course_id)
      ->where('sci.semester', $effectiveSemesterId)
      ->where('sci.campus_id', $campusId)
      ->where('sci.is_deleted', 0)
      ->distinct();

    if ($routineAllocationGroup > 0 && Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      $baseQuery->where('sci.allocation_group_id', $routineAllocationGroup);
    }

    // Strict shift matching by enrolled program shift.
    $students = (clone $baseQuery)
      ->whereRaw('LOWER(COALESCE(sp.shift, ?)) = ?', ['common', $routineShift])
      ->get();

    // Specialization-aware filtering: when the course is specialization-linked,
    // show only students assigned to one of the required specializations.
    if ($students->isNotEmpty() && Schema::hasTable('student_specializations')) {
      $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
      $comboIds = $students->pluck('program_combo_id')->filter(fn($v) => (int) $v > 0)->map(fn($v) => (int) $v)->unique()->values();

      if ($comboIds->isNotEmpty() && Schema::hasColumn($curriculumTable, 'program_combo_refid')) {
        $curriculumQuery = DB::table($curriculumTable)
          ->whereIn('program_combo_refid', $comboIds->all())
          ->where('course_id', (int) $course_id)
          ->where('semester', (int) $effectiveSemesterId);

        if (Schema::hasColumn($curriculumTable, 'is_active')) {
          $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn($curriculumTable, 'batch')) {
          $curriculumQuery->where('batch', (int) $effectiveBatchId);
        }

        $curriculumRows = $curriculumQuery->get([
          'program_combo_refid',
          'specialization_mode',
          'specialization_master_id',
          'specialization_master_ids',
        ]);

        $requiredSpecIdsByCombo = [];
        foreach ($curriculumRows as $row) {
          $comboId = (int) ($row->program_combo_refid ?? 0);
          if ($comboId <= 0) {
            continue;
          }

          $mode = strtoupper(trim((string) ($row->specialization_mode ?? 'COMMON')));
          $isCommon = in_array($mode, ['COMMON', 'PROGRAMME_COMMON', 'PROGRAM_COMMON', 'ALL'], true);
          if ($isCommon) {
            continue;
          }

          $specIds = [];
          $singleSpecId = (int) ($row->specialization_master_id ?? 0);
          if ($singleSpecId > 0) {
            $specIds[] = $singleSpecId;
          }

          $rawIds = $row->specialization_master_ids;
          if (is_string($rawIds) && trim($rawIds) !== '') {
            $decoded = json_decode($rawIds, true);
            if (is_array($decoded)) {
              $rawIds = $decoded;
            }
          }

          if (is_array($rawIds)) {
            foreach ($rawIds as $rawId) {
              $sid = (int) $rawId;
              if ($sid > 0) {
                $specIds[] = $sid;
              }
            }
          }

          if (!empty($specIds)) {
            $existing = $requiredSpecIdsByCombo[$comboId] ?? [];
            $requiredSpecIdsByCombo[$comboId] = array_values(array_unique(array_merge($existing, $specIds)));
          }
        }

        if (!empty($requiredSpecIdsByCombo)) {
          $studentIds = $students->pluck('id')->map(fn($v) => (int) $v)->unique()->values();

          $studentSpecQuery = DB::table('student_specializations')
            ->whereIn('student_id', $studentIds->all())
            ->whereIn('subject_has_student_program_id', array_keys($requiredSpecIdsByCombo));

          if (Schema::hasColumn('student_specializations', 'is_active')) {
            $studentSpecQuery->where('is_active', 1);
          }

          if (Schema::hasColumn('student_specializations', 'deleted_at')) {
            $studentSpecQuery->whereNull('deleted_at');
          }

          if (Schema::hasColumn('student_specializations', 'semester_id')) {
            $studentSpecQuery->where(function ($query) use ($effectiveSemesterId) {
              $query->whereNull('semester_id')->orWhere('semester_id', $effectiveSemesterId);
            });
          }

          $studentSpecRows = $studentSpecQuery
            ->select('student_id', 'subject_has_student_program_id', 'specialization_id')
            ->orderByDesc('id')
            ->get();

          $studentSpecLookup = [];
          foreach ($studentSpecRows as $specRow) {
            $studentId = (int) ($specRow->student_id ?? 0);
            $comboId = (int) ($specRow->subject_has_student_program_id ?? 0);
            $specId = (int) ($specRow->specialization_id ?? 0);

            if ($studentId <= 0 || $comboId <= 0 || $specId <= 0) {
              continue;
            }

            if (!isset($studentSpecLookup[$studentId])) {
              $studentSpecLookup[$studentId] = [];
            }
            if (!isset($studentSpecLookup[$studentId][$comboId])) {
              $studentSpecLookup[$studentId][$comboId] = [];
            }

            $studentSpecLookup[$studentId][$comboId][] = $specId;
          }

          $students = $students->filter(function ($student) use ($requiredSpecIdsByCombo, $studentSpecLookup) {
            $comboId = (int) ($student->program_combo_id ?? 0);

            // If no specialization is required for this combo, keep student.
            if ($comboId <= 0 || !isset($requiredSpecIdsByCombo[$comboId])) {
              return true;
            }

            $required = $requiredSpecIdsByCombo[$comboId] ?? [];
            if (empty($required)) {
              return true;
            }

            $studentId = (int) ($student->id ?? 0);
            $studentSpecs = $studentSpecLookup[$studentId][$comboId] ?? [];

            if (empty($studentSpecs)) {
              return false;
            }

            return !empty(array_intersect($required, array_map(fn($v) => (int) $v, $studentSpecs)));
          })->values();
        }
      }
    }

    $syllabusAssignment = SubjectHasSyllabus::with([
      'courseLink.courseMaster.coursetypemaster',
      'semestermaster:id,title',
      'batchmaster'
    ])->find($syllabus_id);

    $availableHours = $this->getShiftTeachingHours($routineShift);

    // Get existing attendance for this date/hour/routine
    $existingAttendance = StudentAttendance::where('routine_id', $id)
      ->where('attendance_date', $attendanceDate)
      ->where('hour_id', $hourId)
      ->get()
      ->keyBy('student_id');

    if ($request->attendance_type == 'regular') {

      return view('faculty.attendance.take', [
        'students' => $students,
        'recordId' => $id,
        'routineShift' => ucfirst($routineShift),
        'syllabusId' => $syllabus_id,
        'hourId' => $hourId,
        'attendanceDate' => $attendanceDate,
        'batchId' => $effectiveBatchId,
        'syllabusAssignment' => $syllabusAssignment,
        'course_id' => $course_id,
        'semesterId' => $effectiveSemesterId,
        'existingAttendance' => $existingAttendance,
        'availableHours' => $availableHours,
      ]);
    } else {
      return view('faculty.attendance.extra.take', [
        'students' => $students,
        'recordId' => $id,
        'routineShift' => ucfirst($routineShift),
        'syllabusId' => $syllabus_id,
        'hourId' => $hourId,
        'attendanceDate' => $attendanceDate,
        'batchId' => $effectiveBatchId,
        'syllabusAssignment' => $syllabusAssignment,
        'course_id' => $course_id,
        'semesterId' => $effectiveSemesterId,
        'existingAttendance' => $existingAttendance,
        'availableHours' => $availableHours,
      ]);
    }
  }

  function updateAttendance(Request $request, $id)
  {

    $request->validate([
      'attendance_date' => 'required',
      'extra.*' => 'in:late,excused',
      'status' => 'required|in:present,absent',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      if (!empty($request->extra)) {
        $extra = $request->extra;
      } else {
        $extra = null;
      }
      StudentAttendance::updateOrCreate(
        [
          'id' => $id,
        ],
        [
          'status' => $request->status,
          'attendance_method' => 'manual',
          'extra' => $extra,
        ]
      );


      DB::commit();
      return redirect()
        ->back()
        ->with('success', 'Attendance updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', 'Failed to update attendance. Please try again.');
    }
  }


  function extraClasses()
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    // Get all subjects assigned to this faculty
    $syllabusAssignments = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->get()
      ->unique('syllabus_id');

    return view('faculty.attendance.extra.index', [
      'syllabusAssignments' => $syllabusAssignments
    ]);
  }

  /**
   * Store remedial class attendance records
   */
  public function storeRemedialAttendance(Request $request)
  {
    $request->validate([
      'routine_id' => 'required|exists:subject_has_routines,id',
      'attendance_date' => 'required|date',
      'attendance' => 'required|array',
      'attendance.*' => 'in:present,absent',
      'course_id' => 'required',
    ]);

    // Check if the selected date is Sunday
    $attendanceDate = Carbon::parse($request->attendance_date);
    if ($attendanceDate->isSunday()) {
      return back()
        ->withInput()
        ->with('error', 'Cannot record attendance for Sunday. Sunday is a holiday.');
    }

    DB::beginTransaction();
    try {
      $userId = Auth::user()->id;
      $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

      // Ensure all required fields are present and properly typed
      if (!$facultyId) {
        throw new \Exception('Faculty ID not found for current user');
      }

      foreach ($request->attendance as $studentId => $status) {
        if ($status !== 'present') {
          continue;
        }
        ExtraClassAttendance::updateOrCreate(
          [
            'routine_id' => (int) $request->routine_id,
            'student_id' => (int) $studentId,
            'attendance_date' => $request->attendance_date,
            'course_id' => (int) $request->course_id,
            'hour_id' => $request->hour_id ? (int) $request->hour_id : null,
            'faculty_id' => (int) $facultyId,
            'semester_id' => (int) $request->semester_id,
            'batch' => $request->batch,
          ],
          [
            'status' => $status,
            'attendance_method' => 'manual',
          ]
        );
      }

      DB::commit();
      return redirect()
        ->route('faculty.attendance.view.remedial-class')
        ->with('success', 'Remedial Class Attendance recorded successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Extra Class Attendance Error: ' . $e->getMessage());
      return back()->with('error', 'Failed to record attendance: ' . $e->getMessage());
    }
  }


  /**
   * View attendance records
   */
  public function viewExtraClassAttendance(Request $request)
  {

    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    // Get all subjects assigned to this faculty
    $syllabusAssignments = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->get()
      ->unique('syllabus_id');


    $query = ExtraClassAttendance::orderBy('attendance_date', 'desc')
      ->orderBy('hour_id', 'asc')
      ->where('faculty_id', $facultyId);

    if (!empty($request->attendance_date)) {
      $query->where('attendance_date', $request->attendance_date);
    }
    if (!empty($request->course_filter)) {
      $query->where('course_id', $request->course_filter);
    }

    $data = $query->get();


    return view('faculty.attendance.extra.view', [
      'syllabusAssignments' => $syllabusAssignments,
      'attendanceRecords' => $data,

    ]);
  }
}
