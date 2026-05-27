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
    Schema::table('faculties', function (Blueprint $table) {
      $table->string('employee_type')->nullable()->after('IS_LEFT'); // permanent, temporary, contract, visiting
      $table->string('designation')->nullable()->after('employee_type');
      $table->string('qualification')->nullable()->after('designation');
      $table->string('specialization')->nullable()->after('qualification');
      $table->integer('experience_years')->default(0)->after('specialization');
      $table->string('pan_number')->nullable()->after('experience_years');
      $table->string('aadhar_number')->nullable()->after('pan_number');
      $table->string('bank_account_number')->nullable()->after('aadhar_number');
      $table->string('bank_ifsc_code')->nullable()->after('bank_account_number');
      $table->string('bank_name')->nullable()->after('bank_ifsc_code');
      $table->string('emergency_contact_name')->nullable()->after('bank_name');
      $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');
      $table->text('permanent_address')->nullable()->after('emergency_contact_number');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('faculties', function (Blueprint $table) {
      $table->dropColumn([
        'employee_type',
        'designation',
        'qualification',
        'specialization',
        'experience_years',
        'pan_number',
        'aadhar_number',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_name',
        'emergency_contact_name',
        'emergency_contact_number',
        'permanent_address',
      ]);
    });
  }
};
