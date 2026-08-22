<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Faculty\Models\FacultyHoliday;
use App\Models\DepartmentActivity;
use App\Models\ApiMetrixCategory;
use App\Models\ApiMetrixComponent;
use App\Models\Faculty;
use App\Models\MethodologyMaster;
use App\Models\RoleMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\UserHasRole;
use App\Models\Weekday;
use App\Models\WorkDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkDiaryController extends Controller
{
  private $cachedFacultyId = null;
  private $hasRoleContextColumn = null;

  public function index(Request $request)
  {
    $facultyId = $this->getFacultyId();

    if (!$facultyId) {
      return redirect()->route('dashboard.switcher')->with('error', 'Work diary access requires a faculty linkage. Please contact ITCELL.');
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
    $entriesQuery = WorkDiary::where('faculty_id', $facultyId)
      ->whereBetween('date', [$weekStart, $weekEnd]);

    $this->applyWorkDiaryRoleContextScope($entriesQuery, true);
    $entries = $entriesQuery->get();

    // Fixed 8 periods without querying HourMaster
    $hours = collect([
      (object) ['id' => 1, 'title' => '1st Hour'],
      (object) ['id' => 2, 'title' => '2nd Hour'],
      (object) ['id' => 3, 'title' => '3rd Hour'],
      (object) ['id' => 4, 'title' => '4th Hour'],
      (object) ['id' => 5, 'title' => '5th Hour'],
      (object) ['id' => 6, 'title' => '6th Hour'],
      (object) ['id' => 7, 'title' => '7th Hour'],
      (object) ['id' => 8, 'title' => '8th Hour'],
      (object) ['id' => 9, 'title' => '9th Hour'],
      (object) ['id' => 10, 'title' => '10th Hour'],
    ]);

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

    $apiMetrixCategories = $this->getApplicableApiMetrixCategoriesQuery()
      ->orderBy('title')
      ->get(['id', 'title', 'slug']);

    // Get analytics data for the faculty in a single query
    $analyticsQuery = WorkDiary::selectRaw('
        SUM(CASE WHEN class_type = "regular" THEN 1 ELSE 0 END) as regular_count,
        SUM(CASE WHEN class_type = "extra" THEN 1 ELSE 0 END) as extra_count,
        SUM(CASE WHEN class_type = "substitution" THEN 1 ELSE 0 END) as substitution_count
      ')
      ->where('faculty_id', $facultyId);

    $this->applyWorkDiaryRoleContextScope($analyticsQuery, true);
    $analytics = $analyticsQuery->first();

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
      'apiMetrixCategories' => $apiMetrixCategories,
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
      'class_type' => 'required|string|exists:api_metrix_categories,title',
      'api_metrix_component_id' => 'required|integer|exists:api_metrix_components,id',
      'department_activity_id' => 'nullable|integer|exists:department_activities,id',
      'work_type' => 'nullable|string|in:library,research,prep class',
      'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
    ]);

    $facultyId = $this->getFacultyId();

    $category = $this->getApplicableApiMetrixCategoriesQuery()
      ->where('title', $request->class_type)
      ->first();

    $component = null;
    if ($category) {
      $component = ApiMetrixComponent::where('id', $request->api_metrix_component_id)
        ->where('api_metrix_category_id', $category->id)
        ->where('is_active', 1)
        ->first();
    }

    if (!$category || !$component) {
      return response()->json([
        'message' => 'Invalid category/component selection.',
        'errors' => [
          'api_metrix_component_id' => ['Please select a valid component for the selected category.']
        ]
      ], 422);
    }

    $selectedDepartmentEvent = $this->resolveSelectedDepartmentEvent(
      $facultyId,
      $category,
      $component,
      $request->input('department_activity_id')
    );

    $data = [
      'description' => $this->buildDescriptionWithDepartmentEvent($request->description, $selectedDepartmentEvent?->title),
      'methodology' => $component->title,
      'class_type' => $category->title,
      'work_type' => $request->work_type,
      'status' => 'pending'
    ];

    // Handle document upload
    if ($request->hasFile('document')) {
      $file = $request->file('document');
      $filename = StaticController::s3_file_uploader($file, 'work_diary_documents');
      $data['document_path'] = $filename;
    }

    $identity = [
      'faculty_id' => $facultyId,
      'date' => $request->date,
      'hour' => $request->hour,
    ];

    if ($this->hasWorkDiaryRoleContextColumn()) {
      $identity['role_context'] = $this->getCurrentWorkDiaryRoleContext();
    }

    $workDiary = WorkDiary::updateOrCreate($identity, $data);

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
      'class_type' => 'required|string|exists:api_metrix_categories,title',
      'api_metrix_component_id' => 'required|integer|exists:api_metrix_components,id',
      'department_activity_id' => 'nullable|integer|exists:department_activities,id',
      'work_type' => 'nullable|string|in:library,research,prep class',
      'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
    ]);

    $facultyId = $this->getFacultyId();
    $workDiaryQuery = WorkDiary::where('faculty_id', $facultyId)->where('id', (int) $id);
    $this->applyWorkDiaryRoleContextScope($workDiaryQuery, true);
    $workDiary = $workDiaryQuery->firstOrFail();

    $category = $this->getApplicableApiMetrixCategoriesQuery()
      ->where('title', $request->class_type)
      ->first();

    $component = null;
    if ($category) {
      $component = ApiMetrixComponent::where('id', $request->api_metrix_component_id)
        ->where('api_metrix_category_id', $category->id)
        ->where('is_active', 1)
        ->first();
    }

    if (!$category || !$component) {
      return response()->json([
        'message' => 'Invalid category/component selection.',
        'errors' => [
          'api_metrix_component_id' => ['Please select a valid component for the selected category.']
        ]
      ], 422);
    }

    $selectedDepartmentEvent = $this->resolveSelectedDepartmentEvent(
      $facultyId,
      $category,
      $component,
      $request->input('department_activity_id')
    );

    $data = [
      'description' => $this->buildDescriptionWithDepartmentEvent($request->description, $selectedDepartmentEvent?->title),
      'methodology' => $component->title,
      'class_type' => $category->title,
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
    $workDiaryQuery = WorkDiary::where('faculty_id', $facultyId)->where('id', (int) $id);
    $this->applyWorkDiaryRoleContextScope($workDiaryQuery, true);
    $workDiary = $workDiaryQuery->firstOrFail();
    $workDiary->delete();

    return response()->json([
      'success' => true,
      'message' => 'Work diary entry deleted successfully'
    ]);
  }

  public function toggleStatus($id)
  {
    $facultyId = $this->getFacultyId();
    $workDiaryQuery = WorkDiary::where('faculty_id', $facultyId)->where('id', (int) $id);
    $this->applyWorkDiaryRoleContextScope($workDiaryQuery, true);
    $workDiary = $workDiaryQuery->firstOrFail();

    $workDiary->status = $workDiary->status === 'pending' ? 'completed' : 'pending';
    $workDiary->save();

    return response()->json([
      'success' => true,
      'status' => $workDiary->status,
      'message' => 'Status updated successfully'
    ]);
  }

  public function getApiMetrixComponents(Request $request)
  {
    $request->validate([
      'category_title' => 'nullable|string',
      'category_slug' => 'nullable|string',
    ]);

    $categoryTitle = trim((string) $request->input('category_title', ''));
    $categorySlug = trim((string) $request->input('category_slug', ''));

    if ($categoryTitle === '' && $categorySlug === '') {
      return response()->json([
        'success' => false,
        'message' => 'Category title or slug is required.',
      ], 422);
    }

    $category = $this->getApplicableApiMetrixCategoriesQuery()
      ->where(function ($query) use ($categorySlug, $categoryTitle) {
        if ($categorySlug !== '') {
          $query->whereRaw('LOWER(slug) = ?', [mb_strtolower($categorySlug)]);
        }

        if ($categoryTitle !== '') {
          $titleQuery = mb_strtolower($categoryTitle);
          if ($categorySlug !== '') {
            $query->orWhereRaw('LOWER(title) = ?', [$titleQuery]);
          } else {
            $query->whereRaw('LOWER(title) = ?', [$titleQuery]);
          }
        }
      })
      ->first();

    if (!$category) {
      return response()->json([
        'success' => true,
        'components' => []
      ]);
    }

    $components = ApiMetrixComponent::query()
      ->with('verifierRole:id,role_name')
      ->where('api_metrix_category_id', $category->id)
      ->where('is_active', 1)
      ->orderBy('sort_order')
      ->orderBy('title')
      ->get(['id', 'title', 'verifier_role_master_id']);

    $components = $components->map(function ($component) {
      return [
        'id' => $component->id,
        'title' => $component->title,
        'verifier_role_name' => optional($component->verifierRole)->role_name,
      ];
    })->values();

    return response()->json([
      'success' => true,
      'components' => $components
    ]);
  }

  public function getApiMetrixCategories()
  {
    $categories = $this->getApplicableApiMetrixCategoriesQuery()
      ->orderBy('title')
      ->get(['id', 'title', 'slug']);

    return response()->json([
      'success' => true,
      'categories' => $categories
    ]);
  }

  public function getDepartmentInchargeEvents()
  {
    $facultyId = $this->getFacultyId();
    if (!$facultyId) {
      return response()->json([
        'success' => true,
        'activities' => []
      ]);
    }

    $activities = $this->getInchargeDepartmentActivitiesQuery($facultyId)
      ->orderByDesc('activity_date')
      ->get(['id', 'title', 'activity_date'])
      ->map(function ($activity) {
        return [
          'id' => $activity->id,
          'title' => $activity->title,
          'activity_date' => optional($activity->activity_date)->format('Y-m-d'),
        ];
      })
      ->values();

    return response()->json([
      'success' => true,
      'activities' => $activities,
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
    $entriesQuery = WorkDiary::where('faculty_id', $facultyId)
      ->whereBetween('date', [$monthStart, $monthEnd])
      ->orderBy('date')
      ->orderBy('hour');

    $this->applyWorkDiaryRoleContextScope($entriesQuery, true);
    $entries = $entriesQuery->get();

    // Calculate statistics
    $regularCount = $entries->where('class_type', 'regular')->count();
    $extraCount = $entries->where('class_type', 'extra')->count();
    $substitutionCount = $entries->where('class_type', 'substitution')->count();
    $totalClasses = $entries->count();

    // Group by work type for remedial classes
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

    $componentMetaMap = ApiMetrixComponent::query()
      ->with(['category:id,title', 'verifierRole:id,role_name'])
      ->get(['id', 'api_metrix_category_id', 'title', 'score', 'verifier_role_master_id'])
      ->mapWithKeys(function ($component) {
        $categoryTitle = trim((string) optional($component->category)->title);
        $componentTitle = trim((string) $component->title);

        if ($categoryTitle === '' || $componentTitle === '') {
          return [];
        }

        $key = mb_strtolower($categoryTitle) . '|' . mb_strtolower($componentTitle);
        return [
          $key => [
            'score' => (float) ($component->score ?? 0),
            'verifier_role_name' => trim((string) optional($component->verifierRole)->role_name) ?: 'Verifier Not Assigned',
          ],
        ];
      });

    $approvedStatuses = ['approved', 'completed'];

    $categoryAnalytics = $entries
      ->groupBy(function ($entry) {
        $category = trim((string) ($entry->class_type ?? ''));
        return $category !== '' ? $category : 'Uncategorized';
      })
      ->map(function ($group, $categoryTitle) use ($componentMetaMap, $approvedStatuses) {
        $filledCount = $group->count();
        $approvedCount = $group->filter(function ($entry) use ($approvedStatuses) {
          return in_array(mb_strtolower(trim((string) ($entry->status ?? ''))), $approvedStatuses, true);
        })->count();

        $resolveComponentMeta = function ($entry) use ($componentMetaMap, $categoryTitle): array {
          $componentTitle = trim((string) ($entry->methodology ?? ''));
          if ($componentTitle === '') {
            return [
              'score' => 0,
              'verifier_role_name' => 'Verifier Not Assigned',
            ];
          }

          $key = mb_strtolower(trim((string) $categoryTitle)) . '|' . mb_strtolower($componentTitle);
          return $componentMetaMap[$key] ?? [
            'score' => 0,
            'verifier_role_name' => 'Verifier Not Assigned',
          ];
        };

        $totalApiScore = (float) $group->sum(fn($entry) => (float) ($resolveComponentMeta($entry)['score'] ?? 0));
        $approvedApiScore = (float) $group
          ->filter(function ($entry) use ($approvedStatuses) {
            return in_array(mb_strtolower(trim((string) ($entry->status ?? ''))), $approvedStatuses, true);
          })
          ->sum(fn($entry) => (float) ($resolveComponentMeta($entry)['score'] ?? 0));

        $pendingWith = $group
          ->filter(function ($entry) use ($approvedStatuses) {
            return !in_array(mb_strtolower(trim((string) ($entry->status ?? ''))), $approvedStatuses, true);
          })
          ->map(function ($entry) use ($resolveComponentMeta) {
            return (string) ($resolveComponentMeta($entry)['verifier_role_name'] ?? 'Verifier Not Assigned');
          })
          ->countBy()
          ->map(function ($count, $roleName) {
            return $roleName . ' (' . $count . ')';
          })
          ->values()
          ->implode(', ');

        return [
          'category_title' => $categoryTitle,
          'filled_count' => $filledCount,
          'approved_count' => $approvedCount,
          'pending_count' => max(0, $filledCount - $approvedCount),
          'pending_with' => $pendingWith !== '' ? $pendingWith : '—',
          'total_api_score' => round($totalApiScore, 2),
          'approved_api_score' => round($approvedApiScore, 2),
        ];
      })
      ->sortByDesc('filled_count')
      ->values();

    $verificationSummary = [
      'filled_count' => (int) $categoryAnalytics->sum('filled_count'),
      'approved_count' => (int) $categoryAnalytics->sum('approved_count'),
      'pending_count' => (int) $categoryAnalytics->sum('pending_count'),
      'total_api_score' => round((float) $categoryAnalytics->sum('total_api_score'), 2),
      'approved_api_score' => round((float) $categoryAnalytics->sum('approved_api_score'), 2),
    ];
    $verificationSummary['approval_rate'] = $verificationSummary['filled_count'] > 0
      ? round(($verificationSummary['approved_count'] / $verificationSummary['filled_count']) * 100, 1)
      : 0;

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
      'categoryAnalytics' => $categoryAnalytics,
      'verificationSummary' => $verificationSummary,
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

  private function hasWorkDiaryRoleContextColumn(): bool
  {
    if ($this->hasRoleContextColumn === null) {
      $this->hasRoleContextColumn = Schema::hasColumn('work_diaries', 'role_context');
    }

    return (bool) $this->hasRoleContextColumn;
  }

  private function getCurrentWorkDiaryRoleContext(): string
  {
    $activeRole = mb_strtolower(trim((string) session('active_dashboard_role', '')));

    if (in_array($activeRole, ['dean', 'dean-office'], true)) {
      return 'dean-office';
    }

    if (in_array($activeRole, ['dean-of-student-affairs', 'dean-student-affairs', 'student-affairs-dean'], true)) {
      return 'dean-student-affairs';
    }

    if (in_array($activeRole, ['super-admin', 'admin', 'itcell'], true)) {
      return 'admin';
    }

    return 'faculty';
  }

  private function applyWorkDiaryRoleContextScope($query, bool $includeLegacyFaculty = false): void
  {
    if (!$this->hasWorkDiaryRoleContextColumn()) {
      return;
    }

    $context = $this->getCurrentWorkDiaryRoleContext();

    if ($includeLegacyFaculty && $context === 'faculty') {
      $query->where(function ($inner) use ($context) {
        $inner->where('role_context', $context)
          ->orWhereNull('role_context')
          ->orWhere('role_context', '');
      });
      return;
    }

    $query->where('role_context', $context);
  }

  private function getApplicableApiMetrixCategoriesQuery()
  {
    $facultyId = $this->getFacultyId();

    $facultyAccessUserIds = SubjectFacultyMaster::query()
      ->when($facultyId > 0, function ($query) use ($facultyId) {
        $query->where('faculty_id', $facultyId);
      }, function ($query) {
        $query->where('access_id', Auth::id());
      })
      ->pluck('access_id')
      ->filter(fn($id) => !empty($id))
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    if ($facultyAccessUserIds->isEmpty() && Auth::id()) {
      $facultyAccessUserIds = collect([(int) Auth::id()]);
    }

    $facultyRoleTypes = $this->resolveFacultyRoleTypes($facultyAccessUserIds);

    $activeRoleType = $this->resolveActiveDashboardRoleType();
    if ($activeRoleType !== null && $activeRoleType !== '') {
      $facultyRoleTypes = collect([$activeRoleType]);
    }

    return ApiMetrixCategory::query()
      ->where('status', 'active')
      ->where('show_in_workdiary', 1)
      ->where(function ($query) use ($facultyRoleTypes) {
        $query->whereDoesntHave('roles');

        if ($facultyRoleTypes->isNotEmpty()) {
          $query->orWhereHas('roles', function ($roleQuery) use ($facultyRoleTypes) {
            $roleQuery->whereIn('role_masters.roletype', $facultyRoleTypes->all());
          });
        }
      });
  }

  private function resolveActiveDashboardRoleType(): ?string
  {
    $activeRole = mb_strtolower(trim((string) session('active_dashboard_role', '')));
    if ($activeRole === '') {
      return null;
    }

    $roleMaster = RoleMaster::query()
      ->where(function ($query) use ($activeRole) {
        $query->whereRaw('LOWER(TRIM(slug)) = ?', [$activeRole])
          ->orWhereRaw('LOWER(TRIM(role_name)) = ?', [$activeRole]);
      })
      ->first(['roletype']);

    $roleType = trim((string) ($roleMaster->roletype ?? ''));
    return $roleType !== '' ? $roleType : null;
  }

  private function resolveFacultyRoleTypes($facultyAccessUserIds)
  {
    $userRolesQuery = UserHasRole::query()
      ->whereIn('user_id', $facultyAccessUserIds);

    // Support both schema variants:
    // 1) user_has_roles.role_id -> role_masters.id
    // 2) user_has_roles.role_name (legacy) matched against role_masters.slug/role_name
    if (Schema::hasColumn('user_has_roles', 'role_id')) {
      return (clone $userRolesQuery)
        ->join('role_masters', 'role_masters.id', '=', 'user_has_roles.role_id')
        ->pluck('role_masters.roletype')
        ->filter(fn($type) => !empty($type))
        ->map(fn($type) => trim((string) $type))
        ->unique()
        ->values();
    }

    if (!Schema::hasColumn('user_has_roles', 'role_name')) {
      return collect();
    }

    $assignedRoleKeys = (clone $userRolesQuery)
      ->pluck('role_name')
      ->filter(fn($roleName) => !empty($roleName))
      ->map(fn($roleName) => mb_strtolower(trim((string) $roleName)))
      ->unique()
      ->values();

    if ($assignedRoleKeys->isEmpty()) {
      return collect();
    }

    return RoleMaster::query()
      ->get(['slug', 'role_name', 'roletype'])
      ->filter(function ($roleMaster) use ($assignedRoleKeys) {
        $slug = mb_strtolower(trim((string) ($roleMaster->slug ?? '')));
        $roleName = mb_strtolower(trim((string) ($roleMaster->role_name ?? '')));

        return $assignedRoleKeys->contains($slug) || $assignedRoleKeys->contains($roleName);
      })
      ->pluck('roletype')
      ->filter(fn($type) => !empty($type))
      ->map(fn($type) => trim((string) $type))
      ->unique()
      ->values();
  }

  private function shouldUseDepartmentEventSelector(ApiMetrixCategory $category, ApiMetrixComponent $component): bool
  {
    $categorySlug = mb_strtolower(trim((string) ($category->slug ?? '')));
    $categoryTitle = mb_strtolower(trim((string) ($category->title ?? '')));
    $componentTitle = mb_strtolower(trim((string) ($component->title ?? '')));

    $normalizedCategory = preg_replace('/[^a-z0-9]+/u', ' ', $categoryTitle);
    $normalizedComponent = preg_replace('/[^a-z0-9]+/u', ' ', $componentTitle);

    $isCoCurricularCategory =
      str_contains($categorySlug, 'co-curricular')
      || str_contains($categorySlug, 'co curricular')
      || str_contains($normalizedCategory, 'co curricular');

    $isDepartmentCoCurricularComponent =
      str_contains($normalizedComponent, 'department')
      && str_contains($normalizedComponent, 'co curricular');

    return $isCoCurricularCategory && $isDepartmentCoCurricularComponent;
  }

  private function resolveSelectedDepartmentEvent($facultyId, ApiMetrixCategory $category, ApiMetrixComponent $component, $departmentActivityId): ?DepartmentActivity
  {
    if (!$this->shouldUseDepartmentEventSelector($category, $component)) {
      return null;
    }

    if (empty($departmentActivityId)) {
      throw \Illuminate\Validation\ValidationException::withMessages([
        'department_activity_id' => ['Please select a department event where you are incharge.']
      ]);
    }

    $event = $this->getInchargeDepartmentActivitiesQuery($facultyId)
      ->where('id', (int) $departmentActivityId)
      ->first();

    if (!$event) {
      throw \Illuminate\Validation\ValidationException::withMessages([
        'department_activity_id' => ['Please select a valid department event where you are incharge.']
      ]);
    }

    return $event;
  }

  private function buildDescriptionWithDepartmentEvent(string $description, ?string $eventTitle): string
  {
    $cleanDescription = preg_replace('/^\[Dept Event:\s.*?\]\s*/u', '', trim($description));
    if (empty($eventTitle)) {
      return $cleanDescription;
    }

    return '[Dept Event: ' . trim($eventTitle) . '] ' . $cleanDescription;
  }

  private function getInchargeDepartmentActivitiesQuery($facultyId)
  {
    $faculty = Faculty::find($facultyId);
    if (!$faculty) {
      return DepartmentActivity::query()->whereRaw('1 = 0');
    }

    $firstName = trim((string) ($faculty->FIRST_NAME ?? ''));
    $middleName = trim((string) ($faculty->MIDDLE_NAME ?? ''));
    $lastName = trim((string) ($faculty->LAST_NAME ?? ''));
    $facultyName = trim($firstName . ' ' . $lastName);
    $facultyNameWithMiddle = trim($firstName . ' ' . $middleName . ' ' . $lastName);
    $facultyEmail = mb_strtolower(trim((string) ($faculty->MAIL_ID ?? '')));
    $facultyPhone = trim((string) ($faculty->MOBILE_NO ?? ''));

    $nameMatchers = array_values(array_filter(array_unique([
      mb_strtolower($facultyName),
      mb_strtolower($facultyNameWithMiddle),
    ])));

    $likeMatchers = [];
    if ($firstName !== '' && $lastName !== '') {
      $likeMatchers[] = mb_strtolower($firstName) . '% ' . mb_strtolower($lastName) . '%';
      $likeMatchers[] = mb_strtolower($firstName) . '%' . mb_strtolower($lastName) . '%';
    }

    if ($facultyEmail === '' && empty($nameMatchers) && empty($likeMatchers) && $facultyPhone === '') {
      return DepartmentActivity::query()->whereRaw('1 = 0');
    }

    return DepartmentActivity::query()
      ->whereHas('participants', function ($query) use ($facultyEmail, $facultyPhone, $nameMatchers, $likeMatchers) {
        $query->where('participation_type', 'internal')
          ->where('participant_category', 'faculty')
          ->where('is_incharge', 1)
          ->where(function ($matchQuery) use ($facultyEmail, $facultyPhone, $nameMatchers, $likeMatchers) {
            $hasCondition = false;

            if ($facultyEmail !== '') {
              $matchQuery->whereRaw('LOWER(participant_email) = ?', [$facultyEmail]);
              $hasCondition = true;
            }

            if ($facultyPhone !== '') {
              if ($hasCondition) {
                $matchQuery->orWhere('participant_phone', $facultyPhone);
              } else {
                $matchQuery->where('participant_phone', $facultyPhone);
                $hasCondition = true;
              }
            }

            foreach ($nameMatchers as $nameMatcher) {
              if ($hasCondition) {
                $matchQuery->orWhereRaw('LOWER(TRIM(participant_name)) = ?', [$nameMatcher]);
              } else {
                $matchQuery->whereRaw('LOWER(TRIM(participant_name)) = ?', [$nameMatcher]);
                $hasCondition = true;
              }
            }

            foreach ($likeMatchers as $likeMatcher) {
              if ($hasCondition) {
                $matchQuery->orWhereRaw('LOWER(TRIM(participant_name)) LIKE ?', [$likeMatcher]);
              } else {
                $matchQuery->whereRaw('LOWER(TRIM(participant_name)) LIKE ?', [$likeMatcher]);
                $hasCondition = true;
              }
            }
          });
      });
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
