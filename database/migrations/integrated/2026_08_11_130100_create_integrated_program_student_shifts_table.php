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
    Schema::create('integrated_program_student_shifts', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('from_program_id');
      $table->unsignedBigInteger('to_program_id');
      $table->unsignedBigInteger('from_combination_id')->nullable();
      $table->unsignedBigInteger('to_combination_id');
      $table->unsignedBigInteger('origin_program_id')->nullable();
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('shifted_by')->nullable();
      $table->timestamps();

      $table->index(['student_id', 'batch_id'], 'integrated_shift_student_batch_idx');
      $table->index(['from_program_id', 'to_program_id'], 'integrated_shift_program_pair_idx');
      $table->index('to_combination_id', 'integrated_shift_to_combo_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('integrated_program_student_shifts');
  }
};
