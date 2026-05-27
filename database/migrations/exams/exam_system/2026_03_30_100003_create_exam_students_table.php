<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_students', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id')->unique();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('enrollment_no')->unique();
      $table->string('status')->default('active');
      $table->timestamps();
      $table->index('erp_student_id');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exam_students');
  }
};
