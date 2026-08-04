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
    Schema::create('student_campus_transfer_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id');
      $table->string('roll_no')->nullable();
      $table->unsignedBigInteger('from_campus_id');
      $table->unsignedBigInteger('to_campus_id');
      $table->unsignedBigInteger('from_program_id')->nullable();
      $table->unsignedBigInteger('to_program_id')->nullable();
      $table->unsignedBigInteger('from_department_id')->nullable();
      $table->unsignedBigInteger('to_department_id')->nullable();
      $table->unsignedBigInteger('changed_by')->nullable();
      $table->string('reason', 500)->nullable();
      $table->json('old_snapshot')->nullable();
      $table->json('new_snapshot')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->index('student_id');
      $table->index('roll_no');
      $table->index('from_campus_id');
      $table->index('to_campus_id');
      $table->index('created_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('student_campus_transfer_logs');
  }
};
