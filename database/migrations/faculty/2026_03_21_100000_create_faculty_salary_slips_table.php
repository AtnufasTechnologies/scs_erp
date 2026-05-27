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
    Schema::create('faculty_salary_slips', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('faculty_id');
      $table->unsignedBigInteger('annual_session_id')->nullable();
      $table->string('month', 2); // 01-12
      $table->string('year', 4); // 2024
      $table->string('salary_slip_number')->unique();

      // Earnings
      $table->decimal('basic_salary', 10, 2)->default(0);
      $table->decimal('da', 10, 2)->default(0); // Dearness Allowance
      $table->decimal('hra', 10, 2)->default(0); // House Rent Allowance
      $table->decimal('ta', 10, 2)->default(0); // Transport Allowance
      $table->decimal('medical_allowance', 10, 2)->default(0);
      $table->decimal('special_allowance', 10, 2)->default(0);
      $table->decimal('other_allowances', 10, 2)->default(0);

      // Deductions
      $table->decimal('pf', 10, 2)->default(0); // Provident Fund
      $table->decimal('esi', 10, 2)->default(0); // Employee State Insurance
      $table->decimal('professional_tax', 10, 2)->default(0);
      $table->decimal('tds', 10, 2)->default(0); // Tax Deducted at Source
      $table->decimal('loan_deduction', 10, 2)->default(0);
      $table->decimal('other_deductions', 10, 2)->default(0);

      // Calculated fields
      $table->decimal('gross_salary', 10, 2)->default(0);
      $table->decimal('total_deductions', 10, 2)->default(0);
      $table->decimal('net_salary', 10, 2)->default(0);

      // Attendance
      $table->integer('working_days')->default(0);
      $table->integer('present_days')->default(0);
      $table->integer('leave_days')->default(0);

      // Payment information
      $table->date('payment_date')->nullable();
      $table->string('payment_mode')->nullable(); // bank_transfer, cash, cheque
      $table->string('payment_reference')->nullable();

      // Status and approval
      $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();

      // Additional fields
      $table->text('remarks')->nullable();

      $table->timestamps();
      $table->softDeletes();

      // Indexes
      $table->index(['faculty_id', 'year', 'month']);
      $table->index('status');
      $table->index('payment_date');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('faculty_salary_slips');
  }
};
