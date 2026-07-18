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
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $table) {
      if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'course_type')) {
        continue;
      }

      // Keep legacy values while ensuring current app values are accepted.
      DB::statement("ALTER TABLE `{$table}` MODIFY `course_type` ENUM('AUTO','STUDENT_CHOICE','DEPARTMENT_CHOICE','COMPULSORY','ELECTIVE','OPTIONAL') NOT NULL");
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $table) {
      if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'course_type')) {
        continue;
      }

      DB::statement("ALTER TABLE `{$table}` MODIFY `course_type` ENUM('AUTO','STUDENT_CHOICE','DEPARTMENT_CHOICE') NOT NULL");
    }
  }
};
