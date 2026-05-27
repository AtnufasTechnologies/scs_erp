<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('seating_allocations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_schedule_id')->constrained('exam_schedules');
      $table->foreignId('room_id')->constrained('rooms');
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->string('seat_no');
      $table->timestamps();
      $table->unique(['exam_schedule_id', 'room_id', 'seat_no'], 'seating_alloc_unique');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('seating_allocations');
  }
};
