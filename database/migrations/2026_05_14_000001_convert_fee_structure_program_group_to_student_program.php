<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   * 
   * This migration converts the std_program_id column in fee_structure_has_many_programs
   * from program_group_id to actual student_program_id to fix fee matching issues.
   */
  public function up(): void
  {
    // Convert existing program_group_ids to student_program_ids
    $records = DB::table('fee_structure_has_many_programs')
      ->whereNull('deleted_at')
      ->get();

    foreach ($records as $record) {
      // The std_program_id currently contains program_group_id
      $programGroupId = $record->std_program_id;

      // Get the actual student_program_id from program_group table
      $programGroup = DB::table('program_group')
        ->where('id', $programGroupId)
        ->first();

      if ($programGroup && $programGroup->program_id) {
        // Update to use the actual student_program_id
        DB::table('fee_structure_has_many_programs')
          ->where('id', $record->id)
          ->update([
            'std_program_id' => $programGroup->program_id,
            'updated_at' => now()
          ]);

        echo "Updated record {$record->id}: program_group_id {$programGroupId} -> student_program_id {$programGroup->program_id}\n";
      } else {
        echo "Warning: Could not find program_group with id {$programGroupId} for record {$record->id}\n";
      }
    }

    echo "Migration completed: Converted program_group_ids to student_program_ids\n";
  }

  /**
   * Reverse the migrations.
   * 
   * Note: This reverse migration attempts to convert back, but may not be 100% accurate
   * if multiple program_groups reference the same student_program
   */
  public function down(): void
  {
    // Attempt to reverse - convert student_program_ids back to program_group_ids
    $records = DB::table('fee_structure_has_many_programs')
      ->whereNull('deleted_at')
      ->get();

    foreach ($records as $record) {
      // The std_program_id now contains student_program_id
      $studentProgramId = $record->std_program_id;

      // Get the first program_group that references this student_program
      $programGroup = DB::table('program_group')
        ->where('program_id', $studentProgramId)
        ->first();

      if ($programGroup) {
        // Update back to program_group_id
        DB::table('fee_structure_has_many_programs')
          ->where('id', $record->id)
          ->update([
            'std_program_id' => $programGroup->id,
            'updated_at' => now()
          ]);

        echo "Reverted record {$record->id}: student_program_id {$studentProgramId} -> program_group_id {$programGroup->id}\n";
      } else {
        echo "Warning: Could not find program_group for student_program {$studentProgramId} for record {$record->id}\n";
      }
    }

    echo "Rollback completed: Converted student_program_ids back to program_group_ids\n";
  }
};
