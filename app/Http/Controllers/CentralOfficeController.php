<?php

namespace App\Http\Controllers;

use App\Models\AdmissionRegistration;
use App\Models\BatchMaster;
use App\Models\StudentMaster;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CentralOfficeController extends Controller
{
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

  public function dashboard()
  {
    $this->ensureCentralOfficeAccess();

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

    $recentAdmissions = AdmissionRegistration::query()
      ->latest('id')
      ->limit(8)
      ->get(['id', 'first_name', 'last_name', 'application_type', 'batch', 'created_at']);

    return view('central-office.dashboard', [
      'activeStudents' => $activeStudents,
      'leftStudents' => $leftStudents,
      'totalBatches' => $totalBatches,
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

    $rows = $this->buildStudentsQuery($batchId, $status, $search)->get();
    $filename = 'central-office-students-' . date('Y-m-d-His') . '.csv';

    return response()->streamDownload(function () use ($rows) {
      $handle = fopen('php://output', 'w');
      fputcsv($handle, ['Roll No', 'Register No', 'Name', 'Batch', 'Program', 'Mobile', 'Email', 'Status']);

      foreach ($rows as $student) {
        $program = $student->stdprogramenrolled?->code
          ? ($student->stdprogramenrolled->code . ' - ' . $student->stdprogramenrolled->name)
          : 'N/A';

        fputcsv($handle, [
          $student->roll_no ?: 'N/A',
          $student->register_no ?: 'N/A',
          trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))),
          $student->batchmaster?->batch_name ?? 'N/A',
          $program,
          $student->mobile_no ?: 'N/A',
          $student->mail_id ?: 'N/A',
          ((int) ($student->is_left ?? 0) === 1) ? 'Left' : 'Active',
        ]);
      }

      fclose($handle);
    }, $filename, [
      'Content-Type' => 'text/csv',
    ]);
  }

  public function reactivate(int $id)
  {
    $this->ensureCentralOfficeAccess();

    $student = StudentMaster::findOrFail($id);
    $student->is_left = 0;
    $student->save();

    return redirect()->back()->with('success', 'Student reactivated successfully.');
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
