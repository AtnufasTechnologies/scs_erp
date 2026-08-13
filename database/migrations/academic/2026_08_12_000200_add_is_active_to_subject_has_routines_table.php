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
    if (!Schema::hasColumn('subject_has_routines', 'is_active')) {
      Schema::table('subject_has_routines', function (Blueprint $table) {
        $table->boolean('is_active')->default(1)->after('teaching_assignment_id');
        $table->index(['weekday_id', 'hour_id', 'shift', 'is_active'], 'subject_routine_slot_active_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('subject_has_routines', 'is_active')) {
      Schema::table('subject_has_routines', function (Blueprint $table) {
        $table->dropIndex('subject_routine_slot_active_idx');
        $table->dropColumn('is_active');
      });
    }
  }
};
