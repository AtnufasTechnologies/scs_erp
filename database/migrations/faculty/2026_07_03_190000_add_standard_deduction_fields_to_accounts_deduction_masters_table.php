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
    Schema::table('accounts_deduction_masters', function (Blueprint $table) {
      if (!Schema::hasColumn('accounts_deduction_masters', 'tds')) {
        $table->decimal('tds', 10, 2)->default(0)->after('percentage');
      }

      if (!Schema::hasColumn('accounts_deduction_masters', 'epf')) {
        $table->decimal('epf', 10, 2)->default(0)->after('tds');
      }

      if (!Schema::hasColumn('accounts_deduction_masters', 'pt')) {
        $table->decimal('pt', 10, 2)->default(0)->after('epf');
      }

      if (!Schema::hasColumn('accounts_deduction_masters', 'lwf')) {
        $table->decimal('lwf', 10, 2)->default(0)->after('pt');
      }

      if (!Schema::hasColumn('accounts_deduction_masters', 'esic')) {
        $table->decimal('esic', 10, 2)->default(0)->after('lwf');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('accounts_deduction_masters', function (Blueprint $table) {
      $columns = ['tds', 'epf', 'pt', 'lwf', 'esic'];
      $existingColumns = array_filter($columns, fn($column) => Schema::hasColumn('accounts_deduction_masters', $column));

      if (!empty($existingColumns)) {
        $table->dropColumn($existingColumns);
      }
    });
  }
};
