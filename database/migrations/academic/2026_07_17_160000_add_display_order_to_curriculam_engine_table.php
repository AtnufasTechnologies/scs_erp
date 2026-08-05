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
    if (Schema::hasTable('curriculam_engine') && !Schema::hasColumn('curriculam_engine', 'display_order')) {
      Schema::table('curriculam_engine', function (Blueprint $table) {
        $table->integer('display_order')->default(1)->after('delivery_category');
        $table->index(['semester', 'display_order'], 'ce_sem_display_order_idx');
      });
    }

    // Backward compatibility for environments that still use old table name.
    if (Schema::hasTable('program_wise_semester_courses') && !Schema::hasColumn('program_wise_semester_courses', 'display_order')) {
      Schema::table('program_wise_semester_courses', function (Blueprint $table) {
        $table->integer('display_order')->default(1)->after('delivery_category');
        $table->index(['semester', 'display_order'], 'pwsc_sem_display_order_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('curriculam_engine') && Schema::hasColumn('curriculam_engine', 'display_order')) {
      Schema::table('curriculam_engine', function (Blueprint $table) {
        $table->dropIndex('ce_sem_display_order_idx');
        $table->dropColumn('display_order');
      });
    }

    if (Schema::hasTable('program_wise_semester_courses') && Schema::hasColumn('program_wise_semester_courses', 'display_order')) {
      Schema::table('program_wise_semester_courses', function (Blueprint $table) {
        $table->dropIndex('pwsc_sem_display_order_idx');
        $table->dropColumn('display_order');
      });
    }
  }
};
