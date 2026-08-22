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
    Schema::table('api_metrix_categories', function (Blueprint $table) {
      if (!Schema::hasColumn('api_metrix_categories', 'show_in_workdiary')) {
        $table->boolean('show_in_workdiary')->default(1)->after('status');
        $table->index('show_in_workdiary');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('api_metrix_categories', function (Blueprint $table) {
      if (Schema::hasColumn('api_metrix_categories', 'show_in_workdiary')) {
        $table->dropIndex(['show_in_workdiary']);
        $table->dropColumn('show_in_workdiary');
      }
    });
  }
};
