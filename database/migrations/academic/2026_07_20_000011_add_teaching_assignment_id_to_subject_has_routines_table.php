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
    Schema::table('subject_has_routines', function (Blueprint $table) {
      $table->unsignedBigInteger('teaching_assignment_id')->nullable()->after('subject_course_id');
      $table->index('teaching_assignment_id', 'subject_has_routines_teaching_assignment_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('subject_has_routines', function (Blueprint $table) {
      $table->dropIndex('subject_has_routines_teaching_assignment_idx');
      $table->dropColumn('teaching_assignment_id');
    });
  }
};
