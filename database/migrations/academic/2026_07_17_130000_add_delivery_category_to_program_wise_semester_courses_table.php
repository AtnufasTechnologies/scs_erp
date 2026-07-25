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
    Schema::table('program_wise_semester_courses', function (Blueprint $table) {
      if (!Schema::hasColumn('program_wise_semester_courses', 'delivery_category')) {
        $table->string('delivery_category', 30)->nullable()->after('course_type');
        $table->index('delivery_category', 'pwsc_delivery_category_idx');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('program_wise_semester_courses', function (Blueprint $table) {
      if (Schema::hasColumn('program_wise_semester_courses', 'delivery_category')) {
        $table->dropIndex('pwsc_delivery_category_idx');
        $table->dropColumn('delivery_category');
      }
    });
  }
};
