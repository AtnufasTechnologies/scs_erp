<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('promotions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->foreignId('from_exam_id')->constrained('exams');
      $table->foreignId('to_exam_id')->constrained('exams');
      $table->timestamp('promoted_at')->nullable();
      $table->timestamps();
      $table->unique(['exam_student_id', 'from_exam_id', 'to_exam_id'], 'promotions_unique');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('promotions');
  }
};
