<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasColumn('subject_has_routines', 'program_type')) {
      Schema::table('subject_has_routines', function (Blueprint $table) {
        $table->char('program_type', 10)->nullable()->after('shift')->comment('UG, PG');
        $table->index(['batch_id', 'shift', 'program_type'], 'subject_has_routines_batch_shift_program_idx');
      });
    }

    if (Schema::hasColumn('subject_has_syllabi', 'program_type')) {
      Schema::table('subject_has_syllabi', function (Blueprint $table) {
        $table->dropColumn('program_type');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('subject_has_routines', 'program_type')) {
      Schema::table('subject_has_routines', function (Blueprint $table) {
        $table->dropIndex('subject_has_routines_batch_shift_program_idx');
        $table->dropColumn('program_type');
      });
    }

    if (!Schema::hasColumn('subject_has_syllabi', 'program_type')) {
      Schema::table('subject_has_syllabi', function (Blueprint $table) {
        $table->char('program_type', 10)->nullable()->after('semester_id')->comment('UG, PG');
      });
    }
  }
};
