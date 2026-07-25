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
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $tableName) {
      if (!Schema::hasTable($tableName)) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) use ($tableName) {
        if (!Schema::hasColumn($tableName, 'academic_pathway_id')) {
          $table->unsignedBigInteger('academic_pathway_id')->nullable()->after('offering_dept');
          $table->index('academic_pathway_id', $tableName . '_apath_idx');
        }

        if (!Schema::hasColumn($tableName, 'degree_track_id')) {
          $table->unsignedBigInteger('degree_track_id')->nullable()->after('academic_pathway_id');
          $table->index('degree_track_id', $tableName . '_dtrack_idx');
        }
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
      if (!Schema::hasTable($tableName)) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) use ($tableName) {
        if (Schema::hasColumn($tableName, 'degree_track_id')) {
          $table->dropIndex($tableName . '_dtrack_idx');
          $table->dropColumn('degree_track_id');
        }

        if (Schema::hasColumn($tableName, 'academic_pathway_id')) {
          $table->dropIndex($tableName . '_apath_idx');
          $table->dropColumn('academic_pathway_id');
        }
      });
    }
  }
};
