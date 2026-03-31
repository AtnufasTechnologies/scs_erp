<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('student_credits', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->foreignId('exam_subject_id')->constrained('exam_subject_masters');
      $table->integer('credits_earned');
      $table->timestamps();
      $table->unique(['exam_student_id', 'exam_subject_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('student_credits');
  }
};
