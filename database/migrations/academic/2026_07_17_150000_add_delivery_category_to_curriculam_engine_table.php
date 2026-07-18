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
    if (Schema::hasTable('curriculam_engine') && !Schema::hasColumn('curriculam_engine', 'delivery_category')) {
      Schema::table('curriculam_engine', function (Blueprint $table) {
        $table->string('delivery_category', 30)->nullable()->after('course_type');
        $table->index('delivery_category', 'ce_delivery_category_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('curriculam_engine') && Schema::hasColumn('curriculam_engine', 'delivery_category')) {
      Schema::table('curriculam_engine', function (Blueprint $table) {
        $table->dropIndex('ce_delivery_category_idx');
        $table->dropColumn('delivery_category');
      });
    }
  }
};
