<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExamSystem\FacultySyncService;

class SyncFacultyProfiles extends Command
{
  protected $signature = 'faculty:sync';
  protected $description = 'Sync faculty profiles from ERP to faculty_profiles table';

  public function handle(FacultySyncService $syncService)
  {
    try {
      $result = $syncService->syncFromErp();
      $this->info("Faculty sync complete. Synced: {$result['synced']}, Created: {$result['created']}, Updated: {$result['updated']}");
    } catch (\Exception $e) {
      $this->error('Faculty sync failed: ' . $e->getMessage());
      return 1;
    }
    return 0;
  }
}
