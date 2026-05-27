<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_seating_arrangements', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->string('room_no');
      $table->string('seat_no');
      $table->unsignedBigInteger('erp_student_id');
      $table->timestamps();
      $table->index(['exam_session_id', 'room_no', 'seat_no'], 'exam_seating_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_seating_arrangements');
  }
};
