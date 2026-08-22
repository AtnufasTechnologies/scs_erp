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
    Schema::table('work_diaries', function (Blueprint $table) {
      $table->string('role_context', 50)->nullable()->after('faculty_id');
      $table->index(['faculty_id', 'role_context', 'date'], 'work_diaries_faculty_role_date_idx');
    });

    // Legacy entries were historically faculty-side.
    DB::table('work_diaries')
      ->whereNull('role_context')
      ->orWhere('role_context', '')
      ->update(['role_context' => 'faculty']);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('work_diaries', function (Blueprint $table) {
      $table->dropIndex('work_diaries_faculty_role_date_idx');
      $table->dropColumn('role_context');
    });
  }
};
