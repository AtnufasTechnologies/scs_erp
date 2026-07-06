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
    Schema::table('student_program', function (Blueprint $table) {
      $table->string('shift', 20)->default('common')->after('campus_id');
      $table->index('shift', 'student_program_shift_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('student_program', function (Blueprint $table) {
      $table->dropIndex('student_program_shift_idx');
      $table->dropColumn('shift');
    });
  }
};
