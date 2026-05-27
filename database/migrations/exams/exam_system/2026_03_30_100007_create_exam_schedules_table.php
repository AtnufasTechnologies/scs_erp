<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams');
      $table->foreignId('exam_subject_id')->constrained('exam_subject_masters');
      $table->date('exam_date');
      $table->time('start_time');
      $table->time('end_time');
      $table->unsignedBigInteger('room_id')->nullable(); // FK to be added after rooms table
      $table->timestamps();
      $table->unique(['exam_id', 'exam_subject_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exam_schedules');
  }
};
