<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\DepartmentMaster;
use App\Models\Faculty;
use App\Models\FacultyLeaveApplication;
use App\Models\HourMaster;
use App\Models\PrincipalAppointment;
use App\Models\ReceptionistWorkDiary;
use App\Models\SubjectHasRoutine;
use App\Models\Weekday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReceptionistController extends Controller
{
  private function hasAppointmentConflict(string $date, string $time, ?int $ignoreId = null): bool
  {
    $query = PrincipalAppointment::query()
      ->whereDate('appointment_date', $date)
      ->where('appointment_time', $time)
      ->where('status', '!=', 'cancelled');

    if ($ignoreId && $ignoreId > 0) {
      $query->where('id', '!=', $ignoreId);
    }

    return $query->exists();
  }

  public function dashboard()
  {
    $today = today();

    $totalFaculty = Faculty::where(function ($query) {
      $query->whereNull('IS_LEFT')->orWhere('IS_LEFT', 0);
    })->whereNull('deleted_at')->count();

    $todayAppointments = PrincipalAppointment::whereDate('appointment_date', $today)->count();
    $pendingAppointments = PrincipalAppointment::whereIn('status', ['scheduled', 'rescheduled'])->count();
    $todayDiaryEntries = ReceptionistWorkDiary::where('user_id', (int) auth()->id())
      ->whereDate('entry_date', $today)
      ->count();

    $recentAppointments = PrincipalAppointment::orderBy('appointment_date')
      ->orderBy('appointment_time')
      ->limit(8)
      ->get();

    return view('receptionist.dashboard', compact(
      'totalFaculty',
      'todayAppointments',
      'pendingAppointments',
      'todayDiaryEntries',
      'recentAppointments'
    ));
  }

  public function facultyIndex(Request $request)
  {
    $campuses = Campus::orderBy('name')->get();
    $departments = DepartmentMaster::orderBy('name')->get();

    $query = Faculty::query()
      ->whereNull('deleted_at')
      ->where(function ($q) {
        $q->whereNull('IS_LEFT')->orWhere('IS_LEFT', 0);
      });

    if ($request->filled('campus_id')) {
      $departmentIds = DepartmentMaster::where('campus_id', (int) $request->campus_id)
        ->pluck('id');
      $query->whereIn('DEPARTMENT', $departmentIds);
    }

    if ($request->filled('department_id')) {
      $query->where('DEPARTMENT', (int) $request->department_id);
    }

    if ($request->filled('q')) {
      $term = trim((string) $request->q);
      $query->where(function ($q) use ($term) {
        $q->where('FIRST_NAME', 'like', "%{$term}%")
          ->orWhere('MIDDLE_NAME', 'like', "%{$term}%")
          ->orWhere('LAST_NAME', 'like', "%{$term}%")
          ->orWhere('USER_CODE', 'like', "%{$term}%");
      });
    }

    $facultyList = $query->orderBy('FIRST_NAME')->orderBy('LAST_NAME')->paginate(18)->withQueryString();

    $today = today();
    $facultyIds = collect($facultyList->items())
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->values();

    $facultyOnLeaveTodayIds = collect();
    if ($facultyIds->isNotEmpty()) {
      $facultyOnLeaveTodayIds = FacultyLeaveApplication::query()
        ->whereIn('faculty_id', $facultyIds->all())
        ->where('status', 'approved')
        ->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today)
        ->pluck('faculty_id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();
    }

    foreach ($facultyList as $faculty) {
      $faculty->department_info = DepartmentMaster::with('campusmaster')->find($faculty->DEPARTMENT);
      $faculty->is_on_leave_today = $facultyOnLeaveTodayIds->contains((int) $faculty->id);
    }

    $selectedCampus = $request->campus_id;
    $selectedDepartment = $request->department_id;

    return view('receptionist.faculty.index', compact(
      'facultyList',
      'campuses',
      'departments',
      'selectedCampus',
      'selectedDepartment'
    ));
  }

  public function facultyTimetable($id)
  {
    $faculty = Faculty::findOrFail($id);

    $timetable = SubjectHasRoutine::with([
      'weekdaymaster',
      'hourmaster',
      'lecturehallmaster',
      'subjectCourse.courseMaster.semestermaster',
      'batch',
      'syllabus.semestermaster',
    ])->where('faculty_id', $id)->get();

    $weekdays = Weekday::all();
    $hours = HourMaster::all();

    $timetableGrid = [];
    foreach ($weekdays as $day) {
      $timetableGrid[$day->id] = [
        'day' => $day->title,
        'slots' => [],
      ];

      foreach ($hours as $hour) {
        $slot = $timetable->first(function ($routine) use ($day, $hour) {
          return (int) $routine->weekday_id === (int) $day->id
            && (int) $routine->hour_id === (int) $hour->id;
        });

        $timetableGrid[$day->id]['slots'][$hour->id] = [
          'hour' => $hour->title,
          'routine' => $slot,
        ];
      }
    }

    return view('receptionist.faculty.timetable', compact('faculty', 'weekdays', 'hours', 'timetableGrid'));
  }

  public function appointments(Request $request)
  {
    $query = PrincipalAppointment::query();

    if ($request->filled('date')) {
      $query->whereDate('appointment_date', $request->date);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $appointments = $query->orderBy('appointment_date')
      ->orderBy('appointment_time')
      ->paginate(20)
      ->withQueryString();

    return view('receptionist.appointments.index', compact('appointments'));
  }

  public function storeAppointment(Request $request)
  {
    $data = $request->validate([
      'visitor_name' => 'required|string|max:190',
      'visitor_phone' => 'nullable|string|max:20',
      'visitor_email' => 'nullable|email|max:190',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required|date_format:H:i',
      'purpose' => 'required|string|max:255',
      'notes' => 'nullable|string',
      'status' => 'nullable|in:scheduled,rescheduled,completed,cancelled',
    ]);

    if ($this->hasAppointmentConflict((string) $data['appointment_date'], (string) $data['appointment_time'])) {
      return redirect()->back()
        ->withErrors(['appointment_time' => 'This time slot is already booked for the Principal. Please choose another time.'])
        ->withInput();
    }

    $data['status'] = $data['status'] ?? 'scheduled';
    $data['created_by'] = (int) auth()->id();

    PrincipalAppointment::create($data);

    return redirect()->route('receptionist.appointments.index')->with('success', 'Appointment scheduled successfully.');
  }

  public function updateAppointment(Request $request, $id)
  {
    $appointment = PrincipalAppointment::findOrFail((int) $id);

    $data = $request->validate([
      'visitor_name' => 'required|string|max:190',
      'visitor_phone' => 'nullable|string|max:20',
      'visitor_email' => 'nullable|email|max:190',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required|date_format:H:i',
      'purpose' => 'required|string|max:255',
      'notes' => 'nullable|string',
      'status' => 'required|in:scheduled,rescheduled,completed,cancelled',
    ]);

    if ($this->hasAppointmentConflict((string) $data['appointment_date'], (string) $data['appointment_time'], (int) $appointment->id)) {
      return redirect()->back()
        ->withErrors(['appointment_time' => 'This time slot is already booked for the Principal. Please choose another time.'])
        ->withInput();
    }

    $appointment->update($data);

    return redirect()->route('receptionist.appointments.index')->with('success', 'Appointment updated successfully.');
  }

  public function destroyAppointment($id)
  {
    $appointment = PrincipalAppointment::findOrFail((int) $id);
    $appointment->delete();

    return redirect()->route('receptionist.appointments.index')->with('success', 'Appointment deleted successfully.');
  }

  public function workDiary(Request $request)
  {
    $selectedMonth = $request->month ?? now()->format('Y-m');
    $selectedMonthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth();
    $selectedMonthEnd = (clone $selectedMonthStart)->endOfMonth();

    $entries = ReceptionistWorkDiary::where('user_id', (int) auth()->id())
      ->whereBetween('entry_date', [$selectedMonthStart, $selectedMonthEnd])
      ->orderByDesc('entry_date')
      ->orderByDesc('id')
      ->get();

    return view('receptionist.work-diary.index', compact('entries', 'selectedMonth'));
  }

  public function storeWorkDiary(Request $request)
  {
    $data = $request->validate([
      'entry_date' => 'required|date',
      'work_summary' => 'required|string',
      'notes' => 'nullable|string',
      'status' => 'nullable|in:completed,pending',
    ]);

    $data['user_id'] = (int) auth()->id();
    $data['status'] = $data['status'] ?? 'completed';

    ReceptionistWorkDiary::create($data);

    return redirect()->route('receptionist.work-diary.index')->with('success', 'Work diary entry added successfully.');
  }

  public function updateWorkDiary(Request $request, $id)
  {
    $entry = ReceptionistWorkDiary::where('user_id', (int) auth()->id())
      ->where('id', (int) $id)
      ->firstOrFail();

    $data = $request->validate([
      'entry_date' => 'required|date',
      'work_summary' => 'required|string',
      'notes' => 'nullable|string',
      'status' => 'required|in:completed,pending',
    ]);

    $entry->update($data);

    return redirect()->route('receptionist.work-diary.index')->with('success', 'Work diary entry updated successfully.');
  }

  public function destroyWorkDiary($id)
  {
    $entry = ReceptionistWorkDiary::where('user_id', (int) auth()->id())
      ->where('id', (int) $id)
      ->firstOrFail();

    $entry->delete();

    return redirect()->route('receptionist.work-diary.index')->with('success', 'Work diary entry deleted successfully.');
  }
}
