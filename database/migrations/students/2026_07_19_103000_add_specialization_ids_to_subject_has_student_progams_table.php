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
    Schema::table('subject_has_student_progams', function (Blueprint $table) {
      $table->json('specialization_ids')->nullable()->after('total_available_seats');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('subject_has_student_progams', function (Blueprint $table) {
      $table->dropColumn('specialization_ids');
    });
  }
};
