<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\FacultyProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacultySyncService
{
  /**
   * Fetch faculty data from ERP and sync to faculty_profiles table.
   *
   * @return array [synced, created, updated]
   */
  public function syncFromErp()
  {
    // Example: Replace with actual ERP API endpoint and authentication
    $erpApiUrl = config('services.erp.faculty_endpoint');
    $apiToken = config('services.erp.api_token');

    $response = Http::withToken($apiToken)->get($erpApiUrl);
    if (!$response->ok()) {
      Log::error('ERP Faculty Sync failed', ['status' => $response->status(), 'body' => $response->body()]);
      throw new \Exception('Failed to fetch faculty data from ERP');
    }
    $facultyList = $response->json();
    $synced = $created = $updated = 0;
    foreach ($facultyList as $faculty) {
      $profile = FacultyProfile::updateOrCreate(
        ['erp_faculty_id' => $faculty['erp_faculty_id']],
        [
          'department' => $faculty['department'] ?? null,
          'designation' => $faculty['designation'] ?? null,
        ]
      );
      $synced++;
      if ($profile->wasRecentlyCreated) {
        $created++;
      } else {
        $updated++;
      }
    }
    return compact('synced', 'created', 'updated');
  }
}
