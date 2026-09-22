<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Program;
use App\Models\ExamSystem\ProgramRegulation;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\Registration;
use App\Models\ProgramCourseMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CoeExamController extends Controller
{
  private const ELIGIBILITY_THRESHOLD = 75;

  private ?bool $hasAssessmentTypeColumn = null;
  private ?bool $hasExamPublishColumns = null;
  private ?bool $hasExamRegistrationModeColumn = null;

  private function canUseAssessmentType(): bool
  {
    if ($this->hasAssessmentTypeColumn === null) {
      $this->hasAssessmentTypeColumn = Schema::hasColumn('exams', 'assessment_type');
    }

    return $this->hasAssessmentTypeColumn;
  }

  private function canUseExamPublish(): bool
  {
    if ($this->hasExamPublishColumns === null) {
      $this->hasExamPublishColumns = Schema::hasColumn('exams', 'is_published');
    }

    return $this->hasExamPublishColumns;
  }

  private function canUseExamRegistrationMode(): bool
  {
    if ($this->hasExamRegistrationModeColumn === null) {
      $this->hasExamRegistrationModeColumn = Schema::hasColumn('exams', 'registration_mode');
    }

    return $this->hasExamRegistrationModeColumn;
  }

  private function resolveAssessmentModule(?string $module): ?string
  {
    if (!$module) {
      return null;
    }

    $normalized = strtoupper(str_replace('-', '', trim($module)));
    if ($normalized === 'FA2') {
      return 'FA2';
    }

    if ($normalized === 'SA') {
      return 'SA';
    }

    return null;
  }

  /**
   * Display a listing of exams
   */
  public function index(Request $request)
  {
    $module = $this->resolveAssessmentModule($request->input('module'));
    $query = Exam::with(['program', 'regulation']);

    if ($module && $this->canUseAssessmentType()) {
      $query->where('assessment_type', $module);
    }

    // Apply filters
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('exam_type')) {
      $query->where('exam_type', $request->exam_type);
    }

    if ($request->filled('program_id')) {
      $query->where('program_id', $request->program_id);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('exam_type', 'like', "%{$search}%");
      });
    }

    $exams = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();

    $programTypeSub = DB::table('student_course_rosters')
      ->select([
        'student_id',
        DB::raw('MAX(UPPER(COALESCE(program_type, ""))) as program_type'),
      ])
      ->whereNull('deleted_at')
      ->groupBy('student_id');

    $eligibilityBase = DB::table('student_attendances as sa')
      ->join('student_masters as sm', 'sm.id', '=', 'sa.student_id')
      ->leftJoin('batch_masters as bm', 'bm.id', '=', 'sa.batch')
      ->leftJoin('semesters as sem', 'sem.id', '=', 'sa.semester_id')
      ->leftJoinSub($programTypeSub, 'spt', function ($join) {
        $join->on('spt.student_id', '=', 'sa.student_id');
      })
      ->whereNotNull('sa.student_id')
      ->whereNotNull('sa.course_id');

    if (Schema::hasColumn('student_attendances', 'deleted_at')) {
      $eligibilityBase->whereNull('sa.deleted_at');
    }

    $groupedEligibility = (clone $eligibilityBase)
      ->select([
        'sa.student_id',
        'sm.roll_no',
        'sm.register_no',
        'sm.first_name',
        'sm.last_name',
        'sa.batch as batch_id',
        'bm.batch_name',
        'sa.semester_id',
        'sem.title as semester_title',
        DB::raw('COALESCE(spt.program_type, "-") as program_type'),
        DB::raw("ROUND((SUM(CASE WHEN sa.status IN ('present','late','excused') THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) as attendance_percentage"),
      ])
      ->groupBy(
        'sa.student_id',
        'sm.roll_no',
        'sm.register_no',
        'sm.first_name',
        'sm.last_name',
        'sa.batch',
        'bm.batch_name',
        'sa.semester_id',
        'sem.title',
        'spt.program_type'
      );

    $eligibleStudentsCount = (clone $groupedEligibility)
      ->havingRaw(
        "ROUND((SUM(CASE WHEN sa.status IN ('present','late','excused') THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) >= ?",
        [self::ELIGIBILITY_THRESHOLD]
      )
      ->get()
      ->count();

    $unallowedStudentsCount = (clone $groupedEligibility)
      ->havingRaw(
        "ROUND((SUM(CASE WHEN sa.status IN ('present','late','excused') THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) < ?",
        [self::ELIGIBILITY_THRESHOLD]
      )
      ->get()
      ->count();

    $exams->setCollection(
      $exams->getCollection()->map(function ($exam) use ($eligibleStudentsCount, $unallowedStudentsCount) {
        $exam->eligible_students_count = (int) $eligibleStudentsCount;
        $exam->unallowed_students_count = (int) $unallowedStudentsCount;
        return $exam;
      })
    );

    // Calculate statistics
    $statsQuery = Exam::query();
    if ($module && $this->canUseAssessmentType()) {
      $statsQuery->where('assessment_type', $module);
    }

    $totalExams = (clone $statsQuery)->count();
    $upcomingExams = (clone $statsQuery)->where('status', 'upcoming')->count();
    $ongoingExams = (clone $statsQuery)->where('status', 'ongoing')->count();
    $completedExams = (clone $statsQuery)->where('status', 'completed')->count();

    // Load programs for filter dropdown
    $programs = Program::orderBy('name')->get();

    return view('coe.exams.index', compact(
      'exams',
      'totalExams',
      'upcomingExams',
      'ongoingExams',
      'completedExams',
      'programs',
      'module'
    ));
  }

  /**
   * Show the form for creating a new exam
   */
  public function create()
  {
    $module = $this->resolveAssessmentModule(request()->input('module')) ?? 'SA';
    $programs = Program::orderBy('name')->get();

    return view('coe.exams.create', compact('programs', 'module'));
  }

  /**
   * Show ongoing courses with roster enrollment counts.
   */
  public function ongoingCourses(Request $request)
  {
    $search = trim((string) $request->query('search', ''));
    $programType = strtoupper(trim((string) $request->query('program_type', '')));
    $batchId = (int) $request->query('batch_id', 0);
    $semesterId = (int) $request->query('semester_id', 0);

    $query = DB::table('student_course_rosters as scr')
      ->join('program_course_masters as pcm', 'pcm.id', '=', 'scr.course_id')
      ->leftJoin('teaching_assignments as ta', 'ta.id', '=', 'scr.ta_id')
      ->leftJoin('batch_masters as bm', 'bm.id', '=', 'scr.batch_id')
      ->leftJoin('semesters as sem', 'sem.id', '=', 'scr.semester_id')
      ->whereNull('scr.deleted_at')
      ->whereNull('pcm.deleted_at')
      ->where(function ($builder) {
        $builder->whereNull('ta.id')
          ->orWhere(function ($activeAssignment) {
            $activeAssignment->whereNull('ta.deleted_at')
              ->where('ta.is_active', 1);
          });
      });

    if ($search !== '') {
      $query->where(function ($builder) use ($search) {
        $builder->where('pcm.course_code', 'like', '%' . $search . '%')
          ->orWhere('pcm.course_title', 'like', '%' . $search . '%');
      });
    }

    if ($programType !== '') {
      $query->whereRaw('UPPER(COALESCE(scr.program_type, "")) = ?', [$programType]);
    }

    if ($batchId > 0) {
      $query->where('scr.batch_id', $batchId);
    }

    if ($semesterId > 0) {
      $query->where('scr.semester_id', $semesterId);
    }

    $programTypes = (clone $query)
      ->whereNotNull('scr.program_type')
      ->where('scr.program_type', '!=', '')
      ->select(DB::raw('UPPER(scr.program_type) as program_type'))
      ->groupBy(DB::raw('UPPER(scr.program_type)'))
      ->orderBy(DB::raw('UPPER(scr.program_type)'))
      ->pluck('program_type');

    $batches = (clone $query)
      ->whereNotNull('scr.batch_id')
      ->select('scr.batch_id', 'bm.batch_name')
      ->groupBy('scr.batch_id', 'bm.batch_name')
      ->orderByDesc('scr.batch_id')
      ->get();

    $semesters = (clone $query)
      ->whereNotNull('scr.semester_id')
      ->select('scr.semester_id', 'sem.title as semester_title')
      ->groupBy('scr.semester_id', 'sem.title')
      ->orderBy('scr.semester_id')
      ->get();

    $ongoingCourses = $query
      ->select([
        'scr.course_id',
        'scr.program_type',
        'scr.batch_id',
        'bm.batch_name',
        'scr.semester_id',
        'sem.title as semester_title',
        'pcm.course_code',
        'pcm.course_title',
        DB::raw('COUNT(DISTINCT scr.student_id) as enrolled_students'),
      ])
      ->groupBy(
        'scr.course_id',
        'scr.program_type',
        'scr.batch_id',
        'bm.batch_name',
        'scr.semester_id',
        'sem.title',
        'pcm.course_code',
        'pcm.course_title'
      )
      ->orderByRaw('COUNT(DISTINCT scr.student_id) DESC')
      ->orderBy('pcm.course_code')
      ->get();

    $summary = [
      'course_count' => (clone $query)
        ->select('scr.course_id')
        ->groupBy('scr.course_id')
        ->get()
        ->count(),
      'student_count' => (int) ((clone $query)->distinct('scr.student_id')->count('scr.student_id')),
    ];

    return view('coe.exams.ongoing-courses', compact(
      'ongoingCourses',
      'summary',
      'search',
      'programTypes',
      'batches',
      'semesters',
      'programType',
      'batchId',
      'semesterId'
    ));
  }

  /**
   * Google-style dynamic exam calendar page.
   */
  public function calendar(Request $request)
  {
    $module = $this->resolveAssessmentModule($request->query('module'));
    $requestedExamId = (int) $request->query('exam_id', 0);

    $examsQuery = Exam::query();
    if ($module && $this->canUseAssessmentType()) {
      $examsQuery->where('assessment_type', $module);
    }

    $exams = $examsQuery->orderByDesc('start_date')->get(['id', 'name', 'assessment_type', 'start_date', 'end_date']);
    $initialExamId = ($requestedExamId > 0 && $exams->contains('id', $requestedExamId)) ? $requestedExamId : null;

    $courses = ProgramCourseMaster::query()
      ->select(['id', 'course_code', 'course_title'])
      ->where(function ($query) {
        $query->whereNull('is_deleted')->orWhere('is_deleted', 0);
      })
      ->orderBy('course_code')
      ->limit(2000)
      ->get();

    return view('coe.exams.calendar', compact('exams', 'courses', 'module', 'initialExamId'));
  }

  /**
   * Fetch calendar entries for FullCalendar.
   */
  public function calendarEvents(Request $request)
  {
    $query = DB::table('exam_calendar_entries as ece')
      ->join('exams as e', 'e.id', '=', 'ece.exam_id')
      ->leftJoin('program_course_masters as pcm', 'pcm.id', '=', 'ece.course_id')
      ->select([
        'ece.id',
        'ece.exam_id',
        'ece.course_id',
        'ece.course_code',
        'ece.title',
        'ece.exam_date',
        'ece.start_time',
        'ece.end_time',
        'ece.notes',
        'e.name as exam_name',
        DB::raw('COALESCE(pcm.course_code, ece.course_code) as effective_course_code'),
      ]);

    if ($request->filled('exam_id')) {
      $query->where('ece.exam_id', (int) $request->query('exam_id'));
    }

    if ($request->filled('start')) {
      $query->whereDate('ece.exam_date', '>=', $request->query('start'));
    }

    if ($request->filled('end')) {
      $query->whereDate('ece.exam_date', '<=', $request->query('end'));
    }

    $rows = $query->orderBy('ece.exam_date')->orderBy('ece.start_time')->get();

    $events = $rows->map(function ($row) {
      $courseCode = trim((string) ($row->effective_course_code ?? ''));
      $examName = trim((string) ($row->exam_name ?? ''));

      return [
        'id' => (string) $row->id,
        'title' => $row->title,
        'start' => $row->exam_date . 'T' . $row->start_time,
        'end' => $row->exam_date . 'T' . $row->end_time,
        'extendedProps' => [
          'exam_id' => (int) $row->exam_id,
          'course_id' => $row->course_id ? (int) $row->course_id : null,
          'course_code' => $courseCode,
          'exam_name' => $examName,
          'notes' => $row->notes,
        ],
      ];
    });

    return response()->json($events);
  }

  /**
   * Store calendar entry.
   */
  public function calendarStore(Request $request)
  {
    $validated = $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'course_id' => 'nullable|exists:program_course_masters,id',
      'title' => 'required|string|max:255',
      'exam_date' => 'required|date',
      'start_time' => 'required|date_format:H:i',
      'end_time' => 'required|date_format:H:i|after:start_time',
      'notes' => 'nullable|string|max:2000',
    ]);

    $courseCode = null;
    if (!empty($validated['course_id'])) {
      $courseCode = ProgramCourseMaster::where('id', (int) $validated['course_id'])->value('course_code');
    }

    $id = DB::table('exam_calendar_entries')->insertGetId([
      'exam_id' => (int) $validated['exam_id'],
      'course_id' => $validated['course_id'] ? (int) $validated['course_id'] : null,
      'course_code' => $courseCode,
      'title' => $validated['title'],
      'exam_date' => $validated['exam_date'],
      'start_time' => $validated['start_time'],
      'end_time' => $validated['end_time'],
      'notes' => $validated['notes'] ?? null,
      'created_by' => auth()->id(),
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    return response()->json([
      'success' => true,
      'id' => $id,
      'message' => 'Exam timetable entry added successfully.',
    ]);
  }

  /**
   * Update calendar entry.
   */
  public function calendarUpdate(Request $request, $id)
  {
    $validated = $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'course_id' => 'nullable|exists:program_course_masters,id',
      'title' => 'required|string|max:255',
      'exam_date' => 'required|date',
      'start_time' => 'required|date_format:H:i',
      'end_time' => 'required|date_format:H:i|after:start_time',
      'notes' => 'nullable|string|max:2000',
    ]);

    $courseCode = null;
    if (!empty($validated['course_id'])) {
      $courseCode = ProgramCourseMaster::where('id', (int) $validated['course_id'])->value('course_code');
    }

    DB::table('exam_calendar_entries')
      ->where('id', (int) $id)
      ->update([
        'exam_id' => (int) $validated['exam_id'],
        'course_id' => $validated['course_id'] ? (int) $validated['course_id'] : null,
        'course_code' => $courseCode,
        'title' => $validated['title'],
        'exam_date' => $validated['exam_date'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'notes' => $validated['notes'] ?? null,
        'updated_at' => now(),
      ]);

    return response()->json([
      'success' => true,
      'message' => 'Exam timetable entry updated successfully.',
    ]);
  }

  /**
   * Delete calendar entry.
   */
  public function calendarDestroy($id)
  {
    DB::table('exam_calendar_entries')->where('id', (int) $id)->delete();

    return response()->json([
      'success' => true,
      'message' => 'Exam timetable entry deleted successfully.',
    ]);
  }

  /**
   * Import timetable entries from Excel.
   */
  public function calendarImport(Request $request)
  {
    $validated = $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'file' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    $sheets = Excel::toArray([], $request->file('file'));
    $rows = $sheets[0] ?? [];

    if (count($rows) < 2) {
      return redirect()->back()->with('error', 'Excel file is empty or missing data rows.');
    }

    $headers = array_map(function ($value) {
      return strtolower(trim((string) $value));
    }, $rows[0]);

    $inserted = 0;
    foreach (array_slice($rows, 1) as $row) {
      if (!is_array($row)) {
        continue;
      }

      $data = [];
      foreach ($headers as $index => $header) {
        if ($header === '') {
          continue;
        }
        $data[$header] = $row[$index] ?? null;
      }

      $title = trim((string) ($data['title'] ?? $data['course_title'] ?? ''));
      $courseCode = trim((string) ($data['course_code'] ?? ''));
      $dateRaw = $data['exam_date'] ?? $data['date'] ?? null;
      $startRaw = $data['start_time'] ?? null;
      $endRaw = $data['end_time'] ?? null;
      $hoursRaw = $data['hours'] ?? $data['duration_hours'] ?? null;

      if ($dateRaw === null || $startRaw === null) {
        continue;
      }

      if ($title === '') {
        $title = $courseCode !== '' ? ($courseCode . ' Exam') : 'Exam Slot';
      }

      $examDate = $this->normalizeExcelDate($dateRaw);
      $startTime = $this->normalizeExcelTime($startRaw);
      $endTime = null;

      if ($endRaw !== null && $endRaw !== '') {
        $endTime = $this->normalizeExcelTime($endRaw);
      } else {
        $endTime = $this->calculateEndTimeFromHours($startTime, $hoursRaw);
      }

      if (!$examDate || !$startTime || !$endTime || $startTime >= $endTime) {
        continue;
      }

      $courseId = null;
      if ($courseCode !== '') {
        $courseId = ProgramCourseMaster::where('course_code', $courseCode)->value('id');
      }

      DB::table('exam_calendar_entries')->insert([
        'exam_id' => (int) $validated['exam_id'],
        'course_id' => $courseId,
        'course_code' => $courseCode !== '' ? $courseCode : null,
        'title' => $title,
        'exam_date' => $examDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'notes' => null,
        'created_by' => auth()->id(),
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      $inserted++;
    }

    return redirect()->back()->with('success', $inserted . ' timetable entries imported successfully.');
  }

  /**
   * Download template file for calendar import.
   */
  public function calendarTemplateDownload()
  {
    $filename = 'exam_timetable_template.csv';

    return response()->streamDownload(function () {
      $output = fopen('php://output', 'w');
      fputcsv($output, ['course_code', 'date', 'start_time', 'hours']);
      fputcsv($output, ['CS101', '2026-10-01', '10:00', '2']);
      fclose($output);
    }, $filename, [
      'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
  }

  private function calculateEndTimeFromHours(?string $startTime, $hours): ?string
  {
    if (!$startTime || $hours === null || $hours === '') {
      return null;
    }

    $hoursFloat = (float) $hours;
    if ($hoursFloat <= 0) {
      return null;
    }

    try {
      $start = Carbon::createFromFormat('H:i:s', $startTime);
      return $start->copy()->addMinutes((int) round($hoursFloat * 60))->format('H:i:s');
    } catch (\Throwable $e) {
      return null;
    }
  }

  private function normalizeExcelDate($value): ?string
  {
    if ($value === null || $value === '') {
      return null;
    }

    if (is_numeric($value)) {
      return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
    }

    try {
      return Carbon::parse((string) $value)->format('Y-m-d');
    } catch (\Throwable $e) {
      return null;
    }
  }

  private function normalizeExcelTime($value): ?string
  {
    if ($value === null || $value === '') {
      return null;
    }

    if (is_numeric($value)) {
      return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('H:i:s');
    }

    try {
      return Carbon::parse((string) $value)->format('H:i:s');
    } catch (\Throwable $e) {
      return null;
    }
  }

  /**
   * Store a newly created exam
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'assessment_type' => 'required|in:SA,FA2',
      'exam_type' => 'required|string|in:Regular,Backlog,Improvement,Special',
      'semester' => 'required|in:Odd,Even',
      'program_type' => 'required|in:UG,PG',
      'registration_mode' => 'required|in:registration_required,auto_registered',
      'program_id' => 'nullable|exists:programs,id',
      'regulation_id' => 'nullable|exists:program_regulations,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'exam_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
      'status' => 'required|in:upcoming,ongoing,completed,cancelled'
    ]);

    try {
      if (!$this->canUseAssessmentType()) {
        unset($validated['assessment_type']);
      }

      if (!$this->canUseExamRegistrationMode()) {
        unset($validated['registration_mode']);
      }

      if ($this->canUseExamPublish()) {
        $validated['is_published'] = false;
        if (Schema::hasColumn('exams', 'published_at')) {
          $validated['published_at'] = null;
        }
      }

      if (empty($validated['program_id'])) {
        $selectedType = strtoupper((string) $validated['program_type']);

        $resolvedProgram = Program::whereRaw('UPPER(COALESCE(type, "")) = ?', [$selectedType])
          ->orderBy('id')
          ->first();

        if (!$resolvedProgram) {
          $resolvedProgram = Program::where(function ($query) use ($selectedType) {
            $query->whereRaw('UPPER(COALESCE(name, "")) LIKE ?', ['%' . $selectedType . '%'])
              ->orWhereRaw('UPPER(COALESCE(code, "")) LIKE ?', ['%' . $selectedType . '%']);
          })
            ->orderBy('id')
            ->first();
        }

        if (!$resolvedProgram) {
          $resolvedProgram = Program::orderBy('id')->first();
        }

        if (!$resolvedProgram) {
          $autoCode = 'AUTO_' . strtoupper((string) $validated['program_type']) . '_' . now()->format('YmdHis');
          $resolvedProgram = Program::create([
            'name' => strtoupper((string) $validated['program_type']) . ' Program',
            'code' => $autoCode,
            'type' => strtoupper((string) $validated['program_type']),
          ]);
        }

        $validated['program_id'] = $resolvedProgram->id;
      }

      if (empty($validated['regulation_id'])) {
        $resolvedRegulation = ProgramRegulation::where('program_id', $validated['program_id'])
          ->orderByDesc('id')
          ->first();

        if (!$resolvedRegulation) {
          $startYear = (int) date('Y', strtotime((string) $validated['start_date']));
          $selectedType = strtoupper((string) ($validated['program_type'] ?? 'UG'));

          $resolvedRegulation = ProgramRegulation::create([
            'program_id' => $validated['program_id'],
            'regulation_name' => 'Auto ' . $selectedType . ' Regulation ' . $startYear,
            'regulation_type' => 'NEP',
            'start_year' => $startYear,
            'end_year' => null,
          ]);
        }

        $validated['regulation_id'] = $resolvedRegulation->id;
      }

      unset($validated['program_type']);

      $exam = Exam::create($validated);

      return redirect()
        ->route('coe.exams.show', ['id' => $exam->id, 'module' => $validated['assessment_type'] ?? 'SA'])
        ->with('success', 'Exam created successfully!');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to create exam: ' . $e->getMessage());
    }
  }

  /**
   * Display the specified exam
   */
  public function show($id)
  {
    $module = $this->resolveAssessmentModule(request()->input('module'));
    $hasExamRegistrationLink = Schema::hasColumn('exam_registrations', 'exam_id');

    $relations = ['program', 'regulation'];
    if ($hasExamRegistrationLink) {
      $relations[] = 'registrations';
    }

    $exam = Exam::with($relations)->findOrFail($id);

    if (!$module) {
      $module = $exam->assessment_type ?? 'SA';
    }

    // Calculate attendance statistics
    $attendanceStats = [
      'present' => ExamAttendance::where('exam_id', $id)
        ->where('status', 'present')
        ->count(),
      'absent' => ExamAttendance::where('exam_id', $id)
        ->where('status', 'absent')
        ->count(),
      'total' => ExamAttendance::where('exam_id', $id)->count(),
    ];

    // Calculate percentage
    if ($attendanceStats['total'] > 0) {
      $attendanceStats['percentage'] = round(
        ($attendanceStats['present'] / $attendanceStats['total']) * 100,
        2
      );
    } else {
      $attendanceStats['percentage'] = 0;
    }

    // Duty statistics (placeholder - adjust based on your duty tables)
    $dutyStats = [
      'invigilation' => 0,
      'evaluation' => 0,
      'moderation' => 0,
    ];

    $registrationCount = 0;
    $eligibleStudentCount = 0;
    $registrationPreview = collect();
    if ($hasExamRegistrationLink) {
      $registrationCount = Registration::where('exam_id', $id)->count();
      $registrationPreview = Registration::with('student')
        ->where('exam_id', $id)
        ->latest('id')
        ->take(10)
        ->get();
    }

    // Keep exam-details eligibility aligned with attendance eligibility (>= 75%).
    $programTypeSub = DB::table('student_course_rosters')
      ->select([
        'student_id',
        DB::raw('MAX(UPPER(COALESCE(program_type, ""))) as program_type'),
      ])
      ->whereNull('deleted_at')
      ->groupBy('student_id');

    $eligibleStudents = DB::table('student_attendances as sa')
      ->join('student_masters as sm', 'sm.id', '=', 'sa.student_id')
      ->leftJoin('batch_masters as bm', 'bm.id', '=', 'sa.batch')
      ->leftJoin('semesters as sem', 'sem.id', '=', 'sa.semester_id')
      ->leftJoinSub($programTypeSub, 'spt', function ($join) {
        $join->on('spt.student_id', '=', 'sa.student_id');
      })
      ->whereNotNull('sa.student_id')
      ->whereNotNull('sa.course_id');

    if (Schema::hasColumn('student_attendances', 'deleted_at')) {
      $eligibleStudents->whereNull('sa.deleted_at');
    }

    $eligibleStudentCount = $eligibleStudents
      ->select([
        'sa.student_id',
        'sm.roll_no',
        'sm.register_no',
        'sm.first_name',
        'sm.last_name',
        'sa.batch as batch_id',
        'bm.batch_name',
        'sa.semester_id',
        'sem.title as semester_title',
        DB::raw('COALESCE(spt.program_type, "-") as program_type'),
        DB::raw("ROUND((SUM(CASE WHEN sa.status IN ('present','late','excused') THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) as attendance_percentage"),
      ])
      ->groupBy(
        'sa.student_id',
        'sm.roll_no',
        'sm.register_no',
        'sm.first_name',
        'sm.last_name',
        'sa.batch',
        'bm.batch_name',
        'sa.semester_id',
        'sem.title',
        'spt.program_type'
      )
      ->havingRaw(
        "ROUND((SUM(CASE WHEN sa.status IN ('present','late','excused') THEN 1 ELSE 0 END) / NULLIF(COUNT(sa.id), 0)) * 100, 2) >= ?",
        [self::ELIGIBILITY_THRESHOLD]
      )
      ->get()
      ->count();

    return view('coe.exams.show', compact(
      'exam',
      'attendanceStats',
      'dutyStats',
      'module',
      'registrationCount',
      'eligibleStudentCount',
      'registrationPreview'
    ));
  }

  /**
   * Show the form for editing the specified exam
   */
  public function edit($id)
  {
    $module = $this->resolveAssessmentModule(request()->input('module'));
    $exam = Exam::findOrFail($id);
    if (!$module) {
      $module = $exam->assessment_type ?? 'SA';
    }
    $programs = Program::orderBy('name')->get();
    $regulations = ProgramRegulation::orderBy('regulation_name')->get();

    return view('coe.exams.edit', compact('exam', 'programs', 'regulations', 'module'));
  }

  /**
   * Update the specified exam
   */
  public function update(Request $request, $id)
  {
    $exam = Exam::findOrFail($id);

    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'assessment_type' => 'required|in:SA,FA2',
      'exam_type' => 'required|string|in:Regular,Backlog,Improvement,Special',
      'semester' => 'required|in:Odd,Even',
      'registration_mode' => 'required|in:registration_required,auto_registered',
      'program_id' => 'nullable|exists:programs,id',
      'regulation_id' => 'nullable|exists:program_regulations,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'exam_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
      'status' => 'required|in:upcoming,ongoing,completed,cancelled'
    ]);

    try {
      if (!$this->canUseAssessmentType()) {
        unset($validated['assessment_type']);
      }

      if (!$this->canUseExamRegistrationMode()) {
        unset($validated['registration_mode']);
      }

      $exam->update($validated);

      return redirect()
        ->route('coe.exams.show', ['id' => $exam->id, 'module' => $validated['assessment_type']])
        ->with('success', 'Exam updated successfully!');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to update exam: ' . $e->getMessage());
    }
  }

  /**
   * Toggle exam publish status for student visibility.
   */
  public function togglePublish($id)
  {
    if (!$this->canUseExamPublish()) {
      return redirect()->back()->with('error', 'Publish feature is not available until migration is applied.');
    }

    $exam = Exam::findOrFail($id);
    $publishNow = !((bool) ($exam->is_published ?? false));

    $payload = ['is_published' => $publishNow];
    if (Schema::hasColumn('exams', 'published_at')) {
      $payload['published_at'] = $publishNow ? now() : null;
    }

    $exam->update($payload);

    return redirect()->back()->with('success', $publishNow
      ? 'Exam published successfully. It is now visible to students.'
      : 'Exam unpublished successfully. It is now hidden from students.');
  }

  /**
   * Remove the specified exam
   */
  public function destroy($id)
  {
    try {
      $exam = Exam::findOrFail($id);

      // Check if there are any attendance records
      $attendanceCount = ExamAttendance::where('exam_id', $id)->count();

      if ($attendanceCount > 0) {
        return redirect()
          ->back()
          ->with('error', 'Cannot delete exam with existing attendance records. Please delete attendance records first.');
      }

      $examName = $exam->name;
      $exam->delete();

      return redirect()
        ->route('coe.exams.index', ['module' => $exam->assessment_type ?? 'SA'])
        ->with('success', "Exam '{$examName}' deleted successfully!");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to delete exam: ' . $e->getMessage());
    }
  }
}
