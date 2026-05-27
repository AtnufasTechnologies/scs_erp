<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('revaluations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('marks_id')->constrained('marks');
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->string('status')->default('pending');
      $table->text('remarks')->nullable();
      $table->timestamps();
      $table->unique(['marks_id', 'exam_student_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('revaluations');
  }
};
