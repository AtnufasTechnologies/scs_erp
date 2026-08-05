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
    if (!Schema::hasTable('hr_pay_matrix') || !Schema::hasColumn('hr_pay_matrix', 'pay_band')) {
      return;
    }

    $driver = DB::getDriverName();

    if (in_array($driver, ['mysql', 'mariadb'], true)) {
      DB::statement('ALTER TABLE `hr_pay_matrix` MODIFY `pay_band` VARCHAR(100) NULL');
      return;
    }

    if ($driver === 'pgsql') {
      DB::statement('ALTER TABLE hr_pay_matrix ALTER COLUMN pay_band TYPE VARCHAR(100)');
      DB::statement('ALTER TABLE hr_pay_matrix ALTER COLUMN pay_band DROP NOT NULL');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('hr_pay_matrix') || !Schema::hasColumn('hr_pay_matrix', 'pay_band')) {
      return;
    }

    $driver = DB::getDriverName();

    if (in_array($driver, ['mysql', 'mariadb'], true)) {
      DB::statement('ALTER TABLE `hr_pay_matrix` MODIFY `pay_band` INT NULL');
      return;
    }

    if ($driver === 'pgsql') {
      DB::statement('ALTER TABLE hr_pay_matrix ALTER COLUMN pay_band TYPE INTEGER USING NULLIF(pay_band, \'\')::INTEGER');
    }
  }
};
