<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('teaching_practice', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->foreignId('exam_id')->constrained('exams');
      $table->string('school_name');
      $table->integer('duration'); // in days
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->unique(['exam_student_id', 'exam_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('teaching_practice');
  }
};
