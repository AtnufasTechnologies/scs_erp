<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasColumn('faculty_salary_slips', 'financial_year_id')) {
      Schema::table('faculty_salary_slips', function (Blueprint $table) {
        $table->unsignedBigInteger('financial_year_id')->nullable()->after('annual_session_id');
        $table->index('financial_year_id', 'faculty_salary_slips_financial_year_id_index');
      });
    }

    DB::table('faculty_salary_slips')
      ->select('id', 'year', 'month')
      ->orderBy('id')
      ->chunkById(500, function ($rows) {
        foreach ($rows as $row) {
          $periodDate = sprintf('%04d-%02d-01', (int) $row->year, (int) $row->month);

          $financialYearId = DB::table('financial_year_masters')
            ->whereDate('start_date', '<=', $periodDate)
            ->whereDate('end_date', '>=', $periodDate)
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->value('id');

          if ($financialYearId) {
            DB::table('faculty_salary_slips')
              ->where('id', $row->id)
              ->update(['financial_year_id' => $financialYearId]);
          }
        }
      });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('faculty_salary_slips', 'financial_year_id')) {
      Schema::table('faculty_salary_slips', function (Blueprint $table) {
        $table->dropIndex('faculty_salary_slips_financial_year_id_index');
        $table->dropColumn('financial_year_id');
      });
    }
  }
};
