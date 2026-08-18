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
    $databaseName = DB::getDatabaseName();
    $hasRosterIndex = DB::table('information_schema.statistics')
      ->where('table_schema', $databaseName)
      ->where('table_name', 'student_course_rosters')
      ->where('index_name', 'scr_ta_course_routine_idx')
      ->exists();

    Schema::table('student_course_rosters', function (Blueprint $table) use ($hasRosterIndex) {
      if (!Schema::hasColumn('student_course_rosters', 'routine_id')) {
        $table->unsignedBigInteger('routine_id')->nullable()->after('course_id');
      }

      if (!$hasRosterIndex) {
        $table->index(['ta_id', 'course_id', 'routine_id'], 'scr_ta_course_routine_idx');
      }
    });

    $hasTeachingAssignmentId = Schema::hasColumn('subject_has_routines', 'teaching_assignment_id');
    $hasTeachingAllocationId = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    if (
      !Schema::hasTable('student_course_rosters')
      || !Schema::hasTable('subject_has_routines')
      || !Schema::hasTable('subject_has_syllabi')
      || !Schema::hasTable('student_masters')
    ) {
      return;
    }

    $programTypeNameById = [];
    if (Schema::hasTable('student_program_type_masters')) {
      $programTypeNameById = DB::table('student_program_type_masters')
        ->pluck('name', 'id')
        ->mapWithKeys(function ($name, $id) {
          return [(int) $id => strtoupper(trim((string) $name))];
        })
        ->all();
    }

    $normalize = function ($value) use ($programTypeNameById): string {
      if ($value === null) {
        return '';
      }

      $stringValue = strtoupper(trim((string) $value));
      if ($stringValue === '') {
        return '';
      }

      if (str_starts_with($stringValue, 'UG')) {
        return 'UG';
      }

      if (str_starts_with($stringValue, 'PG')) {
        return 'PG';
      }

      if (ctype_digit($stringValue)) {
        $resolved = strtoupper(trim((string) ($programTypeNameById[(int) $stringValue] ?? '')));
        if (str_starts_with($resolved, 'UG')) {
          return 'UG';
        }
        if (str_starts_with($resolved, 'PG')) {
          return 'PG';
        }
        return $resolved;
      }

      return $stringValue;
    };

    DB::table('student_course_rosters')
      ->select(['id', 'ta_id', 'course_id', 'student_id'])
      ->whereNull('routine_id')
      ->orderBy('id')
      ->chunkById(500, function ($rows) use ($normalize, $hasTeachingAssignmentId, $hasTeachingAllocationId): void {
        foreach ($rows as $row) {
          if (!$hasTeachingAssignmentId && !$hasTeachingAllocationId) {
            continue;
          }

          $candidateRoutines = DB::table('subject_has_routines as shr')
            ->join('subject_has_syllabi as shs', 'shs.id', '=', 'shr.syllabus_id')
            ->where('shs.course_id', (int) $row->course_id)
            ->whereNull('shr.deleted_at')
            ->where(function ($query) use ($row, $hasTeachingAssignmentId, $hasTeachingAllocationId) {
              if ($hasTeachingAssignmentId) {
                $query->where('shr.teaching_assignment_id', (int) $row->ta_id);
              }
              if ($hasTeachingAllocationId) {
                if ($hasTeachingAssignmentId) {
                  $query->orWhere('shr.teaching_allocation_id', (int) $row->ta_id);
                } else {
                  $query->where('shr.teaching_allocation_id', (int) $row->ta_id);
                }
              }
            })
            ->orderBy('shr.id')
            ->get([
              'shr.id',
              'shr.program_type',
              'shs.program_type as syllabus_program_type',
            ]);

          if ($candidateRoutines->isEmpty()) {
            continue;
          }

          $resolvedRoutineId = 0;
          if ($candidateRoutines->count() === 1) {
            $resolvedRoutineId = (int) ($candidateRoutines->first()->id ?? 0);
          } else {
            $studentProgramTypeRaw = DB::table('student_masters as sm')
              ->leftJoin('student_program as sp', 'sp.id', '=', 'sm.new_program_id')
              ->where('sm.id', (int) $row->student_id)
              ->value('sp.program_type');

            $studentProgramType = $normalize($studentProgramTypeRaw);

            if ($studentProgramType !== '') {
              $filtered = $candidateRoutines->filter(function ($candidate) use ($normalize, $studentProgramType) {
                $candidateProgram = $normalize($candidate->program_type ?? $candidate->syllabus_program_type ?? null);
                return $candidateProgram === $studentProgramType;
              })->values();

              if ($filtered->count() === 1) {
                $resolvedRoutineId = (int) ($filtered->first()->id ?? 0);
              }
            }
          }

          if ($resolvedRoutineId > 0) {
            DB::table('student_course_rosters')
              ->where('id', (int) $row->id)
              ->update(['routine_id' => $resolvedRoutineId]);
          }
        }
      });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    $databaseName = DB::getDatabaseName();
    $hasRosterIndex = DB::table('information_schema.statistics')
      ->where('table_schema', $databaseName)
      ->where('table_name', 'student_course_rosters')
      ->where('index_name', 'scr_ta_course_routine_idx')
      ->exists();

    Schema::table('student_course_rosters', function (Blueprint $table) use ($hasRosterIndex) {
      if (Schema::hasColumn('student_course_rosters', 'routine_id')) {
        if ($hasRosterIndex) {
          $table->dropIndex('scr_ta_course_routine_idx');
        }
        $table->dropColumn('routine_id');
      }
    });
  }
};
