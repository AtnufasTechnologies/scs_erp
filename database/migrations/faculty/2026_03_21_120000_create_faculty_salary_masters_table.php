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
    Schema::create('faculty_salary_masters', function (Blueprint $table) {
      $table->id();
      $table->integer('faculty_id');
      $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');

      // Salary Structure - Earnings
      $table->decimal('basic_salary', 10, 2)->default(0);
      $table->decimal('da', 10, 2)->default(0);
      $table->decimal('hra', 10, 2)->default(0);
      $table->decimal('ta', 10, 2)->default(0);
      $table->decimal('medical_allowance', 10, 2)->default(0);
      $table->decimal('special_allowance', 10, 2)->default(0);
      $table->decimal('other_allowances', 10, 2)->default(0);

      // Salary Structure - Deductions
      $table->decimal('pf', 10, 2)->default(0);
      $table->decimal('esi', 10, 2)->default(0);
      $table->decimal('professional_tax', 10, 2)->default(0);
      $table->decimal('tds', 10, 2)->default(0);
      $table->decimal('other_deductions', 10, 2)->default(0);

      // Attendance defaults
      $table->integer('working_days')->default(26);

      // Status
      $table->enum('status', ['active', 'inactive'])->default('active');

      // Effective dates
      $table->date('effective_from')->nullable();
      $table->date('effective_to')->nullable();

      $table->text('remarks')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('faculty_salary_masters');
    Schema::enableForeignKeyConstraints();
  }
};
