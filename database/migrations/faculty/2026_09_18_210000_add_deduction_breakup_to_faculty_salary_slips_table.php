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
    Schema::table('faculty_salary_slips', function (Blueprint $table) {
      if (!Schema::hasColumn('faculty_salary_slips', 'late_attendance_deduction')) {
        $table->decimal('late_attendance_deduction', 10, 2)->default(0)->after('loan_deduction');
      }
      if (!Schema::hasColumn('faculty_salary_slips', 'leave_deduction_amount')) {
        $table->decimal('leave_deduction_amount', 10, 2)->default(0)->after('late_attendance_deduction');
      }
      if (!Schema::hasColumn('faculty_salary_slips', 'manual_other_deduction')) {
        $table->decimal('manual_other_deduction', 10, 2)->default(0)->after('leave_deduction_amount');
      }
      if (!Schema::hasColumn('faculty_salary_slips', 'emi_deduction_count')) {
        $table->unsignedInteger('emi_deduction_count')->default(0)->after('manual_other_deduction');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('faculty_salary_slips', function (Blueprint $table) {
      if (Schema::hasColumn('faculty_salary_slips', 'emi_deduction_count')) {
        $table->dropColumn('emi_deduction_count');
      }
      if (Schema::hasColumn('faculty_salary_slips', 'manual_other_deduction')) {
        $table->dropColumn('manual_other_deduction');
      }
      if (Schema::hasColumn('faculty_salary_slips', 'leave_deduction_amount')) {
        $table->dropColumn('leave_deduction_amount');
      }
      if (Schema::hasColumn('faculty_salary_slips', 'late_attendance_deduction')) {
        $table->dropColumn('late_attendance_deduction');
      }
    });
  }
};
