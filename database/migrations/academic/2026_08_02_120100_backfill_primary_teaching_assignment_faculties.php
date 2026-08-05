<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasTable('teaching_assignments') || !Schema::hasTable('teaching_assignment_faculties')) {
      return;
    }

    $now = now();

    DB::table('teaching_assignments')
      ->select(['id', 'faculty_id'])
      ->whereNotNull('faculty_id')
      ->orderBy('id')
      ->chunkById(300, function ($assignments) use ($now) {
        foreach ($assignments as $assignment) {
          $assignmentId = (int) ($assignment->id ?? 0);
          $primaryFacultyId = (int) ($assignment->faculty_id ?? 0);

          if ($assignmentId <= 0 || $primaryFacultyId <= 0) {
            continue;
          }

          $hasPrimary = DB::table('teaching_assignment_faculties')
            ->where('teaching_assignment_id', $assignmentId)
            ->where('teaching_role', 'Primary')
            ->exists();

          if ($hasPrimary) {
            continue;
          }

          $existingPrimaryFacultyRow = DB::table('teaching_assignment_faculties')
            ->where('teaching_assignment_id', $assignmentId)
            ->where('faculty_id', $primaryFacultyId)
            ->first();

          if ($existingPrimaryFacultyRow) {
            DB::table('teaching_assignment_faculties')
              ->where('id', (int) $existingPrimaryFacultyRow->id)
              ->update([
                'teaching_role' => 'Primary',
                'updated_at' => $now,
              ]);

            continue;
          }

          DB::table('teaching_assignment_faculties')->insert([
            'teaching_assignment_id' => $assignmentId,
            'faculty_id' => $primaryFacultyId,
            'teaching_role' => 'Primary',
            'created_at' => $now,
            'updated_at' => $now,
          ]);
        }
      }, 'id');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Intentionally left blank.
    // This data backfill is idempotent and cannot be safely reversed
    // without risking deletion of legitimate role assignments.
  }
};
