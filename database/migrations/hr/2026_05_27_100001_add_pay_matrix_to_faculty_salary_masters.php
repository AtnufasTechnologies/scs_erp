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
    Schema::table('faculty_salary_masters', function (Blueprint $table) {
      // Add pay matrix reference
      $table->unsignedBigInteger('pay_matrix_id')->nullable()->after('faculty_id');

      // Add index for better query performance
      $table->index('pay_matrix_id');

      // Note: Not adding foreign key constraint to avoid issues with existing data
      // Referential integrity will be maintained at application level
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('faculty_salary_masters', function (Blueprint $table) {
      $table->dropIndex(['pay_matrix_id']);
      $table->dropColumn('pay_matrix_id');
    });
  }
};
