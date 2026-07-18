<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (Schema::hasTable('program_wise_semester_courses') && !Schema::hasTable('curriculam_engine')) {
      Schema::rename('program_wise_semester_courses', 'curriculam_engine');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('curriculam_engine') && !Schema::hasTable('program_wise_semester_courses')) {
      Schema::rename('curriculam_engine', 'program_wise_semester_courses');
    }
  }
};
