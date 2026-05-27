<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('faculty_leave_applications', function (Blueprint $table) {
      $table->string('forwarded_to')->nullable()->after('admin_remarks'); // DeanOfStudentStudies, DCOE, HR
      $table->unsignedBigInteger('forwarded_by')->nullable()->after('forwarded_to');
      $table->timestamp('forwarded_at')->nullable()->after('forwarded_by');
      $table->text('forwarded_remarks')->nullable()->after('forwarded_at');
      $table->string('dept_action')->nullable()->after('forwarded_remarks'); // rejected, forwarded
      $table->unsignedBigInteger('dept_action_by')->nullable()->after('dept_action');
      $table->timestamp('dept_action_at')->nullable()->after('dept_action_by');
    });
  }

  public function down(): void
  {
    Schema::table('faculty_leave_applications', function (Blueprint $table) {
      $table->dropColumn([
        'forwarded_to',
        'forwarded_by',
        'forwarded_at',
        'forwarded_remarks',
        'dept_action',
        'dept_action_by',
        'dept_action_at',
      ]);
    });
  }
};
