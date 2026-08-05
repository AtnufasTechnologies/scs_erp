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
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $tableName) {
      if (!Schema::hasTable($tableName)) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) use ($tableName) {
        if (!Schema::hasColumn($tableName, 'specialization_mode')) {
          $table->string('specialization_mode', 20)->default('COMMON')->after('delivery_category');
          $table->index('specialization_mode', $tableName . '_spec_mode_idx');
        }

        if (!Schema::hasColumn($tableName, 'specialization_master_id')) {
          $table->unsignedBigInteger('specialization_master_id')->nullable()->after('specialization_mode');
          $table->index('specialization_master_id', $tableName . '_spec_id_idx');
        }
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    $tables = ['curriculam_engine', 'program_wise_semester_courses'];

    foreach ($tables as $tableName) {
      if (!Schema::hasTable($tableName)) {
        continue;
      }

      Schema::table($tableName, function (Blueprint $table) use ($tableName) {
        if (Schema::hasColumn($tableName, 'specialization_master_id')) {
          $table->dropIndex($tableName . '_spec_id_idx');
          $table->dropColumn('specialization_master_id');
        }

        if (Schema::hasColumn($tableName, 'specialization_mode')) {
          $table->dropIndex($tableName . '_spec_mode_idx');
          $table->dropColumn('specialization_mode');
        }
      });
    }
  }
};
