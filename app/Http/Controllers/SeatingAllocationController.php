<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ExamOperationalSession;
use App\Models\ExamSystem\ExamRoomGeneratedSeat;
use App\Models\ExamSystem\ExamSchedule;
use App\Models\ExamSystem\SeatingAllocation;
use App\Models\ExamSystem\Room;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SeatingAllocationController extends Controller
{
  public function index(Request $request)
  {
    $this->syncOperationalSessionsFromSchedules();

    $query = SeatingAllocation::with([
      'examSchedule.exam',
      'room',
      'examStudent.student.campusmaster',
    ]);

    if ($request->filled('exam_id')) {
      $query->where('exam_schedule_id', $request->exam_id);
    }

    if ($request->filled('exam_master_id')) {
      $examMasterId = (int) $request->input('exam_master_id');
      $query->whereHas('examSchedule', function ($scheduleQuery) use ($examMasterId) {
        $scheduleQuery->where('exam_id', $examMasterId);
      });
    }

    if ($request->filled('room_id')) {
      $query->where('room_id', $request->room_id);
    }

    if (
      $request->filled('status') &&
      Schema::hasColumn('seating_allocations', 'status') &&
      in_array((string) $request->input('status'), ['draft', 'finalized'], true)
    ) {
      $query->where('status', (string) $request->input('status'));
    }

    $allocations = $query->orderBy('seat_no')->paginate(50);

    $exams = ExamSchedule::with('exam:id,name,exam_type')
      ->orderByDesc('exam_date')
      ->orderBy('start_time')
      ->get();

    $rooms = Room::orderBy('building')->orderBy('room_number')->get();

    $selectedExamMasterId = (int) $request->input('exam_master_id', 0);
    $selectedExamLabel = null;

    if ($request->filled('exam_id')) {
      $selectedSchedule = $exams->firstWhere('id', (int) $request->input('exam_id'));
      if ($selectedSchedule && $selectedSchedule->exam) {
        $selectedExamLabel = $selectedSchedule->exam->name;
        if ($selectedExamMasterId <= 0) {
          $selectedExamMasterId = (int) ($selectedSchedule->exam_id ?? 0);
        }
      }
    }

    if ($selectedExamLabel === null && $selectedExamMasterId > 0) {
      $selectedExam = Exam::query()->select('id', 'name')->find($selectedExamMasterId);
      if ($selectedExam) {
        $selectedExamLabel = $selectedExam->name;
      }
    }

    $operationalSessions = $this->getExamSessionUnits(
      null,
      null,
      $selectedExamMasterId > 0 ? $selectedExamMasterId : null
    );

    $selectedOperationalSessionId = (int) $request->input('operational_session_id', 0);
    $generatedSeatSummary = collect();
    if ($selectedOperationalSessionId > 0) {
      $generatedSeatSummary = ExamRoomGeneratedSeat::query()
        ->select('room_id', DB::raw('COUNT(*) as generated_count'))
        ->where('exam_operational_session_id', $selectedOperationalSessionId)
        ->groupBy('room_id')
        ->with('room:id,name,room_no,room_number,building')
        ->get();
    }

    $availableRoomsForExam = collect();
    if ($selectedExamMasterId > 0) {
      $allocatedRoomIdsForExam = SeatingAllocation::query()
        ->join('exam_schedules as es', 'es.id', '=', 'seating_allocations.exam_schedule_id')
        ->where('es.exam_id', $selectedExamMasterId)
        ->pluck('seating_allocations.room_id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();

      $availableRoomsForExam = Room::query()
        ->when($allocatedRoomIdsForExam->isNotEmpty(), function ($roomQuery) use ($allocatedRoomIdsForExam) {
          $roomQuery->whereNotIn('id', $allocatedRoomIdsForExam->all());
        })
        ->orderBy('building')
        ->orderBy('room_number')
        ->get();
    }

    return view('coe.seating-allocation.index', compact(
      'allocations',
      'exams',
      'rooms',
      'selectedExamMasterId',
      'selectedExamLabel',
      'operationalSessions',
      'generatedSeatSummary',
      'selectedOperationalSessionId',
      'availableRoomsForExam'
    ));
  }

  public function examSessions(Request $request)
  {
    $sessionDate = trim((string) $request->input('session_date', ''));
    $sessionStartTime = trim((string) $request->input('session_start_time', ''));
    $examId = (int) $request->input('exam_id', 0);

    $selectedExam = null;
    if ($examId > 0) {
      $selectedExam = Exam::query()->select('id', 'name')->find($examId);
      if ($selectedExam === null) {
        $examId = 0;
      }
    }

    $calendarSessions = $this->getCalendarBasedExamSessions(
      $sessionDate !== '' ? $sessionDate : null,
      $sessionStartTime !== '' ? $sessionStartTime : null,
      $examId > 0 ? $examId : null
    );

    return view('coe.seating-allocation.exam-sessions', compact(
      'calendarSessions',
      'sessionDate',
      'sessionStartTime',
      'examId',
      'selectedExam'
    ));
  }

  private function getCalendarBasedExamSessions(?string $sessionDate = null, ?string $sessionStartTime = null, ?int $examId = null)
  {
    $query = DB::table('exam_calendar_entries as ece')
      ->leftJoin('program_course_masters as pcm', 'pcm.id', '=', 'ece.course_id')
      ->select([
        'ece.exam_date',
        'ece.start_time',
        'ece.end_time',
        'ece.course_id',
        'ece.course_code',
        'ece.title',
        DB::raw('COALESCE(pcm.course_code, ece.course_code) as effective_course_code'),
        DB::raw('COALESCE(pcm.course_title, ece.title) as effective_course_title'),
      ])
      ->whereNotNull('ece.exam_date')
      ->whereNotNull('ece.start_time');

    if (!empty($examId)) {
      $query->where('ece.exam_id', (int) $examId);
    }

    if (!empty($sessionDate)) {
      $query->whereDate('ece.exam_date', $sessionDate);
    }

    if (!empty($sessionStartTime)) {
      $query->whereTime('ece.start_time', Carbon::parse($sessionStartTime)->format('H:i:s'));
    }

    $rows = $query
      ->orderBy('ece.exam_date')
      ->orderBy('ece.start_time')
      ->get();

    $courseIds = $rows
      ->pluck('course_id')
      ->filter()
      ->map(function ($id) {
        return (int) $id;
      })
      ->unique()
      ->values();

    $studentCountByCourse = collect();
    if ($courseIds->isNotEmpty()) {


      $studentCountByCourse = DB::table('student_course_rosters')
        ->select('course_id', DB::raw('COUNT(DISTINCT student_id) as student_count'))
        ->whereIn('course_id', $courseIds->all())
        ->whereNull('deleted_at')
        ->groupBy('course_id')
        ->pluck('student_count', 'course_id');
    }

    return $rows
      ->groupBy(function ($row) {
        return $this->buildSessionSlotKey($row->exam_date, $row->start_time);
      })
      ->map(function ($slotRows) use ($studentCountByCourse) {
        $firstRow = $slotRows->first();
        $courses = $slotRows
          ->map(function ($row) use ($studentCountByCourse) {
            $courseCode = trim((string) ($row->effective_course_code ?? ''));
            $courseTitle = trim((string) ($row->effective_course_title ?? ''));

            if ($courseCode === '' && $courseTitle === '') {
              $courseCode = 'N/A';
              $courseTitle = 'Untitled Course';
            }

            return [
              'course_id' => $row->course_id ? (int) $row->course_id : null,
              'course_code' => $courseCode,
              'course_title' => $courseTitle,
              'student_count' => $row->course_id ? (int) ($studentCountByCourse->get((int) $row->course_id, 0)) : 0,
            ];
          })
          ->unique(function ($course) {
            return ($course['course_id'] ?? 0) . '|' . $course['course_code'] . '|' . $course['course_title'];
          })
          ->values();

        return (object) [
          'exam_date' => $firstRow->exam_date,
          'start_time' => $firstRow->start_time,
          'end_time' => $slotRows->max('end_time'),
          'courses' => $courses,
          'course_count' => $courses->count(),
        ];
      })
      ->values();
  }

  public function create()
  {
    $exams = ExamSchedule::with('exam:id,name,exam_type')
      ->orderByDesc('exam_date')
      ->orderBy('start_time')
      ->get();
    $rooms = Room::orderBy('building')->orderBy('room_number')->get();
    $students = ExamStudent::with('student:id,first_name,last_name,roll_no,register_no')
      ->orderByDesc('id')
      ->get();

    return view('coe.seating-allocation.create', compact('exams', 'rooms', 'students'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_schedule_id' => 'required|exists:exam_schedules,id',
      'room_id' => 'required|exists:rooms,id',
      'exam_student_id' => 'required|exists:exam_students,id',
      'seat_no' => 'required|string',
    ]);

    SeatingAllocation::create($request->only(['exam_schedule_id', 'room_id', 'exam_student_id', 'seat_no']));

    return redirect()->route('admin.seating-allocation.index')
      ->with('success', 'Seating allocation created successfully');
  }

  public function show($id)
  {
    $allocation = SeatingAllocation::with(['examSchedule.exam', 'room', 'examStudent.student'])->findOrFail($id);
    return view('coe.seating-allocation.show', compact('allocation'));
  }

  public function edit($id)
  {
    $allocation = SeatingAllocation::findOrFail($id);
    $exams = ExamSchedule::with('exam:id,name,exam_type')
      ->orderByDesc('exam_date')
      ->orderBy('start_time')
      ->get();
    $rooms = Room::orderBy('building')->orderBy('room_number')->get();
    $students = ExamStudent::with('student:id,first_name,last_name,roll_no,register_no')
      ->orderByDesc('id')
      ->get();

    return view('coe.seating-allocation.edit', compact('allocation', 'exams', 'rooms', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_schedule_id' => 'required|exists:exam_schedules,id',
      'room_id' => 'required|exists:rooms,id',
      'exam_student_id' => 'required|exists:exam_students,id',
      'seat_no' => 'required|string',
    ]);

    $allocation = SeatingAllocation::findOrFail($id);
    $allocation->update($request->only(['exam_schedule_id', 'room_id', 'exam_student_id', 'seat_no']));

    return redirect()->route('admin.seating-allocation.index')
      ->with('success', 'Seating allocation updated successfully');
  }

  public function destroy($id)
  {
    $allocation = SeatingAllocation::findOrFail($id);
    $allocation->delete();

    return redirect()->route('admin.seating-allocation.index')
      ->with('success', 'Seating allocation deleted successfully');
  }

  public function autoAllocate(Request $request)
  {
    $this->syncOperationalSessionsFromSchedules();

    $request->validate([
      'operational_session_id' => 'nullable|exists:exam_operational_sessions,id',
      'exam_master_id' => 'nullable|integer|exists:exams,id',
      'exam_id' => 'nullable|integer|exists:exam_schedules,id',
      'enforce_same_course_separation' => 'nullable|boolean',
      'strict_room_lock' => 'nullable|boolean',
      'allowed_course_groups' => 'nullable|string|max:4000',
    ]);

    try {
      DB::beginTransaction();

      $enforceSameCourseSeparation = (bool) $request->boolean('enforce_same_course_separation', true);
      $strictRoomLock = (bool) $request->boolean('strict_room_lock', true);
      $examMasterId = 0;
      $selectedSchedule = null;

      // Keep exam resolution simple: if a schedule is selected in page context,
      // use its parent exam id as the source of truth.
      $examScheduleId = (int) $request->input('exam_id', 0);
      if ($examScheduleId > 0) {
        $selectedSchedule = ExamSchedule::query()
          ->select('id', 'exam_id', 'exam_date', 'start_time')
          ->find($examScheduleId);
        $examMasterId = (int) ($selectedSchedule->exam_id ?? 0);
      }

      if ($examMasterId <= 0) {
        $examMasterId = (int) $request->input('exam_master_id', 0);
      }

      $sessionIds = [];
      $explicitSessionId = (int) $request->input('operational_session_id', 0);
      if ($explicitSessionId > 0) {
        $sessionIds[] = $explicitSessionId;
      } else {
        if ($examMasterId <= 0) {
          DB::rollBack();
          return redirect()->back()->with('error', 'Please filter/select an exam first. Session selection is optional, but exam context is required.');
        }

        $scheduleSlots = ExamSchedule::query()
          ->where('exam_id', $examMasterId)
          ->whereNotNull('exam_date')
          ->whereNotNull('start_time')
          ->get(['exam_date', 'start_time'])
          ->toBase();

        if (
          $selectedSchedule &&
          !empty($selectedSchedule->exam_date) &&
          !empty($selectedSchedule->start_time)
        ) {
          $scheduleSlots = $scheduleSlots->push((object) [
            'exam_date' => $selectedSchedule->exam_date,
            'start_time' => $selectedSchedule->start_time,
          ]);
        }

        $calendarSlots = DB::table('exam_calendar_entries')
          ->select('exam_date', 'start_time')
          ->where('exam_id', $examMasterId)
          ->whereNotNull('exam_date')
          ->whereNotNull('start_time')
          ->distinct()
          ->get();

        if ($calendarSlots->isNotEmpty()) {
          $scheduleSlots = $scheduleSlots->merge($calendarSlots);
        }

        $scheduleSlots = $scheduleSlots
          ->filter(function ($slot) {
            return !empty($slot->exam_date) && !empty($slot->start_time);
          })
          ->map(function ($slot) {
            return (object) [
              'exam_date' => Carbon::parse($slot->exam_date)->format('Y-m-d'),
              'start_time' => Carbon::parse($slot->start_time)->format('H:i:s'),
            ];
          })
          ->unique(function ($slot) {
            return $slot->exam_date . '|' . $slot->start_time;
          })
          ->values();

        // If still empty, last fallback is the selected schedule slot when date/time are absent in loaded model.
        if ($scheduleSlots->isEmpty() && $examScheduleId > 0) {
          $selectedScheduleWithSlot = ExamSchedule::query()
            ->select('exam_date', 'start_time')
            ->find($examScheduleId);

          if (
            $selectedScheduleWithSlot &&
            !empty($selectedScheduleWithSlot->exam_date) &&
            !empty($selectedScheduleWithSlot->start_time)
          ) {
            $scheduleSlots = collect([(object) [
              'exam_date' => Carbon::parse($selectedScheduleWithSlot->exam_date)->format('Y-m-d'),
              'start_time' => Carbon::parse($selectedScheduleWithSlot->start_time)->format('H:i:s'),
            ]]);
          }
        }

        if ($scheduleSlots->isEmpty()) {
          DB::rollBack();
          return redirect()->back()->with('error', 'No exam schedules found for the selected exam ID.');
        }

        foreach ($scheduleSlots as $scheduleSlot) {
          $slotDate = Carbon::parse($scheduleSlot->exam_date)->format('Y-m-d');
          $slotStart = Carbon::parse($scheduleSlot->start_time)->format('H:i:s');

          $session = ExamOperationalSession::query()->firstOrCreate(
            [
              'exam_date' => $slotDate,
              'start_time' => $slotStart,
            ],
            [
              'end_time' => null,
              'session_label' => Carbon::parse($slotDate)->format('d M Y') . ' ' . Carbon::parse($slotStart)->format('H:i'),
              'status' => 'active',
            ]
          );

          $sessionIds[] = (int) $session->id;
        }
      }

      $sessionIds = collect($sessionIds)
        ->filter(fn($id) => (int) $id > 0)
        ->unique()
        ->values();
      if ($sessionIds->isEmpty()) {
        DB::rollBack();
        return redirect()->back()->with('error', 'No operational session slot found for selected exam context.');
      }

      $sessionModels = ExamOperationalSession::query()
        ->whereIn('id', $sessionIds->all())
        ->get()
        ->keyBy('id');

      $sessionSlots = $sessionIds
        ->map(function ($sessionId) use ($sessionModels) {
          $session = $sessionModels->get((int) $sessionId);
          if (!$session) {
            return null;
          }

          return [
            'exam_date' => Carbon::parse($session->exam_date)->format('Y-m-d'),
            'start_time' => Carbon::parse($session->start_time)->format('H:i:s'),
          ];
        })
        ->filter()
        ->values();

      if ($sessionSlots->isEmpty()) {
        DB::rollBack();
        return redirect()->back()->with('error', 'No operational session slot found for selected exam context.');
      }

      $targetExamIds = collect();
      if ($examMasterId > 0) {
        $targetExamIds->push($examMasterId);
      }

      if ($targetExamIds->isEmpty() && $selectedSchedule) {
        $selectedScheduleExamId = (int) ($selectedSchedule->exam_id ?? 0);
        if ($selectedScheduleExamId > 0) {
          $targetExamIds->push($selectedScheduleExamId);
        }
      }

      if ($targetExamIds->isEmpty()) {
        $targetExamIds = ExamSchedule::query()
          ->where(function ($query) use ($sessionSlots) {
            foreach ($sessionSlots as $slot) {
              $query->orWhere(function ($subQuery) use ($slot) {
                $subQuery
                  ->whereDate('exam_date', $slot['exam_date'])
                  ->whereTime('start_time', $slot['start_time']);
              });
            }
          })
          ->pluck('exam_id')
          ->map(fn($id) => (int) $id)
          ->filter()
          ->unique()
          ->values();
      }

      // Fallbacks for exam context when schedules are sparse/missing.
      if ($targetExamIds->isEmpty() && $examMasterId > 0) {
        $targetExamIds = collect([$examMasterId]);
      }

      if ($targetExamIds->isEmpty()) {
        $targetExamIds = DB::table('exam_calendar_entries as ece')
          ->where(function ($query) use ($sessionSlots) {
            foreach ($sessionSlots as $slot) {
              $query->orWhere(function ($subQuery) use ($slot) {
                $subQuery
                  ->whereDate('ece.exam_date', $slot['exam_date'])
                  ->whereTime('ece.start_time', $slot['start_time']);
              });
            }
          })
          ->pluck('ece.exam_id')
          ->map(fn($id) => (int) $id)
          ->filter()
          ->unique()
          ->values();
      }

      if ($targetExamIds->isEmpty()) {
        DB::rollBack();
        return redirect()->back()->with('error', 'No exam context found to determine available rooms.');
      }

      $allocatedRoomIdsForExam = SeatingAllocation::query()
        ->join('exam_schedules as es', 'es.id', '=', 'seating_allocations.exam_schedule_id')
        ->whereIn('es.exam_id', $targetExamIds->all())
        ->pluck('seating_allocations.room_id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();

      $rooms = Room::query()
        ->when($allocatedRoomIdsForExam->isNotEmpty(), function ($roomQuery) use ($allocatedRoomIdsForExam) {
          $roomQuery->whereNotIn('id', $allocatedRoomIdsForExam->all());
        })
        ->orderBy('building')
        ->orderBy('room_number')
        ->get();

      if ($rooms->isEmpty()) {
        DB::rollBack();
        return redirect()->back()->with('error', 'No available rooms found. All rooms are already allocated for this examination.');
      }

      $totals = [
        'generated' => 0,
        'allocated' => 0,
        'unassigned' => 0,
        'separation_exceptions' => 0,
        'fixed_room_overrides' => 0,
        'sessions_processed' => 0,
      ];

      foreach ($sessionIds as $sessionId) {
        $result = $this->generateAndAllocateForSession(
          (int) $sessionId,
          $rooms,
          $enforceSameCourseSeparation,
          $strictRoomLock,
          $targetExamIds->all(),
          (string) $request->input('allowed_course_groups', '')
        );

        if (($result['status'] ?? 'ok') === 'skip') {
          continue;
        }

        if (($result['status'] ?? 'ok') !== 'ok') {
          DB::rollBack();
          return redirect()->back()->with('error', (string) ($result['message'] ?? 'Seat generation failed.'));
        }

        $totals['generated'] += (int) ($result['generated'] ?? 0);
        $totals['allocated'] += (int) ($result['allocated'] ?? 0);
        $totals['unassigned'] += (int) ($result['unassigned'] ?? 0);
        $totals['separation_exceptions'] += (int) ($result['separation_exceptions'] ?? 0);
        $totals['fixed_room_overrides'] += (int) ($result['fixed_room_overrides'] ?? 0);
        $totals['sessions_processed']++;
      }

      if ($totals['sessions_processed'] <= 0) {
        DB::rollBack();
        return redirect()->back()->with('error', 'No exam schedules found for selected exam context and session slots.');
      }

      DB::commit();

      $redirectTarget = trim((string) $request->input('redirect_to', ''));
      $message = 'Seat generation completed for ' . $totals['sessions_processed'] . ' session slot(s). Generated ' . $totals['generated'] . ' physical seats and allocated ' . $totals['allocated'] . ' students.';
      if ($totals['unassigned'] > 0) {
        $message .= ' Unassigned students: ' . $totals['unassigned'] . '.';
      }
      if ($totals['separation_exceptions'] > 0) {
        $message .= ' Separation exceptions used: ' . $totals['separation_exceptions'] . '.';
      }
      if ($totals['fixed_room_overrides'] > 0) {
        $message .= ' Fixed-room overrides used: ' . $totals['fixed_room_overrides'] . '.';
      }

      if ($redirectTarget !== '') {
        return redirect($redirectTarget)->with('success', $message);
      }

      return redirect()->route('admin.seating-allocation.index')->with('success', $message);
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Seat generation failed: ' . $e->getMessage());
    }
  }

  public function finalizeDrafts(Request $request)
  {
    if (!Schema::hasColumn('seating_allocations', 'status')) {
      return redirect()->back()->with('error', 'Draft/finalize workflow is not available because seating status column is missing. Please run migrations.');
    }

    $request->validate([
      'operational_session_id' => 'nullable|exists:exam_operational_sessions,id',
      'exam_master_id' => 'nullable|integer|exists:exams,id',
      'exam_id' => 'nullable|integer|exists:exam_schedules,id',
    ]);

    $scheduleIds = collect();

    $scheduleIdFromRequest = (int) $request->input('exam_id', 0);
    if ($scheduleIdFromRequest > 0) {
      $scheduleIds->push($scheduleIdFromRequest);
    }

    $examMasterId = (int) $request->input('exam_master_id', 0);
    if ($examMasterId > 0) {
      $examScheduleIds = ExamSchedule::query()
        ->where('exam_id', $examMasterId)
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->all();

      $scheduleIds = $scheduleIds->merge($examScheduleIds);
    }

    $sessionId = (int) $request->input('operational_session_id', 0);
    if ($sessionId > 0) {
      $session = ExamOperationalSession::query()->find($sessionId);
      if ($session) {
        $slotSchedules = ExamSchedule::query()
          ->whereDate('exam_date', Carbon::parse($session->exam_date)->format('Y-m-d'))
          ->whereTime('start_time', Carbon::parse($session->start_time)->format('H:i:s'))
          ->pluck('id')
          ->map(fn($id) => (int) $id)
          ->all();

        $scheduleIds = $scheduleIds->merge($slotSchedules);
      }
    }

    $scheduleIds = $scheduleIds->filter()->unique()->values();
    if ($scheduleIds->isEmpty()) {
      return redirect()->back()->with('error', 'No exam context selected to finalize draft seating allocations.');
    }

    $finalizedCount = SeatingAllocation::query()
      ->whereIn('exam_schedule_id', $scheduleIds->all())
      ->where('status', 'draft')
      ->update(['status' => 'finalized']);

    if ($finalizedCount <= 0) {
      return redirect()->back()->with('error', 'No draft seating allocations found for selected context.');
    }

    return redirect()->back()->with('success', 'Draft seating finalized successfully. Finalized records: ' . $finalizedCount . '.');
  }

  private function generateAndAllocateForSession(int $sessionId, $rooms, bool $enforceSameCourseSeparation, bool $strictRoomLock, array $targetExamIds, string $allowedCourseGroupsRaw): array
  {
    $session = ExamOperationalSession::query()->find($sessionId);
    if (!$session) {
      return ['status' => 'error', 'message' => 'Operational session not found.'];
    }

    $sessionDate = Carbon::parse($session->exam_date)->format('Y-m-d');
    $sessionStart = Carbon::parse($session->start_time)->format('H:i:s');

    $sessionSchedulesQuery = ExamSchedule::query()
      ->with(['examSubjectMaster:id,erp_subject_id,subject_code,name'])
      ->whereDate('exam_date', $sessionDate)
      ->whereTime('start_time', $sessionStart);

    if (!empty($targetExamIds)) {
      $sessionSchedulesQuery->whereIn('exam_id', $targetExamIds);
    }

    $sessionSchedules = $sessionSchedulesQuery->get();

    // Fallback for mixed/inconsistent exam_id linkage: keep the slot, relax exam filter.
    if ($sessionSchedules->isEmpty() && !empty($targetExamIds)) {
      $sessionSchedules = ExamSchedule::query()
        ->with(['examSubjectMaster:id,erp_subject_id,subject_code,name'])
        ->whereDate('exam_date', $sessionDate)
        ->whereTime('start_time', $sessionStart)
        ->get();
    }


    if ($sessionSchedules->isEmpty()) {
      return ['status' => 'skip', 'message' => 'No exam schedules found for this session slot.'];
    }

    $allowedCoursePairs = $this->parseAllowedCoursePairs($allowedCourseGroupsRaw, $sessionSchedules);

    ExamRoomGeneratedSeat::query()
      ->where('exam_operational_session_id', $sessionId)
      ->whereIn('room_id', $rooms->pluck('id')->all())
      ->delete();

    $generatedCount = 0;
    foreach ($rooms as $room) {
      [$rows, $columns] = $this->resolveRoomLayout($room);
      if ($rows <= 0 || $columns <= 0) {
        continue;
      }

      $seatNumber = 1;
      for ($columnNo = 1; $columnNo <= $columns; $columnNo++) {
        for ($rowNo = 1; $rowNo <= $rows; $rowNo++) {
          $seatCode = $this->buildSeatCode($room, $rowNo, $columnNo);

          ExamRoomGeneratedSeat::create([
            'exam_operational_session_id' => $sessionId,
            'room_id' => $room->id,
            'row_no' => $rowNo,
            'column_no' => $columnNo,
            'seat_number' => $seatNumber,
            'seat_code' => $seatCode,
            'is_allocated' => false,
          ]);

          $generatedCount++;
          $seatNumber++;
        }
      }
    }

    $seatPool = ExamRoomGeneratedSeat::query()
      ->where('exam_operational_session_id', $sessionId)
      ->whereIn('room_id', $rooms->pluck('id')->all())
      ->orderBy('room_id')
      ->orderBy('column_no')
      ->orderBy('row_no')
      ->get();

    $candidates = $this->buildAllocationCandidatesForSession($sessionSchedules);
    if ($candidates->isEmpty()) {
      return [
        'status' => 'ok',
        'generated' => $generatedCount,
        'allocated' => 0,
        'unassigned' => 0,
        'separation_exceptions' => 0,
        'fixed_room_overrides' => 0,
      ];
    }

    $preferredRoomByStudent = $this->buildPreferredRoomMapForCandidates(
      $candidates,
      $rooms->pluck('id')->map(fn($id) => (int) $id)->all(),
      $sessionSchedules->pluck('exam_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all()
    );

    $scheduleIds = $sessionSchedules->pluck('id')->map(fn($id) => (int) $id)->values();
    SeatingAllocation::query()->whereIn('exam_schedule_id', $scheduleIds->all())->delete();

    $queuesByCourse = $candidates
      ->groupBy('course_id')
      ->map(function ($rows) {
        return $rows->values();
      });

    $courseOrder = $queuesByCourse
      ->keys()
      ->map(fn($key) => (int) $key)
      ->values();

    $assignedCount = 0;
    $separationViolations = 0;
    $fixedRoomOverrides = 0;
    $assignedSeatIds = [];
    $assignedBySeat = [];

    foreach ($seatPool as $seat) {
      $courseOrder = $courseOrder
        ->sortByDesc(function ($courseId) use ($queuesByCourse) {
          return $queuesByCourse->get((int) $courseId, collect())->count();
        })
        ->values();

      $selectedCourseId = null;
      $selectedCandidate = null;
      $selectedQueueIndex = null;

      foreach ($courseOrder as $courseId) {
        $queue = $queuesByCourse->get((int) $courseId, collect());
        if ($queue->isEmpty()) {
          continue;
        }

        $candidateSelection = $this->pickCandidateForRoomFromQueue($queue, (int) $seat->room_id, $preferredRoomByStudent);
        if ($candidateSelection === null) {
          continue;
        }

        $candidate = $candidateSelection['candidate'];
        if ($this->canPlaceCandidateInSeat($seat, $candidate, $assignedBySeat, $enforceSameCourseSeparation, $allowedCoursePairs)) {
          $selectedCourseId = (int) $courseId;
          $selectedCandidate = $candidate;
          $selectedQueueIndex = (int) $candidateSelection['index'];
          break;
        }
      }

      if ($selectedCandidate === null) {
        // Strict lock keeps fixed-room students in their mapped room; relaxed mode may override.
        foreach ($courseOrder as $courseId) {
          $queue = $queuesByCourse->get((int) $courseId, collect());
          if ($queue->isEmpty()) {
            continue;
          }

          $candidateSelection = $strictRoomLock
            ? $this->pickUnpinnedCandidateFromQueue($queue, $preferredRoomByStudent)
            : $this->pickAnyCandidateFromQueue($queue);
          if ($candidateSelection === null) {
            continue;
          }

          $candidate = $candidateSelection['candidate'];
          if ($this->canPlaceCandidateInSeat($seat, $candidate, $assignedBySeat, $enforceSameCourseSeparation, $allowedCoursePairs)) {
            $selectedCourseId = (int) $courseId;
            $selectedCandidate = $candidate;
            $selectedQueueIndex = (int) $candidateSelection['index'];
            if ($enforceSameCourseSeparation) {
              $separationViolations++;
            }
            break;
          }
        }
      }

      if ($selectedCandidate === null) {
        continue;
      }

      $preferredRoomId = (int) ($preferredRoomByStudent[(int) $selectedCandidate['exam_student_id']] ?? 0);
      if ($preferredRoomId > 0 && $preferredRoomId !== (int) $seat->room_id) {
        $fixedRoomOverrides++;
      }

      $allocationPayload = [
        'exam_schedule_id' => (int) $selectedCandidate['exam_schedule_id'],
        'room_id' => (int) $seat->room_id,
        'exam_student_id' => (int) $selectedCandidate['exam_student_id'],
        'seat_no' => (string) $seat->seat_code,
      ];

      if (Schema::hasColumn('seating_allocations', 'status')) {
        $allocationPayload['status'] = 'draft';
      }

      SeatingAllocation::create($allocationPayload);

      $assignedBySeat[$seat->room_id . '|' . $seat->row_no . '|' . $seat->column_no] = [
        'course_id' => (int) $selectedCandidate['course_id'],
      ];

      $assignedSeatIds[] = (int) $seat->id;
      $assignedCount++;

      if ($selectedQueueIndex === null) {
        $selectedQueueIndex = 0;
      }

      $remainingQueue = $queuesByCourse->get($selectedCourseId)->forget($selectedQueueIndex)->values();
      $queuesByCourse->put($selectedCourseId, $remainingQueue);
    }

    if (!empty($assignedSeatIds)) {
      ExamRoomGeneratedSeat::query()
        ->whereIn('id', $assignedSeatIds)
        ->update(['is_allocated' => true]);
    }

    return [
      'status' => 'ok',
      'generated' => $generatedCount,
      'allocated' => $assignedCount,
      'unassigned' => max(0, $candidates->count() - $assignedCount),
      'separation_exceptions' => $separationViolations,
      'fixed_room_overrides' => $fixedRoomOverrides,
    ];
  }

  public function export(Request $request)
  {
    // Export seating allocation data
    $query = SeatingAllocation::with(['examSchedule.exam', 'room', 'examStudent.student']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_schedule_id', $request->exam_id);
    }

    if ($request->filled('exam_master_id')) {
      $examMasterId = (int) $request->input('exam_master_id');
      $query->whereHas('examSchedule', function ($scheduleQuery) use ($examMasterId) {
        $scheduleQuery->where('exam_id', $examMasterId);
      });
    }

    $allocations = $query->orderBy('seat_no')->get();

    // Return export logic (CSV, Excel, PDF)
    return response()->json($allocations);
  }

  private function syncOperationalSessionsFromSchedules(): void
  {
    $slots = ExamSchedule::query()
      ->select('exam_date', 'start_time', DB::raw('MAX(end_time) as end_time'))
      ->whereNotNull('exam_date')
      ->whereNotNull('start_time')
      ->groupBy('exam_date', 'start_time')
      ->get();

    foreach ($slots as $slot) {
      $date = Carbon::parse($slot->exam_date)->format('Y-m-d');
      $start = Carbon::parse($slot->start_time)->format('H:i:s');
      $end = $slot->end_time ? Carbon::parse($slot->end_time)->format('H:i:s') : null;

      ExamOperationalSession::updateOrCreate(
        [
          'exam_date' => $date,
          'start_time' => $start,
        ],
        [
          'end_time' => $end,
          'session_label' => Carbon::parse($date)->format('d M Y') . ' ' . Carbon::parse($start)->format('H:i'),
          'status' => 'active',
        ]
      );
    }
  }

  private function getExamSessionUnits(?string $sessionDate = null, ?string $sessionStartTime = null, ?int $examId = null)
  {
    $sessionQuery = ExamOperationalSession::query()
      ->withCount('generatedSeats')
      ->orderByDesc('exam_date')
      ->orderBy('start_time');

    if (!empty($sessionDate)) {
      $sessionQuery->whereDate('exam_date', $sessionDate);
    }

    if (!empty($sessionStartTime)) {
      $normalizedStartTime = Carbon::parse($sessionStartTime)->format('H:i:s');
      $sessionQuery->whereTime('start_time', $normalizedStartTime);
    }

    $sessions = $sessionQuery->get();
    if ($sessions->isEmpty()) {
      return $sessions;
    }

    $schedules = ExamSchedule::query()
      ->with([
        'exam:id,name,exam_type',
        'examSubjectMaster:id,subject_code,name',
      ])
      ->where(function ($query) use ($sessions) {
        foreach ($sessions as $session) {
          $query->orWhere(function ($subQuery) use ($session) {
            $subQuery
              ->whereDate('exam_date', Carbon::parse($session->exam_date)->format('Y-m-d'))
              ->whereTime('start_time', Carbon::parse($session->start_time)->format('H:i:s'));
          });
        }
      })
      ->get();

    if (!empty($examId)) {
      $schedules = $schedules->where('exam_id', (int) $examId)->values();
    }

    $schedulesBySlot = $schedules->groupBy(function ($schedule) {
      return $this->buildSessionSlotKey($schedule->exam_date, $schedule->start_time);
    });

    $sessionUnits = $sessions->map(function ($session) use ($schedulesBySlot) {
      $slotKey = $this->buildSessionSlotKey($session->exam_date, $session->start_time);
      $sessionSchedules = $schedulesBySlot->get($slotKey, collect());

      $distinctCourseIds = $sessionSchedules
        ->pluck('exam_subject_id')
        ->filter()
        ->unique()
        ->values();

      $courses = $sessionSchedules->map(function ($schedule) {
        return [
          'exam_subject_id' => (int) $schedule->exam_subject_id,
          'subject_code' => (string) ($schedule->examSubjectMaster->subject_code ?? ('SUB-' . $schedule->exam_subject_id)),
          'subject_name' => (string) ($schedule->examSubjectMaster->name ?? 'Unknown Subject'),
          'exam_name' => (string) ($schedule->exam->name ?? 'Exam'),
        ];
      })->unique(function ($course) {
        return (int) ($course['exam_subject_id'] ?? 0);
      })->values();

      $session->setAttribute('schedule_count', $sessionSchedules->count());
      $session->setAttribute('course_count', $distinctCourseIds->count());
      $session->setAttribute('exam_count', $sessionSchedules->pluck('exam_id')->filter()->unique()->count());
      $session->setAttribute('room_count', $sessionSchedules->pluck('room_id')->filter()->unique()->count());
      $session->setAttribute('courses', $courses);

      return $session;
    });

    if (!empty($examId)) {
      return $sessionUnits->filter(function ($session) {
        return (int) ($session->schedule_count ?? 0) > 0;
      })->values();
    }

    return $sessionUnits;
  }

  private function buildSessionSlotKey($examDate, $startTime): string
  {
    $dateToken = Carbon::parse($examDate)->format('Y-m-d');
    $timeToken = Carbon::parse($startTime)->format('H:i:s');

    return $dateToken . '|' . $timeToken;
  }

  private function resolveRoomLayout(Room $room): array
  {
    $rows = (int) ($room->rows ?? 0);
    $columns = (int) ($room->columns ?? 0);

    if ($rows > 0 && $columns > 0) {
      return [$rows, $columns];
    }

    $capacity = (int) ($room->capacity ?? 0);
    if ($capacity > 0) {
      return [1, $capacity];
    }

    return [0, 0];
  }

  private function buildAllocationCandidatesForSession($sessionSchedules)
  {
    $scheduleBySubjectAndExam = [];
    $scheduleBySubjectOnly = [];

    foreach ($sessionSchedules as $schedule) {
      $courseId = (int) $schedule->exam_subject_id;
      $examId = (int) $schedule->exam_id;
      $erpSubjectId = (int) ($schedule->examSubjectMaster->erp_subject_id ?? 0);

      if ($courseId <= 0 || $examId <= 0 || $erpSubjectId <= 0) {
        continue;
      }

      $scheduleBySubjectAndExam[$examId . '|' . $erpSubjectId] = [
        'exam_schedule_id' => (int) $schedule->id,
        'course_id' => $courseId,
      ];

      if (!isset($scheduleBySubjectOnly[$erpSubjectId])) {
        $scheduleBySubjectOnly[$erpSubjectId] = [
          'exam_schedule_id' => (int) $schedule->id,
          'course_id' => $courseId,
        ];
      }
    }

    if (empty($scheduleBySubjectAndExam)) {
      return collect();
    }

    $examIds = collect($sessionSchedules)->pluck('exam_id')->filter()->map(fn($id) => (int) $id)->unique()->values();
    $erpSubjectIds = collect($sessionSchedules)
      ->map(fn($schedule) => (int) ($schedule->examSubjectMaster->erp_subject_id ?? 0))
      ->filter()
      ->unique()
      ->values();

    $hasRegistrationExamId = Schema::hasColumn('exam_registrations', 'exam_id');

    $registrationQuery = DB::table('exam_registration_subjects as ers')
      ->join('exam_registrations as er', 'er.id', '=', 'ers.exam_registration_id')
      ->join('exam_subjects as es', 'es.id', '=', 'ers.exam_subject_id')
      ->select([
        'er.erp_student_id',
        'es.erp_subject_id',
      ])
      ->whereNotNull('er.erp_student_id')
      ->whereIn('es.erp_subject_id', $erpSubjectIds->all())
      ->when(Schema::hasColumn('exam_registrations', 'status'), function ($query) {
        $query->whereNotIn('er.status', ['rejected', 'cancelled']);
      })
      ->distinct();

    if ($hasRegistrationExamId) {
      $registrationQuery
        ->addSelect('er.exam_id')
        ->whereIn('er.exam_id', $examIds->all());
    }

    $registrationSubjectRows = $registrationQuery->get();

    if ($registrationSubjectRows->isEmpty()) {
      return collect();
    }

    $erpStudentIds = $registrationSubjectRows
      ->pluck('erp_student_id')
      ->filter()
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    $examStudentMap = ExamStudent::query()
      ->whereIn('erp_student_id', $erpStudentIds->all())
      ->pluck('id', 'erp_student_id');

    $candidates = collect();
    foreach ($registrationSubjectRows as $row) {
      $mapping = null;
      if ($hasRegistrationExamId) {
        $key = ((int) $row->exam_id) . '|' . ((int) $row->erp_subject_id);
        $mapping = $scheduleBySubjectAndExam[$key] ?? null;
      } else {
        $mapping = $scheduleBySubjectOnly[(int) $row->erp_subject_id] ?? null;
      }

      if ($mapping === null) {
        continue;
      }

      $examStudentId = (int) ($examStudentMap->get((int) $row->erp_student_id) ?? 0);
      if ($examStudentId <= 0) {
        continue;
      }

      $candidates->push([
        'exam_schedule_id' => (int) $mapping['exam_schedule_id'],
        'course_id' => (int) $mapping['course_id'],
        'exam_student_id' => $examStudentId,
        'exam_id' => (int) ($row->exam_id ?? 0),
      ]);
    }

    return $candidates
      ->unique(function ($item) {
        return (int) $item['exam_student_id'];
      })
      ->sortBy('exam_student_id')
      ->values();
  }

  private function buildPreferredRoomMapForCandidates($candidates, array $selectedRoomIds, array $examIds = []): array
  {
    if ($candidates->isEmpty()) {
      return [];
    }

    $examStudentIds = $candidates
      ->pluck('exam_student_id')
      ->filter()
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    $examIdsFromInput = collect($examIds)
      ->filter()
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    $examIdsFromCandidates = $candidates
      ->pluck('exam_id')
      ->filter()
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    $finalExamIds = $examIdsFromInput->isNotEmpty() ? $examIdsFromInput : $examIdsFromCandidates;

    if ($examStudentIds->isEmpty() || $finalExamIds->isEmpty()) {
      return [];
    }

    $rows = DB::table('seating_allocations as sa')
      ->join('exam_schedules as es', 'es.id', '=', 'sa.exam_schedule_id')
      ->select([
        'sa.exam_student_id',
        'sa.room_id',
        DB::raw('COUNT(*) as usage_count'),
      ])
      ->whereIn('sa.exam_student_id', $examStudentIds->all())
      ->whereIn('es.exam_id', $finalExamIds->all())
      ->groupBy('sa.exam_student_id', 'sa.room_id')
      ->orderBy('sa.exam_student_id')
      ->orderByDesc('usage_count')
      ->get();

    $allowedRoomIds = collect($selectedRoomIds)->map(fn($id) => (int) $id)->all();
    $allowedRoomSet = array_fill_keys($allowedRoomIds, true);

    $preferredByStudent = [];
    foreach ($rows as $row) {
      $studentId = (int) $row->exam_student_id;
      $roomId = (int) $row->room_id;
      if ($studentId <= 0 || $roomId <= 0) {
        continue;
      }

      if (!isset($allowedRoomSet[$roomId])) {
        continue;
      }

      if (!isset($preferredByStudent[$studentId])) {
        $preferredByStudent[$studentId] = $roomId;
      }
    }

    return $preferredByStudent;
  }

  private function pickCandidateForRoomFromQueue($queue, int $roomId, array $preferredRoomByStudent): ?array
  {
    $matchIndex = $queue->search(function ($candidate) use ($roomId, $preferredRoomByStudent) {
      $studentId = (int) ($candidate['exam_student_id'] ?? 0);
      $preferredRoomId = (int) ($preferredRoomByStudent[$studentId] ?? 0);
      return $preferredRoomId > 0 && $preferredRoomId === $roomId;
    });

    if ($matchIndex !== false) {
      return [
        'index' => (int) $matchIndex,
        'candidate' => $queue->get($matchIndex),
      ];
    }

    $unpinnedIndex = $queue->search(function ($candidate) use ($preferredRoomByStudent) {
      $studentId = (int) ($candidate['exam_student_id'] ?? 0);
      $preferredRoomId = (int) ($preferredRoomByStudent[$studentId] ?? 0);
      return $preferredRoomId <= 0;
    });

    if ($unpinnedIndex !== false) {
      return [
        'index' => (int) $unpinnedIndex,
        'candidate' => $queue->get($unpinnedIndex),
      ];
    }

    return null;
  }

  private function pickUnpinnedCandidateFromQueue($queue, array $preferredRoomByStudent): ?array
  {
    $unpinnedIndex = $queue->search(function ($candidate) use ($preferredRoomByStudent) {
      $studentId = (int) ($candidate['exam_student_id'] ?? 0);
      $preferredRoomId = (int) ($preferredRoomByStudent[$studentId] ?? 0);
      return $preferredRoomId <= 0;
    });

    if ($unpinnedIndex === false) {
      return null;
    }

    return [
      'index' => (int) $unpinnedIndex,
      'candidate' => $queue->get($unpinnedIndex),
    ];
  }

  private function pickAnyCandidateFromQueue($queue): ?array
  {
    if ($queue->isEmpty()) {
      return null;
    }

    return [
      'index' => 0,
      'candidate' => $queue->first(),
    ];
  }

  private function parseAllowedCoursePairs(string $raw, $sessionSchedules): array
  {
    $subjectCodeToCourseId = [];
    foreach ($sessionSchedules as $schedule) {
      $subjectCode = strtoupper(trim((string) ($schedule->examSubjectMaster->subject_code ?? '')));
      if ($subjectCode !== '') {
        $subjectCodeToCourseId[$subjectCode] = (int) $schedule->exam_subject_id;
      }
    }

    $pairs = [];
    $chunks = preg_split('/[\r\n,;]+/', $raw) ?: [];

    foreach ($chunks as $chunk) {
      $token = trim((string) $chunk);
      if ($token === '') {
        continue;
      }

      $parts = preg_split('/\s*[-:|]\s*/', $token) ?: [];
      if (count($parts) !== 2) {
        continue;
      }

      $leftId = $this->resolveCourseTokenToId($parts[0], $subjectCodeToCourseId);
      $rightId = $this->resolveCourseTokenToId($parts[1], $subjectCodeToCourseId);
      if ($leftId <= 0 || $rightId <= 0) {
        continue;
      }

      $pairKey = min($leftId, $rightId) . '|' . max($leftId, $rightId);
      $pairs[$pairKey] = true;
    }

    return $pairs;
  }

  private function resolveCourseTokenToId(string $token, array $subjectCodeToCourseId): int
  {
    $normalized = strtoupper(trim($token));
    if ($normalized === '') {
      return 0;
    }

    if (ctype_digit($normalized)) {
      return (int) $normalized;
    }

    return (int) ($subjectCodeToCourseId[$normalized] ?? 0);
  }

  private function canPlaceCandidateInSeat($seat, array $candidate, array $assignedBySeat, bool $enforceSameCourseSeparation, array $allowedCoursePairs): bool
  {
    $candidateCourseId = (int) ($candidate['course_id'] ?? 0);
    if ($candidateCourseId <= 0) {
      return false;
    }

    $neighborOffsets = [
      [-1, 0],
      [1, 0],
      [0, -1],
      [0, 1],
    ];

    foreach ($neighborOffsets as [$rowOffset, $columnOffset]) {
      $neighborKey = $seat->room_id . '|' . ((int) $seat->row_no + $rowOffset) . '|' . ((int) $seat->column_no + $columnOffset);
      $neighbor = $assignedBySeat[$neighborKey] ?? null;
      if ($neighbor === null) {
        continue;
      }

      $neighborCourseId = (int) ($neighbor['course_id'] ?? 0);
      if ($neighborCourseId <= 0) {
        continue;
      }

      if ($enforceSameCourseSeparation && $neighborCourseId === $candidateCourseId) {
        $sameCoursePairKey = $candidateCourseId . '|' . $candidateCourseId;
        if (!isset($allowedCoursePairs[$sameCoursePairKey])) {
          return false;
        }
      }

      $pairKey = min($neighborCourseId, $candidateCourseId) . '|' . max($neighborCourseId, $candidateCourseId);
      if (isset($allowedCoursePairs[$pairKey])) {
        continue;
      }
    }

    return true;
  }

  private function buildSeatCode(Room $room, int $rowNo, int $columnNo): string
  {
    $roomToken = strtoupper(trim((string) ($room->room_number ?? $room->room_no ?? $room->name ?? ('ROOM' . $room->id))));
    $roomToken = preg_replace('/[^A-Z0-9]/', '', $roomToken) ?: ('ROOM' . $room->id);

    return $roomToken . '-R' . str_pad((string) $rowNo, 2, '0', STR_PAD_LEFT) . 'C' . str_pad((string) $columnNo, 2, '0', STR_PAD_LEFT);
  }
}
