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
    Schema::table('co_has_csos', function (Blueprint $table) {
      $table->string('shift', 20)->default('common')->after('lectures_needed');
      $table->index(['co_id', 'shift'], 'co_has_csos_co_id_shift_idx');
    });

    Schema::table('subject_has_routines', function (Blueprint $table) {
      $table->string('shift', 20)->default('common')->after('batch_id');
      $table->index(['batch_id', 'shift'], 'subject_has_routines_batch_shift_idx');
    });

    Schema::table('syllabus_managers', function (Blueprint $table) {
      $table->string('shift', 20)->default('common')->after('semester_id');
      $table->index(['subject_id', 'batch_id', 'semester_id', 'shift'], 'syllabus_managers_subject_batch_sem_shift_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('syllabus_managers', function (Blueprint $table) {
      $table->dropIndex('syllabus_managers_subject_batch_sem_shift_idx');
      $table->dropColumn('shift');
    });

    Schema::table('subject_has_routines', function (Blueprint $table) {
      $table->dropIndex('subject_has_routines_batch_shift_idx');
      $table->dropColumn('shift');
    });

    Schema::table('co_has_csos', function (Blueprint $table) {
      $table->dropIndex('co_has_csos_co_id_shift_idx');
      $table->dropColumn('shift');
    });
  }
};
