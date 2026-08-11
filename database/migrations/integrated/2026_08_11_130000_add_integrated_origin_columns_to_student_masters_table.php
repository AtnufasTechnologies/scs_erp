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
    Schema::table('student_masters', function (Blueprint $table) {
      if (!Schema::hasColumn('student_masters', 'is_integrated_program_origin')) {
        $table->boolean('is_integrated_program_origin')->default(false)->after('new_program_id');
      }

      if (!Schema::hasColumn('student_masters', 'integrated_origin_program_id')) {
        $table->unsignedBigInteger('integrated_origin_program_id')->nullable()->after('is_integrated_program_origin');
      }

      if (!Schema::hasColumn('student_masters', 'integrated_shifted_at')) {
        $table->timestamp('integrated_shifted_at')->nullable()->after('integrated_origin_program_id');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('student_masters', function (Blueprint $table) {
      if (Schema::hasColumn('student_masters', 'integrated_shifted_at')) {
        $table->dropColumn('integrated_shifted_at');
      }

      if (Schema::hasColumn('student_masters', 'integrated_origin_program_id')) {
        $table->dropColumn('integrated_origin_program_id');
      }

      if (Schema::hasColumn('student_masters', 'is_integrated_program_origin')) {
        $table->dropColumn('is_integrated_program_origin');
      }
    });
  }
};
