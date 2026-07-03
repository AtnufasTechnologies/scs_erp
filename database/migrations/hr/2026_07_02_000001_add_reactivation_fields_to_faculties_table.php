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
    Schema::table('faculties', function (Blueprint $table) {
      if (!Schema::hasColumn('faculties', 'reactivation_date')) {
        $table->date('reactivation_date')->nullable()->after('DOL');
      }

      if (!Schema::hasColumn('faculties', 'hr_remark')) {
        $table->text('hr_remark')->nullable()->after('reactivation_date');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('faculties', function (Blueprint $table) {
      $columnsToDrop = [];

      if (Schema::hasColumn('faculties', 'hr_remark')) {
        $columnsToDrop[] = 'hr_remark';
      }

      if (Schema::hasColumn('faculties', 'reactivation_date')) {
        $columnsToDrop[] = 'reactivation_date';
      }

      if (!empty($columnsToDrop)) {
        $table->dropColumn($columnsToDrop);
      }
    });
  }
};
