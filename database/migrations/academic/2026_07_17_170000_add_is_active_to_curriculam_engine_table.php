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
    if (Schema::hasTable('curriculam_engine') && !Schema::hasColumn('curriculam_engine', 'is_active')) {
      Schema::table('curriculam_engine', function (Blueprint $table) {
        $table->boolean('is_active')->default(true)->after('display_order');
      });
    }

    // Backward compatibility for environments that still use old table name.
    if (Schema::hasTable('program_wise_semester_courses') && !Schema::hasColumn('program_wise_semester_courses', 'is_active')) {
      Schema::table('program_wise_semester_courses', function (Blueprint $table) {
        $table->boolean('is_active')->default(true)->after('display_order');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('curriculam_engine') && Schema::hasColumn('curriculam_engine', 'is_active')) {
      Schema::table('curriculam_engine', function (Blueprint $table) {
        $table->dropColumn('is_active');
      });
    }

    if (Schema::hasTable('program_wise_semester_courses') && Schema::hasColumn('program_wise_semester_courses', 'is_active')) {
      Schema::table('program_wise_semester_courses', function (Blueprint $table) {
        $table->dropColumn('is_active');
      });
    }
  }
};
