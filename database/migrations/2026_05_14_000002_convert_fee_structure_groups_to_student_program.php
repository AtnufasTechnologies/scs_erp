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
   * This migration converts fee_structure_groups table to use student_program_id
   * instead of program_group_id
   */
  public function up(): void
  {
    // First, add the new column
    Schema::table('fee_structure_groups', function (Blueprint $table) {
      $table->integer('student_program_id')->nullable()->after('program_group_id');
    });

    // Convert existing data
    $records = DB::table('fee_structure_groups')
      ->whereNull('deleted_at')
      ->get();

    foreach ($records as $record) {
      $programGroupId = $record->program_group_id;

      // Get the actual student_program_id from program_group table
      $programGroup = DB::table('program_group')
        ->where('id', $programGroupId)
        ->first();

      if ($programGroup && $programGroup->program_id) {
        DB::table('fee_structure_groups')
          ->where('id', $record->id)
          ->update([
            'student_program_id' => $programGroup->program_id,
            'updated_at' => now()
          ]);

        echo "Updated fee_structure_groups record {$record->id}: program_group_id {$programGroupId} -> student_program_id {$programGroup->program_id}\n";
      } else {
        echo "Warning: Could not find program_group with id {$programGroupId} for fee_structure_groups record {$record->id}\n";
      }
    }

    // Make the new column non-nullable and drop the old one
    Schema::table('fee_structure_groups', function (Blueprint $table) {
      $table->integer('student_program_id')->nullable(false)->change();
      $table->dropColumn('program_group_id');
    });

    echo "Migration completed: fee_structure_groups now uses student_program_id\n";
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Add back the program_group_id column
    Schema::table('fee_structure_groups', function (Blueprint $table) {
      $table->integer('program_group_id')->nullable()->after('fee_course_master_id');
    });

    // Convert data back
    $records = DB::table('fee_structure_groups')
      ->whereNull('deleted_at')
      ->get();

    foreach ($records as $record) {
      $studentProgramId = $record->student_program_id;

      // Get the first program_group that references this student_program
      $programGroup = DB::table('program_group')
        ->where('program_id', $studentProgramId)
        ->first();

      if ($programGroup) {
        DB::table('fee_structure_groups')
          ->where('id', $record->id)
          ->update([
            'program_group_id' => $programGroup->id,
            'updated_at' => now()
          ]);

        echo "Reverted fee_structure_groups record {$record->id}: student_program_id {$studentProgramId} -> program_group_id {$programGroup->id}\n";
      } else {
        echo "Warning: Could not find program_group for student_program {$studentProgramId} for record {$record->id}\n";
      }
    }

    // Drop student_program_id and make program_group_id non-nullable
    Schema::table('fee_structure_groups', function (Blueprint $table) {
      $table->integer('program_group_id')->nullable(false)->change();
      $table->dropColumn('student_program_id');
    });

    echo "Rollback completed: fee_structure_groups reverted to program_group_id\n";
  }
};
