<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Faculty\Models\FacultyHoliday;
use App\Models\Faculty;
use App\Models\HourMaster;
use App\Models\MethodologyMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\Weekday;
use App\Models\WorkDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkDiaryController extends Controller
{
  private $cachedFacultyId = null;

  public function index(Request $request)
  {
    $facultyId = $this->getFacultyId();

    if (!$facultyId) {
      return redirect()->route('faculty.dashboard')->with('error', 'Faculty profile not found.');
    }

    // Get current week or the requested week
    try {
      $weekStart = $request->input('week_start')
        ? Carbon::parse($request->input('week_start'))
        : Carbon::now()->startOfWeek();
    } catch (\Exception $e) {
      $weekStart = Carbon::now()->startOfWeek();
    }

    $weekEnd = $weekStart->copy()->endOfWeek();

    // Get work diary entries for the week
    $entries = WorkDiary::where('faculty_id', $facultyId)
      ->whereBetween('date', [$weekStart, $weekEnd])
      ->get();

    // Get hours from HourMaster table (cached)
    $hours = Cache::remember('hour_master_all', 3600, function () {
      return HourMaster::orderBy('id')->get();
    });

    // Get weekdays from Weekday table (cached)
    $weekdays = Cache::remember('weekdays_titles', 3600, function () {
      return Weekday::orderBy('id')->pluck('title')->toArray();
    });

    // Organize entries by weekday and hour
    $calendar = [];

    foreach ($weekdays as $day) {
      $calendar[$day] = [];
      foreach ($hours as $hour) {
        $calendar[$day][$hour->title] = [];
      }
    }

    // Map entries into calendar
    foreach ($entries as $entry) {
      $weekday = $entry->date->format('l');
      $hourValue = $entry->hour;

      // The hour field stores the title (e.g., "1st Hour", "2nd Hour")
      if (isset($calendar[$weekday][$hourValue])) {
        $calendar[$weekday][$hourValue][] = $entry;
      }
    }

    // Get active methodologies (cached)
    $methodologies = Cache::remember('methodologies_active', 3600, function () {
      return MethodologyMaster::active()->ordered()->get();
    });

    // Get analytics data for the faculty in a single query
    $analytics = WorkDiary::selectRaw('
        SUM(CASE WHEN class_type = "regular" THEN 1 ELSE 0 END) as regular_count,
        SUM(CASE WHEN class_type = "extra" THEN 1 ELSE 0 END) as extra_count,
        SUM(CASE WHEN class_type = "substitution" THEN 1 ELSE 0 END) as substitution_count
      ')
      ->where('faculty_id', $facultyId)
      ->first();

    $regularCount = $analytics->regular_count ?? 0;
    $extraCount = $analytics->extra_count ?? 0;
    $substitutionCount = $analytics->substitution_count ?? 0;

    return view('faculty.workdiary', [
      'weekStart' => $weekStart,
      'weekEnd' => $weekEnd,
      'entries' => $entries,
      'hours' => $hours,
      'calendar' => $calendar,
      'weekdays' => $weekdays,
      'methodologies' => $methodologies,
      'regularCount' => $regularCount,
      'extraCount' => $extraCount,
      'substitutionCount' => $substitutionCount
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'date' => 'required|date',
      'hour' => 'required|string',
      'description' => 'required|string|max:1000',
      'methodology' => 'nullable|exists:methodology_masters,name',
      'class_type' => 'required|string|in:extra,regular,substitution',
      'work_type' => 'nullable|string|in:library,research,prep class',
      'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
    ]);

    $facultyId = $this->getFacultyId();

    $data = [
      'description' => $request->description,
      'methodology' => $request->methodology,
      'class_type' => $request->class_type,
      'work_type' => $request->work_type,
      'status' => 'pending'
    ];

    // Handle document upload
    if ($request->hasFile('document')) {
      $file = $request->file('document');
      $filename = StaticController::s3_file_uploader($file, 'work_diary_documents');
      $data['document_path'] = $filename;
    }

    $workDiary = WorkDiary::updateOrCreate(
      [
        'faculty_id' => $facultyId,
        'date' => $request->date,
        'hour' => $request->hour
      ],
      $data
    );

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry saved successfully',
      'data' => $workDiary
    ]);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'description' => 'required|string|max:1000',
      'methodology' => 'nullable|exists:methodology_masters,name',
      'class_type' => 'required|string|in:extra,regular,substitution',
      'work_type' => 'nullable|string|in:library,research,prep class',
      'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
    ]);

    $facultyId = $this->getFacultyId();
    $workDiary = WorkDiary::where('faculty_id', $facultyId)->findOrFail($id);

    $data = [
      'description' => $request->description,
      'methodology' => $request->methodology,
      'class_type' => $request->class_type,
      'work_type' => $request->work_type
    ];

    // Handle document upload
    if ($request->hasFile('document')) {
      // Delete old document if it exists
      if ($workDiary->document_path && Storage::disk('public')->exists($workDiary->document_path)) {
        Storage::disk('public')->delete($workDiary->document_path);
      }

      $file = $request->file('document');
      $fileName = time() . '_' . $file->getClientOriginalName();
      $filePath = $file->storeAs('work_diary_documents', $fileName, 'public');
      $data['document_path'] = $filePath;
    }

    $workDiary->update($data);

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry updated successfully',
      'data' => $workDiary
    ]);
  }

  public function destroy($id)
  {
    $facultyId = $this->getFacultyId();
    $workDiary = WorkDiary::where('faculty_id', $facultyId)->findOrFail($id);
    $workDiary->delete();

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry deleted successfully'
    ]);
  }

  public function toggleStatus($id)
  {
    $facultyId = $this->getFacultyId();
    $workDiary = WorkDiary::where('faculty_id', $facultyId)->findOrFail($id);

    $workDiary->status = $workDiary->status === 'pending' ? 'completed' : 'pending';
    $workDiary->save();

    return response()->json([
      'success' => true,
      'status' => $workDiary->status,
      'message' => 'Status updated successfully'
    ]);
  }

  public function monthlyReport(Request $request)
  {
    $data = $this->getMonthlyReportData($request);
    return view('faculty.monthly-report', $data);
  }

  public function downloadMonthlyReportPdf(Request $request)
  {
    $data = $this->getMonthlyReportData($request);
    $data['isPdf'] = true;

    $pdf = Pdf::loadView('faculty.monthly-report-pdf', $data)
      ->setPaper('a4', 'portrait')
      ->setOption('margin-top', 10)
      ->setOption('margin-bottom', 10)
      ->setOption('margin-left', 10)
      ->setOption('margin-right', 10);

    $fileName = 'work-diary-report-' . $data['month']->format('Y-m') . '.pdf';
    return $pdf->download($fileName);
  }

  private function getMonthlyReportData(Request $request)
  {
    $facultyId = $this->getFacultyId();

    // Get current month or the requested month
    try {
      $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();
    } catch (\Exception $e) {
      $month = Carbon::now();
    }

    $monthStart = $month->copy()->startOfMonth();
    $monthEnd = $month->copy()->endOfMonth();

    // Get all entries for the month
    $entries = WorkDiary::where('faculty_id', $facultyId)
      ->whereBetween('date', [$monthStart, $monthEnd])
      ->orderBy('date')
      ->orderBy('hour')
      ->get();

    // Calculate statistics
    $regularCount = $entries->where('class_type', 'regular')->count();
    $extraCount = $entries->where('class_type', 'extra')->count();
    $substitutionCount = $entries->where('class_type', 'substitution')->count();
    $totalClasses = $entries->count();

    // Group by work type for extra classes
    $workTypeBreakdown = $entries->where('class_type', 'extra')
      ->groupBy('work_type')
      ->map(function ($group) {
        return $group->count();
      });

    // Group by methodology
    $methodologyBreakdown = $entries->whereNotNull('methodology')
      ->groupBy('methodology')
      ->map(function ($group) {
        return $group->count();
      });

    // Group by week
    $weeklyBreakdown = $entries->groupBy(function ($entry) {
      return $entry->date->format('W'); // Week number
    })->map(function ($week) {
      return [
        'total' => $week->count(),
        'regular' => $week->where('class_type', 'regular')->count(),
        'extra' => $week->where('class_type', 'extra')->count(),
        'substitution' => $week->where('class_type', 'substitution')->count(),
      ];
    });

    // Group by date for daily view
    $dailyEntries = $entries->groupBy(function ($entry) {
      return $entry->date->format('Y-m-d');
    });

    // Get faculty details
    $faculty = SubjectFacultyMaster::with('faculty')->where('faculty_id', $facultyId)->first();

    return [
      'month' => $month,
      'monthStart' => $monthStart,
      'monthEnd' => $monthEnd,
      'entries' => $entries,
      'dailyEntries' => $dailyEntries,
      'regularCount' => $regularCount,
      'extraCount' => $extraCount,
      'substitutionCount' => $substitutionCount,
      'totalClasses' => $totalClasses,
      'workTypeBreakdown' => $workTypeBreakdown,
      'methodologyBreakdown' => $methodologyBreakdown,
      'weeklyBreakdown' => $weeklyBreakdown,
      'faculty' => $faculty,
      'isPdf' => false
    ];
  }

  private function getFacultyId()
  {
    if ($this->cachedFacultyId !== null) {
      return $this->cachedFacultyId;
    }

    $userId = Auth::user()->id;
    $this->cachedFacultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    return $this->cachedFacultyId;
  }

  // Holiday Management Methods
  public function storeHoliday(Request $request)
  {
    $request->validate([
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'reason' => 'nullable|string|max:500',
      'type' => 'required|string|in:holiday,leave,vacation'
    ]);

    $facultyId = $this->getFacultyId();

    // Check for overlapping holidays
    $overlap = FacultyHoliday::where('faculty_id', $facultyId)
      ->where(function ($query) use ($request) {
        $query->whereBetween('start_date', [$request->start_date, $request->end_date])
          ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
          ->orWhere(function ($q) use ($request) {
            $q->where('start_date', '<=', $request->start_date)
              ->where('end_date', '>=', $request->end_date);
          });
      })
      ->exists();

    if ($overlap) {
      return response()->json([
        'success' => false,
        'message' => 'This date range overlaps with an existing holiday/leave.'
      ], 422);
    }

    $holiday = FacultyHoliday::create([
      'faculty_id' => $facultyId,
      'start_date' => $request->start_date,
      'end_date' => $request->end_date,
      'reason' => $request->reason,
      'type' => $request->type
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Holiday marked successfully!',
      'holiday' => $holiday
    ]);
  }

  public function getHolidays(Request $request)
  {
    $facultyId = $this->getFacultyId();

    try {
      $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();
    } catch (\Exception $e) {
      $month = Carbon::now();
    }

    $monthStart = $month->copy()->startOfMonth();
    $monthEnd = $month->copy()->endOfMonth();

    $holidays = FacultyHoliday::where('faculty_id', $facultyId)
      ->whereNotNull('start_date')
      ->whereNotNull('end_date')
      ->where(function ($query) use ($monthStart, $monthEnd) {
        $query->whereBetween('start_date', [$monthStart, $monthEnd])
          ->orWhereBetween('end_date', [$monthStart, $monthEnd])
          ->orWhere(function ($q) use ($monthStart, $monthEnd) {
            $q->where('start_date', '<=', $monthStart)
              ->where('end_date', '>=', $monthEnd);
          });
      })
      ->get();

    return response()->json([
      'success' => true,
      'holidays' => $holidays
    ]);
  }

  public function deleteHoliday($id)
  {
    $facultyId = $this->getFacultyId();

    $holiday = FacultyHoliday::where('faculty_id', $facultyId)->findOrFail($id);
    $holiday->delete();

    return response()->json([
      'success' => true,
      'message' => 'Holiday deleted successfully!'
    ]);
  }
}
