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
    if (!Schema::hasTable('fees_structures') || !Schema::hasColumn('fees_structures', 'degree_track_id')) {
      return;
    }

    if (!Schema::hasTable('degree_track_masters')) {
      return;
    }

    $regularId = (int) DB::table('degree_track_masters')
      ->whereRaw('LOWER(name) = ?', ['regular'])
      ->value('id');

    if ($regularId <= 0) {
      throw new RuntimeException('Degree Track "Regular" was not found in degree_track_masters.');
    }

    // Set DB-level default for future inserts.
    DB::statement("ALTER TABLE `fees_structures` MODIFY `degree_track_id` BIGINT UNSIGNED NULL DEFAULT {$regularId}");

    // Backfill rows to Regular where it does not violate the unique composite key.
    $rows = DB::table('fees_structures')
      ->select([
        'id',
        'batch_id',
        'program_id',
        'course_name',
        'academic_pathway_id',
        'std_current_year',
        'yearly_pay_order',
        'degree_track_id',
      ])
      ->orderBy('id')
      ->get();

    foreach ($rows as $row) {
      if ((int) $row->degree_track_id === $regularId) {
        continue;
      }

      $hasConflict = DB::table('fees_structures')
        ->where('id', '!=', $row->id)
        ->where('batch_id', $row->batch_id)
        ->where('program_id', $row->program_id)
        ->where('course_name', $row->course_name)
        ->where('academic_pathway_id', $row->academic_pathway_id)
        ->where('std_current_year', $row->std_current_year)
        ->where('yearly_pay_order', $row->yearly_pay_order)
        ->where('degree_track_id', $regularId)
        ->exists();

      if (!$hasConflict) {
        DB::table('fees_structures')
          ->where('id', $row->id)
          ->update(['degree_track_id' => $regularId]);
      }
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('fees_structures') || !Schema::hasColumn('fees_structures', 'degree_track_id')) {
      return;
    }

    // Keep existing data as-is; only remove the DB-level default.
    DB::statement('ALTER TABLE `fees_structures` MODIFY `degree_track_id` BIGINT UNSIGNED NULL DEFAULT NULL');
  }
};
