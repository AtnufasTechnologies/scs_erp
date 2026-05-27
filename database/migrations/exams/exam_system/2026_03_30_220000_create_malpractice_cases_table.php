<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('malpractice_cases', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_id');
      $table->unsignedBigInteger('student_id');
      $table->unsignedBigInteger('subject_id')->nullable();
      $table->unsignedBigInteger('room_id')->nullable();
      $table->string('remarks')->nullable();
      $table->enum('status', ['pending', 'reviewed', 'cleared', 'blocked'])->default('pending');
      $table->unsignedBigInteger('reported_by')->nullable();
      $table->timestamp('reported_at')->nullable();
      $table->timestamps();
      $table->index(['exam_id', 'student_id', 'subject_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('malpractice_cases');
  }
};
