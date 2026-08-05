<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];
    $normalizedExpr = "REPLACE(REPLACE(REPLACE(UPPER(TRIM(delivery_category)), ' ', ''), '-', ''), '_', '')";

    foreach ($tables as $table) {
      if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'delivery_category')) {
        continue;
      }

      DB::table($table)
        ->whereRaw("{$normalizedExpr} IN ('COREA', 'MAJORCOMBO1', 'COMBO1')")
        ->update(['delivery_category' => 'COMBO1']);

      DB::table($table)
        ->whereRaw("{$normalizedExpr} IN ('COREB', 'MAJORCOMBO2', 'COMBO2')")
        ->update(['delivery_category' => 'COMBO2']);
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];
    $normalizedExpr = "REPLACE(REPLACE(REPLACE(UPPER(TRIM(delivery_category)), ' ', ''), '-', ''), '_', '')";

    foreach ($tables as $table) {
      if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'delivery_category')) {
        continue;
      }

      DB::table($table)
        ->whereRaw("{$normalizedExpr} = 'COMBO1'")
        ->update(['delivery_category' => 'CORE-A']);

      DB::table($table)
        ->whereRaw("{$normalizedExpr} = 'COMBO2'")
        ->update(['delivery_category' => 'CORE-B']);
    }
  }
};
