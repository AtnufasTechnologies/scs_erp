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
    Schema::table('subject_course_masters', function (Blueprint $table) {
      if (!Schema::hasColumn('subject_course_masters', 'co_cso_not_applicable')) {
        $table->boolean('co_cso_not_applicable')->default(false)->after('course_master_id');
      }

      if (!Schema::hasColumn('subject_course_masters', 'co_cso_not_applicable_note')) {
        $table->string('co_cso_not_applicable_note', 255)->nullable()->after('co_cso_not_applicable');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('subject_course_masters', function (Blueprint $table) {
      if (Schema::hasColumn('subject_course_masters', 'co_cso_not_applicable_note')) {
        $table->dropColumn('co_cso_not_applicable_note');
      }

      if (Schema::hasColumn('subject_course_masters', 'co_cso_not_applicable')) {
        $table->dropColumn('co_cso_not_applicable');
      }
    });
  }
};
