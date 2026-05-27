<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('research_components', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->foreignId('exam_id')->constrained('exams');
      $table->string('title');
      $table->string('supervisor')->nullable();
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->unique(['exam_student_id', 'exam_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('research_components');
  }
};
