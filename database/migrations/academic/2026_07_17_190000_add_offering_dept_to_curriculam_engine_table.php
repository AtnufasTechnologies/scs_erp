<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $tableName) {
      if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'offering_dept')) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) use ($tableName) {
        $table->unsignedBigInteger('offering_dept')->nullable()->after('course_id');
        $table->index('offering_dept', $tableName . '_offering_dept_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $tableName) {
      if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'offering_dept')) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) use ($tableName) {
        $table->dropIndex($tableName . '_offering_dept_idx');
        $table->dropColumn('offering_dept');
      });
    }
  }
};
