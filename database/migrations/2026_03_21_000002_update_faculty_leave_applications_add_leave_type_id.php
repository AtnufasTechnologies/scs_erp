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
    Schema::table('faculty_leave_applications', function (Blueprint $table) {
      // Add new column for leave_type_id
      $table->unsignedBigInteger('leave_type_id')->nullable()->after('faculty_id');

      // Keep the old leave_type column temporarily for data migration
      // It will be removed after data is migrated
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('faculty_leave_applications', function (Blueprint $table) {
      $table->dropColumn('leave_type_id');
    });
  }
};
