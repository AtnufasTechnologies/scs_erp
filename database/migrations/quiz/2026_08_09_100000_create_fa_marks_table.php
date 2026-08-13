<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('fa_marks', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id');
      $table->unsignedBigInteger('course_id');
      $table->unsignedBigInteger('batch_id')->nullable();
      $table->unsignedBigInteger('semester_id')->nullable();
      $table->unsignedBigInteger('component_id');
      $table->decimal('score', 8, 2)->default(0);
      $table->unsignedInteger('attempt')->default(1);
      $table->timestamps();

      $table->unique([
        'student_id',
        'course_id',
        'batch_id',
        'semester_id',
        'component_id',
        'attempt',
      ], 'fa_marks_student_course_batch_sem_comp_attempt_unique');

      $table->index(['student_id', 'course_id'], 'fa_marks_student_course_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('fa_marks');
  }
};
