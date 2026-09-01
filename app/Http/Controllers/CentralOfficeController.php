<?php

namespace App\Http\Controllers;

use App\Exports\CentralOfficeStudentTemplateExport;
use App\Models\AdmissionApplication;
use App\Models\AdmissionRegistration;
use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class CentralOfficeController extends Controller
{
  private function resolveSiliguriCampusId(): int
  {
    $campusId = (int) DB::table('campuses')
      ->where(function ($query) {
        $query->where('slug', 'siliguri-campus')
          ->orWhere('slug', 'siliguri')
          ->orWhere('name', 'like', '%Siliguri%');
      })
      ->orderBy('id')
      ->value('id');

    return $campusId > 0 ? $campusId : 0;
  }

  private function ensureCentralOfficeAccess(): void
  {
    if (!Auth::check()) {
      abort(401);
    }

    $roleColumns = ['role_name'];
    if (Schema::hasColumn('user_has_roles', 'role_type')) {
      $roleColumns[] = 'role_type';
    }

    $roles = UserHasRole::where('user_id', Auth::id())->get($roleColumns);

    $normalize = function (?string $value): string {
      $v = strtolower(trim((string) $value));
      return str_replace(['_', ' '], '-', $v);
    };

    $roleNames = $roles
      ->pluck('role_name')
      ->map(fn($v) => $normalize($v))
      ->filter(fn($v) => $v !== '')
      ->values();

    $roleTypes = collect();
    if (in_array('role_type', $roleColumns, true)) {
      $roleTypes = $roles
        ->pluck('role_type')
        ->map(fn($v) => $normalize($v))
        ->filter(fn($v) => $v !== '')
        ->values();
    }

    $isCentralOffice = $roleNames->contains(fn($v) => $v === 'central-office' || str_starts_with($v, 'central-office'))
      || $roleTypes->contains(fn($v) => $v === 'central-office' || str_starts_with($v, 'central-office'));

    if (!$isCentralOffice) {
      abort(403, 'You are not authorized to access Central Office module.');
    }
  }

  private function buildStudentsQuery(int $batchId, string $status, string $search)
  {
    $studentTable = (new StudentMaster())->getTable();

    return StudentMaster::with(['batchmaster:id,batch_name', 'stdprogramenrolled:id,code,name'])
      ->when(Schema::hasColumn($studentTable, 'is_deleted'), fn($q) => $q->where('is_deleted', 0))
      ->when($batchId > 0, fn($q) => $q->where('batch', $batchId))
      ->when($status === 'active', function ($q) use ($studentTable) {
        if (Schema::hasColumn($studentTable, 'is_left')) {
          $q->where(function ($inner) {
            $inner->whereNull('is_left')->orWhere('is_left', 0);
          });
        }
      })
      ->when($status === 'left', function ($q) use ($studentTable) {
        if (Schema::hasColumn($studentTable, 'is_left')) {
          $q->where('is_left', 1);
        }
      })
      ->when($search !== '', function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
          $inner->where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('roll_no', 'like', "%{$search}%")
            ->orWhere('register_no', 'like', "%{$search}%")
            ->orWhere('mobile_no', 'like', "%{$search}%");
        });
      })
      ->orderBy('roll_no')
      ->orderBy('id');
  }

  private function buildAdmissionsBatchSummaryQuery(string $batch)
  {
    return DB::table('admission_registrations as ar')
      ->leftJoin('admission_applications as aa', 'aa.registration_id', '=', 'ar.id')
      ->leftJoin('admission_first_phases as af1', 'af1.reg_id', '=', 'ar.id')
      ->leftJoin('admission_final_phases as af2', 'af2.reg_id', '=', 'ar.id')
      ->whereNotNull('ar.batch')
      ->where('ar.batch', '!=', '')
      ->when($batch !== '', fn($q) => $q->where('ar.batch', $batch))
      ->selectRaw('ar.batch as batch_name')
      ->selectRaw('COUNT(DISTINCT ar.id) as total_registrations')
      ->selectRaw("SUM(CASE WHEN ar.application_type = 'UG' THEN 1 ELSE 0 END) as ug_registrations")
      ->selectRaw("SUM(CASE WHEN ar.application_type = 'PG' THEN 1 ELSE 0 END) as pg_registrations")
      ->selectRaw('COUNT(DISTINCT aa.id) as submitted_applications')
      ->selectRaw('COUNT(DISTINCT af1.id) as phase1_count')
      ->selectRaw('COUNT(DISTINCT af2.id) as phase2_count')
      ->selectRaw('SUM(CASE WHEN ar.is_enrolled = 1 THEN 1 ELSE 0 END) as enrolled_count')
      ->groupBy('ar.batch')
      ->orderByDesc('ar.batch');
  }

  private function buildEmployeesQuery(int $departmentId, string $status, string $search, int $campusId)
  {
    $normalizedStatus = strtolower(trim($status));
    if (!in_array($normalizedStatus, ['active', 'left', 'deleted', 'all'], true)) {
      $normalizedStatus = 'active';
    }

    $query = Faculty::query()->select('faculties.*');

    // Derive sortable academic context from subject/deanery mappings.
    $query->selectSub(function ($subQuery) {
      $subQuery->from('subject_faculty_masters as sfm')
        ->join('subjects as s', 's.id', '=', 'sfm.subject_id')
        ->whereColumn('sfm.faculty_id', 'faculties.id')
        ->whereNull('s.deleted_at')
        ->selectRaw('MIN(s.title)');
    }, 'primary_department_name');

    $query->selectSub(function ($subQuery) {
      $subQuery->from('subject_faculty_masters as sfm')
        ->join('subjects as s', 's.id', '=', 'sfm.subject_id')
        ->leftJoin('deanery_dept_pivots as ddp', function ($join) {
          $join->on('ddp.dept_id', '=', 's.id');
          $join->whereNull('ddp.deleted_at');
        })
        ->leftJoin('deaneries as d', 'd.id', '=', 'ddp.deanery_id')
        ->whereColumn('sfm.faculty_id', 'faculties.id')
        ->whereNull('s.deleted_at')
        ->selectRaw('MIN(d.title)');
    }, 'primary_deanery_name');

    if ($campusId > 0) {
      $query->where('CAMPUS_ID', $campusId);
    }

    if ($normalizedStatus === 'all') {
      $query->withTrashed();
    }

    if ($normalizedStatus === 'deleted') {
      $query->onlyTrashed();
    }

    if ($normalizedStatus === 'active') {
      $query->whereNull('deleted_at')
        ->where(function ($inner) {
          $inner->whereNull('IS_LEFT')->orWhere('IS_LEFT', 0);
        });
    }

    if ($normalizedStatus === 'left') {
      $query->whereNull('deleted_at')
        ->where('IS_LEFT', 1);
    }

    $query->when($departmentId > 0, function ($q) use ($departmentId) {
      $q->whereExists(function ($subQuery) use ($departmentId) {
        $subQuery->select(DB::raw(1))
          ->from('subject_faculty_masters as sfm')
          ->join('subjects as s', 's.id', '=', 'sfm.subject_id')
          ->whereColumn('sfm.faculty_id', 'faculties.id')
          ->where('sfm.subject_id', $departmentId)
          ->whereNull('s.deleted_at');
      });
    })
      ->when($search !== '', function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
          $inner->where('USER_CODE', 'like', "%{$search}%")
            ->orWhere('FIRST_NAME', 'like', "%{$search}%")
            ->orWhere('MIDDLE_NAME', 'like', "%{$search}%")
            ->orWhere('LAST_NAME', 'like', "%{$search}%")
            ->orWhere('MAIL_ID', 'like', "%{$search}%")
            ->orWhere('MOBILE_NO', 'like', "%{$search}%");
        });
      })
      ->orderByRaw("COALESCE(primary_deanery_name, 'ZZZ') ASC")
      ->orderByRaw("COALESCE(primary_department_name, 'ZZZ') ASC")
      ->orderBy('FIRST_NAME')
      ->orderBy('LAST_NAME')
      ->orderBy('id');

    return $query;
  }

  private function attachEmployeeAcademicContext(Collection $employees, int $campusId): Collection
  {
    if ($employees->isEmpty()) {
      return $employees;
    }

    $facultyIds = $employees
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($facultyIds->isEmpty()) {
      return $employees;
    }

    $rows = DB::table('subject_faculty_masters as sfm')
      ->join('subjects as s', 's.id', '=', 'sfm.subject_id')
      ->leftJoin('deanery_dept_pivots as ddp', function ($join) {
        $join->on('ddp.dept_id', '=', 's.id');
        $join->whereNull('ddp.deleted_at');
      })
      ->leftJoin('deaneries as d', 'd.id', '=', 'ddp.deanery_id')
      ->whereIn('sfm.faculty_id', $facultyIds->all())
      ->whereNull('s.deleted_at')
      ->when($campusId > 0, fn($query) => $query->where('s.campus_id', $campusId))
      ->select('sfm.faculty_id', 's.id as subject_id', 's.title as subject_title', 'd.title as deanery_title')
      ->get();

    $contextByFaculty = $rows
      ->groupBy('faculty_id')
      ->map(function ($items) {
        $departmentNames = $items
          ->map(fn($item) => trim((string) ($item->subject_title ?? '')))
          ->filter(fn($value) => $value !== '')
          ->unique()
          ->values();

        $deaneryNames = $items
          ->map(fn($item) => trim((string) ($item->deanery_title ?? '')))
          ->filter(fn($value) => $value !== '')
          ->unique()
          ->values();

        return [
          'department_names' => $departmentNames,
          'deanery_names' => $deaneryNames,
        ];
      });

    return $employees->map(function ($employee) use ($contextByFaculty) {
      $context = $contextByFaculty->get((int) $employee->id, [
        'department_names' => collect(),
        'deanery_names' => collect(),
      ]);

      $employee->subject_departments = $context['department_names'];
      $employee->deanery_names = $context['deanery_names'];

      return $employee;
    });
  }

  private function getFacultyDeleteBlockers(int $facultyId): array
  {
    $checks = [
      ['table' => 'subject_has_routines', 'label' => 'timetable routines'],
      ['table' => 'teaching_assignments', 'label' => 'teaching assignments'],
      ['table' => 'teaching_assignment_faculties', 'label' => 'co-faculty assignments'],
    ];

    return collect($checks)
      ->map(function ($check) use ($facultyId) {
        $table = (string) $check['table'];
        $label = (string) $check['label'];

        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'faculty_id')) {
          return null;
        }

        $query = DB::table($table)->where('faculty_id', $facultyId);

        if (Schema::hasColumn($table, 'deleted_at')) {
          $query->whereNull('deleted_at');
        }

        if ($table === 'teaching_assignments' && Schema::hasColumn($table, 'is_active')) {
          $query->where('is_active', 1);
        }

        $count = (int) $query->count();

        if ($count <= 0) {
          return null;
        }

        return [
          'table' => $table,
          'label' => $label,
          'count' => $count,
        ];
      })
      ->filter()
      ->values()
      ->all();
  }

  public function dashboard()
  {
    $this->ensureCentralOfficeAccess();
    $siliguriCampusId = $this->resolveSiliguriCampusId();

    $studentTable = (new StudentMaster())->getTable();

    $activeStudents = StudentMaster::query()
      ->when(Schema::hasColumn($studentTable, 'is_deleted'), fn($q) => $q->where('is_deleted', 0))
      ->when(Schema::hasColumn($studentTable, 'is_left'), function ($q) {
        $q->where(function ($inner) {
          $inner->whereNull('is_left')->orWhere('is_left', 0);
        });
      })
      ->count();

    $leftStudents = StudentMaster::query()
      ->when(Schema::hasColumn($studentTable, 'is_deleted'), fn($q) => $q->where('is_deleted', 0))
      ->when(Schema::hasColumn($studentTable, 'is_left'), fn($q) => $q->where('is_left', 1))
      ->count();

    $totalBatches = BatchMaster::count();
    $totalEmployees = Faculty::query()
      ->whereNull('deleted_at')
      ->when($siliguriCampusId > 0, fn($query) => $query->where('CAMPUS_ID', $siliguriCampusId))
      ->count();

    $recentAdmissions = AdmissionRegistration::query()
      ->latest('id')
      ->limit(8)
      ->get(['id', 'first_name', 'last_name', 'application_type', 'batch', 'created_at']);

    return view('central-office.dashboard', [
      'activeStudents' => $activeStudents,
      'leftStudents' => $leftStudents,
      'totalBatches' => $totalBatches,
      'totalEmployees' => $totalEmployees,
      'recentAdmissions' => $recentAdmissions,
    ]);
  }

  public function students(Request $request)
  {
    $this->ensureCentralOfficeAccess();

    $batchId = (int) $request->input('batch_id', 0);
    $status = (string) $request->input('status', 'active');
    $search = trim((string) $request->input('search', ''));

    $students = $this->buildStudentsQuery($batchId, $status, $search)
      ->paginate(25)
      ->appends($request->query());

    $batches = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);

    return view('central-office.students.index', [
      'students' => $students,
      'batches' => $batches,
      'batchId' => $batchId,
      'status' => $status,
      'search' => $search,
    ]);
  }

  public function employees(Request $request)
  {
    $this->ensureCentralOfficeAccess();
    $siliguriCampusId = $this->resolveSiliguriCampusId();

    $departmentId = (int) $request->input('department_id', 0);
    $status = strtolower(trim((string) $request->input('status', 'active')));
    if (!in_array($status, ['active', 'left', 'deleted', 'all'], true)) {
      $status = 'active';
    }
    $search = trim((string) $request->input('search', ''));

    $employees = $this->buildEmployeesQuery($departmentId, $status, $search, $siliguriCampusId)
      ->paginate(25)
      ->appends($request->query());

    $enrichedItems = $this->attachEmployeeAcademicContext(collect($employees->items()), $siliguriCampusId);
    $employees = new LengthAwarePaginator(
      $enrichedItems,
      $employees->total(),
      $employees->perPage(),
      $employees->currentPage(),
      [
        'path' => $request->url(),
        'query' => $request->query(),
      ]
    );

    $departments = DB::table('subjects')
      ->whereNull('deleted_at')
      ->when($siliguriCampusId > 0, fn($query) => $query->where('campus_id', $siliguriCampusId))
      ->orderBy('title')
      ->get(['id', 'title']);

    return view('central-office.employees.index', [
      'employees' => $employees,
      'departments' => $departments,
      'departmentId' => $departmentId,
      'status' => $status,
      'search' => $search,
      'totalEmployeesCollege' => Faculty::query()
        ->whereNull('deleted_at')
        ->when($siliguriCampusId > 0, fn($query) => $query->where('CAMPUS_ID', $siliguriCampusId))
        ->count(),
    ]);
  }

  public function markLeft(int $id)
  {
    $this->ensureCentralOfficeAccess();

    $student = StudentMaster::findOrFail($id);
    $student->is_left = 1;
    $student->save();

    return redirect()->back()->with('success', 'Student marked as left successfully.');
  }

  public function exportStudents(Request $request)
  {
    $this->ensureCentralOfficeAccess();

    $batchId = (int) $request->input('batch_id', 0);
    $status = (string) $request->input('status', 'active');
    $search = trim((string) $request->input('search', ''));

    if ($batchId <= 0) {
      return redirect()->back()->with('error', 'Please select a batch before exporting student data.');
    }


    $studentTable = (new StudentMaster())->getTable();

    $data = AdmissionApplication::with([
      'studentmaster.batchmaster:id,batch_name',
      'studentmaster.activeSemesterConfig:id,student_id,semester_id,current_semester',
      'studentmaster.nationalitymaster:id,name',
      'studentmaster.religionmaster:id,name',
      'studentmaster.bloodgroup:id,name',
      'registrationmaster.countrymaster:id,name',
      'religionmaster:id,name',
      'bloodgroupmaster:id,name',
    ])
      ->whereHas('studentmaster', function ($q) use ($batchId, $status, $search, $studentTable) {
        $q->where('batch', $batchId)
          ->when(Schema::hasColumn($studentTable, 'is_deleted'), fn($inner) => $inner->where('is_deleted', 0))
          ->when($status === 'active' && Schema::hasColumn($studentTable, 'is_left'), function ($inner) {
            $inner->where(function ($w) {
              $w->whereNull('is_left')->orWhere('is_left', 0);
            });
          })
          ->when($status === 'left' && Schema::hasColumn($studentTable, 'is_left'), fn($inner) => $inner->where('is_left', 1))
          ->when($search !== '', function ($inner) use ($search) {
            $inner->where(function ($w) use ($search) {
              $w->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('roll_no', 'like', "%{$search}%")
                ->orWhere('register_no', 'like', "%{$search}%")
                ->orWhere('mobile_no', 'like', "%{$search}%");
            });
          });
      })
      ->where('payment_gateway_status', 'success')
      ->orderBy('id')
      ->get();

    return Excel::download(new CentralOfficeStudentTemplateExport($data), 'student-list.xlsx');
  }

  public function reactivate(int $id)
  {
    $this->ensureCentralOfficeAccess();

    $student = StudentMaster::findOrFail($id);
    $student->is_left = 0;
    $student->save();

    return redirect()->back()->with('success', 'Student reactivated successfully.');
  }

  public function exportEmployees(Request $request)
  {
    $this->ensureCentralOfficeAccess();
    $siliguriCampusId = $this->resolveSiliguriCampusId();

    $departmentId = (int) $request->input('department_id', 0);
    $status = strtolower(trim((string) $request->input('status', 'active')));
    if (!in_array($status, ['active', 'left', 'deleted', 'all'], true)) {
      $status = 'active';
    }
    $search = trim((string) $request->input('search', ''));

    $rows = $this->buildEmployeesQuery($departmentId, $status, $search, $siliguriCampusId)->get();
    $rows = $this->attachEmployeeAcademicContext($rows, $siliguriCampusId);
    $filename = 'central-office-employees-' . date('Y-m-d-His') . '.csv';

    return response()->streamDownload(function () use ($rows) {
      $handle = fopen('php://output', 'w');
      fputcsv($handle, ['Employee Code', 'Name', 'Email', 'Mobile', 'Department (Subjects)', 'Deanery', 'Designation', 'Employee Type', 'Status', 'Date of Joining']);

      foreach ($rows as $row) {
        $statusLabel = 'Active';
        if (!empty($row->deleted_at)) {
          $statusLabel = 'Deleted';
        } elseif ((int) ($row->IS_LEFT ?? 0) === 1) {
          $statusLabel = 'Left';
        }

        fputcsv($handle, [
          (string) ($row->USER_CODE ?? ''),
          trim((string) ($row->FIRST_NAME ?? '') . ' ' . (string) ($row->MIDDLE_NAME ?? '') . ' ' . (string) ($row->LAST_NAME ?? '')),
          (string) ($row->MAIL_ID ?? ''),
          (string) ($row->MOBILE_NO ?? ''),
          (string) collect($row->subject_departments ?? [])->implode(', '),
          (string) collect($row->deanery_names ?? [])->implode(', '),
          (string) ($row->designation ?? ''),
          (string) ($row->employee_type ?? ''),
          $statusLabel,
          (string) ($row->DOJ ?? ''),
        ]);
      }

      fclose($handle);
    }, $filename, [
      'Content-Type' => 'text/csv',
    ]);
  }

  public function destroyEmployee(int $id)
  {
    $this->ensureCentralOfficeAccess();
    $siliguriCampusId = $this->resolveSiliguriCampusId();

    $faculty = Faculty::withTrashed()->findOrFail($id);
    if ($faculty->trashed()) {
      return redirect()->back()->with('error', 'Faculty is already deleted.');
    }

    if ($siliguriCampusId > 0 && (int) ($faculty->CAMPUS_ID ?? 0) !== $siliguriCampusId) {
      return redirect()->back()->with('error', 'Only Siliguri campus employees can be managed from this panel.');
    }

    $blockers = $this->getFacultyDeleteBlockers((int) $faculty->id);
    if (!empty($blockers)) {
      $details = collect($blockers)
        ->map(fn($item) => ucfirst((string) $item['label']) . ': ' . (int) $item['count'])
        ->implode(', ');

      return redirect()->back()->with('error', 'Faculty cannot be deleted due to active links (' . $details . '). Please unlink/clear mappings first.');
    }

    DB::transaction(function () use ($faculty) {
      SubjectFacultyMaster::where('faculty_id', $faculty->id)->delete();
      $faculty->delete();
    });

    return redirect()->back()->with('success', 'Faculty deleted successfully.');
  }

  public function admissionsBatchWise(Request $request)
  {
    $this->ensureCentralOfficeAccess();

    $batch = trim((string) $request->input('batch', ''));

    $admissionByBatch = $this->buildAdmissionsBatchSummaryQuery($batch)->get();

    $batchOptions = DB::table('admission_registrations')
      ->whereNotNull('batch')
      ->where('batch', '!=', '')
      ->distinct()
      ->orderByDesc('batch')
      ->pluck('batch');

    return view('central-office.admissions.batch-wise', [
      'admissionByBatch' => $admissionByBatch,
      'batchOptions' => $batchOptions,
      'batch' => $batch,
    ]);
  }

  public function exportAdmissionsBatchWise(Request $request)
  {
    $this->ensureCentralOfficeAccess();

    $batch = trim((string) $request->input('batch', ''));
    $rows = $this->buildAdmissionsBatchSummaryQuery($batch)->get();
    $filename = 'central-office-admissions-batch-wise-' . date('Y-m-d-His') . '.csv';

    return response()->streamDownload(function () use ($rows) {
      $handle = fopen('php://output', 'w');
      fputcsv($handle, ['Batch', 'Total Registrations', 'UG Registrations', 'PG Registrations', 'Applications Submitted', 'Phase 1', 'Phase 2', 'Enrolled']);

      foreach ($rows as $row) {
        fputcsv($handle, [
          $row->batch_name,
          (int) $row->total_registrations,
          (int) $row->ug_registrations,
          (int) $row->pg_registrations,
          (int) $row->submitted_applications,
          (int) $row->phase1_count,
          (int) $row->phase2_count,
          (int) $row->enrolled_count,
        ]);
      }

      fclose($handle);
    }, $filename, [
      'Content-Type' => 'text/csv',
    ]);
  }
}
