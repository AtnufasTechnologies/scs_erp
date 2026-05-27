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
    Schema::create('hr_pay_matrix', function (Blueprint $table) {
      $table->id();

      // Matrix identification
      $table->string('matrix_code')->unique();
      $table->string('matrix_name');
      $table->string('designation'); // Professor, Associate Professor, Assistant Professor, etc.
      $table->string('grade_level'); // Level-1, Level-2, etc.
      $table->integer('pay_band')->nullable(); // Pay band value
      $table->integer('grade_pay')->nullable(); // Grade pay value

      // Employment type
      $table->enum('employment_type', ['permanent', 'contractual', 'adhoc', 'guest', 'visiting'])->default('permanent');

      // Salary Components - Earnings
      $table->decimal('basic_salary', 10, 2)->default(0);
      $table->decimal('da_percentage', 5, 2)->default(0)->comment('Dearness Allowance %');
      $table->decimal('da_fixed', 10, 2)->default(0)->comment('Fixed DA amount');
      $table->decimal('hra_percentage', 5, 2)->default(0)->comment('House Rent Allowance %');
      $table->decimal('hra_fixed', 10, 2)->default(0)->comment('Fixed HRA amount');
      $table->decimal('ta', 10, 2)->default(0)->comment('Transport Allowance');
      $table->decimal('medical_allowance', 10, 2)->default(0);
      $table->decimal('special_allowance', 10, 2)->default(0);
      $table->decimal('other_allowances', 10, 2)->default(0);

      // Salary Components - Deductions
      $table->decimal('pf_percentage', 5, 2)->default(0)->comment('Provident Fund %');
      $table->decimal('pf_fixed', 10, 2)->default(0)->comment('Fixed PF amount');
      $table->decimal('esi_percentage', 5, 2)->default(0)->comment('ESI %');
      $table->decimal('esi_fixed', 10, 2)->default(0)->comment('Fixed ESI amount');
      $table->decimal('professional_tax', 10, 2)->default(0);
      $table->decimal('tds_percentage', 5, 2)->default(0)->comment('TDS %');
      $table->decimal('other_deductions', 10, 2)->default(0);

      // Increments and benefits
      $table->decimal('annual_increment_percentage', 5, 2)->default(0);
      $table->integer('increment_month')->default(7)->comment('Month of annual increment (1-12)');

      // Working days
      $table->integer('default_working_days')->default(26);

      // Status and effective dates
      $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
      $table->date('effective_from')->nullable();
      $table->date('effective_to')->nullable();

      // Metadata
      $table->text('description')->nullable();
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();

      $table->timestamps();
      $table->softDeletes();

      // Indexes
      $table->index('designation');
      $table->index('grade_level');
      $table->index('status');
      $table->index('employment_type');
      $table->index(['effective_from', 'effective_to']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hr_pay_matrix');
  }
};
