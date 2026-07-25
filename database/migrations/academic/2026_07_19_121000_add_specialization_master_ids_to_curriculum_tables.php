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
        if (!Schema::hasColumn($tableName, 'specialization_master_ids')) {
          $table->json('specialization_master_ids')->nullable()->after('specialization_master_id');
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
      if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'specialization_master_ids')) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) {
        $table->dropColumn('specialization_master_ids');
      });
    }
  }
};
