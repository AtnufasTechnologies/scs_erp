<?php

namespace App\Http\Controllers;

use App\Models\InternationalOfficeActivityMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InternationalOfficeActivityMasterController extends Controller
{
  public function index()
  {
    $activities = InternationalOfficeActivityMaster::orderByDesc('activity_date')
      ->orderByDesc('id')
      ->get();

    return view('admin.international-office.activity-master', [
      'activities' => $activities,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $this->validatePayload($request);

    $mouCopyPath = null;
    if ($request->hasFile('mou_copy')) {
      $mouCopyPath = $request->file('mou_copy')->store('international-office/mou-copies', 'public');
    }

    $reportPath = null;
    if ($request->hasFile('report_file')) {
      $reportPath = $request->file('report_file')->store('international-office/reports', 'public');
    }

    $photoPaths = [];
    if ($request->hasFile('geotagged_photos')) {
      foreach ($request->file('geotagged_photos') as $photoFile) {
        $photoPaths[] = $photoFile->store('international-office/geotagged-photos', 'public');
      }
    }

    InternationalOfficeActivityMaster::create([
      'activity_title' => $validated['activity_title'],
      'institution_name' => $validated['institution_name'],
      'has_mou' => (int) ($validated['has_mou'] ?? 0) === 1,
      'mou_signing_date' => $validated['mou_signing_date'] ?? null,
      'mou_copy_path' => $mouCopyPath,
      'activity_type' => $validated['activity_type'],
      'participant_type' => $validated['participant_type'],
      'department_scope' => $validated['department_scope'],
      'department_details' => $validated['department_details'] ?? null,
      'approval_status' => $validated['approval_status'] ?? null,
      'activity_date' => $validated['activity_date'],
      'report_path' => $reportPath,
      'geotagged_photo_paths' => !empty($photoPaths) ? $photoPaths : null,
      'finance_grant_kind' => $validated['finance_grant_kind'] ?? null,
      'finance_count' => $validated['finance_count'] ?? null,
      'remarks' => $validated['remarks'] ?? null,
      'is_active' => (int) ($validated['is_active'] ?? 1) === 1,
    ]);

    return redirect()->back()->with('success', 'International Office activity added successfully.');
  }

  public function update(Request $request, $id)
  {
    $activity = InternationalOfficeActivityMaster::findOrFail($id);
    $validated = $this->validatePayload($request);

    $mouCopyPath = $activity->mou_copy_path;
    if ($request->hasFile('mou_copy')) {
      if ($mouCopyPath) {
        Storage::disk('public')->delete($mouCopyPath);
      }
      $mouCopyPath = $request->file('mou_copy')->store('international-office/mou-copies', 'public');
    }

    $reportPath = $activity->report_path;
    if ($request->hasFile('report_file')) {
      if ($reportPath) {
        Storage::disk('public')->delete($reportPath);
      }
      $reportPath = $request->file('report_file')->store('international-office/reports', 'public');
    }

    $photoPaths = is_array($activity->geotagged_photo_paths) ? $activity->geotagged_photo_paths : [];
    if ($request->hasFile('geotagged_photos')) {
      foreach ($request->file('geotagged_photos') as $photoFile) {
        $photoPaths[] = $photoFile->store('international-office/geotagged-photos', 'public');
      }
    }

    $activity->update([
      'activity_title' => $validated['activity_title'],
      'institution_name' => $validated['institution_name'],
      'has_mou' => (int) ($validated['has_mou'] ?? 0) === 1,
      'mou_signing_date' => $validated['mou_signing_date'] ?? null,
      'mou_copy_path' => $mouCopyPath,
      'activity_type' => $validated['activity_type'],
      'participant_type' => $validated['participant_type'],
      'department_scope' => $validated['department_scope'],
      'department_details' => $validated['department_details'] ?? null,
      'approval_status' => $validated['approval_status'] ?? null,
      'activity_date' => $validated['activity_date'],
      'report_path' => $reportPath,
      'geotagged_photo_paths' => !empty($photoPaths) ? $photoPaths : null,
      'finance_grant_kind' => $validated['finance_grant_kind'] ?? null,
      'finance_count' => $validated['finance_count'] ?? null,
      'remarks' => $validated['remarks'] ?? null,
      'is_active' => (int) ($validated['is_active'] ?? 1) === 1,
    ]);

    return redirect()->back()->with('success', 'International Office activity updated successfully.');
  }

  public function destroy($id)
  {
    $activity = InternationalOfficeActivityMaster::findOrFail($id);

    if ($activity->mou_copy_path) {
      Storage::disk('public')->delete($activity->mou_copy_path);
    }

    if ($activity->report_path) {
      Storage::disk('public')->delete($activity->report_path);
    }

    $photoPaths = is_array($activity->geotagged_photo_paths) ? $activity->geotagged_photo_paths : [];
    foreach ($photoPaths as $photoPath) {
      if ($photoPath) {
        Storage::disk('public')->delete($photoPath);
      }
    }

    $activity->delete();

    return redirect()->back()->with('success', 'International Office activity deleted successfully.');
  }

  private function validatePayload(Request $request): array
  {
    return $request->validate([
      'activity_title' => 'required|string|max:255',
      'institution_name' => 'required|string|max:255',
      'has_mou' => 'nullable|in:0,1',
      'mou_signing_date' => 'nullable|date|required_if:has_mou,1',
      'mou_copy' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
      'activity_type' => 'required|string|max:150',
      'participant_type' => 'required|in:student_only,faculty_only,both',
      'department_scope' => 'required|in:one,multiple',
      'department_details' => 'nullable|string|max:255',
      'approval_status' => 'nullable|string|max:50',
      'activity_date' => 'required|date',
      'report_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
      'geotagged_photos' => 'nullable|array',
      'geotagged_photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
      'finance_grant_kind' => 'nullable|string|max:255',
      'finance_count' => 'nullable|integer|min:0',
      'remarks' => 'nullable|string|max:1000',
      'is_active' => 'nullable|in:0,1',
    ]);
  }
}
