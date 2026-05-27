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
      $table->unsignedBigInteger('annual_session_id')->nullable()->after('faculty_id');
      $table->foreign('annual_session_id')->references('id')->on('annual_sessions')->onDelete('set null');
      $table->index(['annual_session_id', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('faculty_leave_applications', function (Blueprint $table) {
      $table->dropForeign(['annual_session_id']);
      $table->dropIndex(['annual_session_id', 'status']);
      $table->dropColumn('annual_session_id');
    });
  }
};
