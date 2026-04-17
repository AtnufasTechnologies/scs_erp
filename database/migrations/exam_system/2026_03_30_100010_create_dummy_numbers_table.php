<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dummy_numbers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams');
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->string('dummy_number')->unique();
      $table->timestamps();
      $table->unique(['exam_id', 'exam_student_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('dummy_numbers');
  }
};
