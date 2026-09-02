<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('student_course_rosters', function (Blueprint $table) {
      if (!Schema::hasColumn('student_course_rosters', 'subject_id')) {
        $table->unsignedBigInteger('subject_id')->nullable()->after('course_id');
      }
      if (!Schema::hasColumn('student_course_rosters', 'syllabus_id')) {
        $table->unsignedBigInteger('syllabus_id')->nullable()->after('subject_id');
      }
      if (!Schema::hasColumn('student_course_rosters', 'batch_id')) {
        $table->unsignedBigInteger('batch_id')->nullable()->after('syllabus_id');
      }
      if (!Schema::hasColumn('student_course_rosters', 'semester_id')) {
        $table->unsignedBigInteger('semester_id')->nullable()->after('batch_id');
      }
      if (!Schema::hasColumn('student_course_rosters', 'program_type')) {
        $table->string('program_type', 20)->nullable()->after('semester_id');
      }
    });

    $databaseName = DB::getDatabaseName();
    $existingIndexes = DB::table('information_schema.statistics')
      ->where('table_schema', $databaseName)
      ->where('table_name', 'student_course_rosters')
      ->pluck('index_name')
      ->all();

    Schema::table('student_course_rosters', function (Blueprint $table) use ($existingIndexes) {
      if (!in_array('scr_course_ta_syllabus_idx', $existingIndexes, true)) {
        $table->index(['course_id', 'ta_id', 'syllabus_id'], 'scr_course_ta_syllabus_idx');
      }

      if (!in_array('scr_course_ta_batch_sem_prog_idx', $existingIndexes, true)) {
        $table->index(['course_id', 'ta_id', 'batch_id', 'semester_id', 'program_type'], 'scr_course_ta_batch_sem_prog_idx');
      }
    });

    if (
      !Schema::hasTable('subject_has_routines')
      || !Schema::hasTable('subject_has_syllabi')
      || !Schema::hasColumn('student_course_rosters', 'routine_id')
    ) {
      return;
    }

    // Primary backfill: rows linked to routines can be mapped deterministically to syllabus context.
    DB::statement(
      'UPDATE student_course_rosters scr '
        . 'INNER JOIN subject_has_routines shr ON shr.id = scr.routine_id '
        . 'INNER JOIN subject_has_syllabi shs ON shs.id = shr.syllabus_id '
        . 'SET scr.syllabus_id = shs.id, '
        . 'scr.subject_id = shs.subject_id, '
        . 'scr.batch_id = shs.batch_id, '
        . 'scr.semester_id = shs.semester_id, '
        . 'scr.program_type = shs.program_type '
        . 'WHERE scr.routine_id IS NOT NULL '
        . 'AND (scr.syllabus_id IS NULL OR scr.subject_id IS NULL OR scr.batch_id IS NULL OR scr.semester_id IS NULL OR scr.program_type IS NULL)'
    );

    $hasTeachingAllocationColumn = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    // Secondary backfill: for legacy rows without routine_id, populate context only when a unique syllabus can be inferred.
    DB::table('student_course_rosters')
      ->whereNull('syllabus_id')
      ->orderBy('id')
      ->chunkById(500, function ($rows) use ($hasTeachingAllocationColumn): void {
        foreach ($rows as $row) {
          $candidateQuery = DB::table('subject_has_routines as shr')
            ->join('subject_has_syllabi as shs', 'shs.id', '=', 'shr.syllabus_id')
            ->where('shs.course_id', (int) $row->course_id)
            ->where(function ($assignmentMatch) use ($row, $hasTeachingAllocationColumn) {
              $assignmentMatch->where('shr.teaching_assignment_id', (int) $row->ta_id);
              if ($hasTeachingAllocationColumn) {
                $assignmentMatch->orWhere('shr.teaching_allocation_id', (int) $row->ta_id);
              }
            })
            ->select('shs.id', 'shs.subject_id', 'shs.batch_id', 'shs.semester_id', 'shs.program_type')
            ->distinct();

          $candidates = $candidateQuery->get();
          if ($candidates->count() !== 1) {
            continue;
          }

          $candidate = $candidates->first();
          DB::table('student_course_rosters')
            ->where('id', (int) $row->id)
            ->update([
              'syllabus_id' => (int) ($candidate->id ?? 0),
              'subject_id' => (int) ($candidate->subject_id ?? 0),
              'batch_id' => (int) ($candidate->batch_id ?? 0),
              'semester_id' => (int) ($candidate->semester_id ?? 0),
              'program_type' => (string) ($candidate->program_type ?? ''),
              'updated_at' => now(),
            ]);
        }
      });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    $databaseName = DB::getDatabaseName();
    $existingIndexes = DB::table('information_schema.statistics')
      ->where('table_schema', $databaseName)
      ->where('table_name', 'student_course_rosters')
      ->pluck('index_name')
      ->all();

    Schema::table('student_course_rosters', function (Blueprint $table) use ($existingIndexes) {
      if (in_array('scr_course_ta_syllabus_idx', $existingIndexes, true)) {
        $table->dropIndex('scr_course_ta_syllabus_idx');
      }

      if (in_array('scr_course_ta_batch_sem_prog_idx', $existingIndexes, true)) {
        $table->dropIndex('scr_course_ta_batch_sem_prog_idx');
      }

      foreach (['program_type', 'semester_id', 'batch_id', 'syllabus_id', 'subject_id'] as $column) {
        if (Schema::hasColumn('student_course_rosters', $column)) {
          $table->dropColumn($column);
        }
      }
    });
  }
};
