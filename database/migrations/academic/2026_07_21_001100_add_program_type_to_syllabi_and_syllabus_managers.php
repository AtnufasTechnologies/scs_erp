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
    if (!Schema::hasColumn('subject_has_syllabi', 'program_type')) {
      Schema::table('subject_has_syllabi', function (Blueprint $table) {
        $table->char('program_type', 10)->default('UG')->after('semester_id')->comment('UG, PG');
        $table->index('program_type', 'subject_has_syllabi_program_type_idx');
      });
    }

    DB::table('subject_has_syllabi')
      ->whereNull('program_type')
      ->update(['program_type' => 'UG']);

    if (!Schema::hasColumn('syllabus_managers', 'program_type')) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->char('program_type', 10)->default('UG')->after('shift')->comment('UG, PG');
        $table->index('program_type', 'syllabus_managers_program_type_idx');
      });
    }

    DB::table('syllabus_managers')
      ->whereNull('program_type')
      ->update(['program_type' => 'UG']);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('syllabus_managers', 'program_type')) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->dropIndex('syllabus_managers_program_type_idx');
        $table->dropColumn('program_type');
      });
    }

    if (Schema::hasColumn('subject_has_syllabi', 'program_type')) {
      Schema::table('subject_has_syllabi', function (Blueprint $table) {
        $table->dropIndex('subject_has_syllabi_program_type_idx');
        $table->dropColumn('program_type');
      });
    }
  }
};
