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
    Schema::table('student_masters', function (Blueprint $table) {
      if (!Schema::hasColumn('student_masters', 'academic_dept_id')) {
        $table->unsignedBigInteger('academic_dept_id')->nullable()->after('department');
      }

      if (!Schema::hasColumn('student_masters', 'new_program_id')) {
        $table->unsignedBigInteger('new_program_id')->nullable()->after('programme');
      }

      if (!Schema::hasColumn('student_masters', 'academic_pathway_id')) {
        $table->unsignedBigInteger('academic_pathway_id')->nullable()->after('new_program_id');
      }

      if (!Schema::hasColumn('student_masters', 'degree_track_id')) {
        $table->unsignedBigInteger('degree_track_id')->nullable()->after('academic_pathway_id');
      }

      if (!Schema::hasColumn('student_masters', 'selected_combo_id')) {
        $table->unsignedBigInteger('selected_combo_id')->nullable()->after('degree_track_id');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('student_masters', function (Blueprint $table) {
      if (Schema::hasColumn('student_masters', 'selected_combo_id')) {
        $table->dropColumn('selected_combo_id');
      }

      if (Schema::hasColumn('student_masters', 'degree_track_id')) {
        $table->dropColumn('degree_track_id');
      }

      if (Schema::hasColumn('student_masters', 'academic_pathway_id')) {
        $table->dropColumn('academic_pathway_id');
      }

      if (Schema::hasColumn('student_masters', 'new_program_id')) {
        $table->dropColumn('new_program_id');
      }

      if (Schema::hasColumn('student_masters', 'academic_dept_id')) {
        $table->dropColumn('academic_dept_id');
      }
    });
  }
};
