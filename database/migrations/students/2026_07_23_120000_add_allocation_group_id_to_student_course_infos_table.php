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
    if (!Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      Schema::table('student_course_infos', function (Blueprint $table) {
        $table->unsignedInteger('allocation_group_id')->default(1)->after('semester');
        $table->index('allocation_group_id', 'student_course_infos_allocation_group_id_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('student_course_infos', 'allocation_group_id')) {
      Schema::table('student_course_infos', function (Blueprint $table) {
        $table->dropIndex('student_course_infos_allocation_group_id_idx');
        $table->dropColumn('allocation_group_id');
      });
    }
  }
};
