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
    Schema::create('teaching_group_items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id');
      $table->unsignedInteger('allocation_group_id');
      $table->unsignedBigInteger('curriculum_row_id')->nullable();
      $table->unsignedBigInteger('course_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('semester_id');
      $table->unsignedBigInteger('student_program_id')->nullable();
      $table->string('program_type', 20)->nullable();
      $table->string('delivery_type', 120)->nullable();
      $table->unsignedBigInteger('offering_dept_id')->nullable();
      $table->unsignedBigInteger('faculty_id')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['subject_id', 'allocation_group_id'], 'tgi_subject_group_idx');
      $table->index(['course_id', 'semester_id'], 'tgi_course_sem_idx');
      $table->index(['faculty_id'], 'tgi_faculty_idx');
      $table->unique(
        ['subject_id', 'allocation_group_id', 'course_id', 'batch_id', 'semester_id', 'student_program_id'],
        'tgi_unique_group_course'
      );
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('teaching_group_items');
  }
};
