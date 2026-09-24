<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_operational_sessions', function (Blueprint $table) {
      $table->id();
      $table->date('exam_date');
      $table->time('start_time');
      $table->time('end_time')->nullable();
      $table->string('session_label', 120)->nullable();
      $table->string('status', 20)->default('active');
      $table->timestamps();

      $table->unique(['exam_date', 'start_time'], 'exam_operational_unique_slot');
    });

    Schema::create('exam_room_generated_seats', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_operational_session_id')->constrained('exam_operational_sessions')->cascadeOnDelete();
      $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
      $table->unsignedInteger('row_no');
      $table->unsignedInteger('column_no');
      $table->unsignedInteger('seat_number');
      $table->string('seat_code', 40);
      $table->boolean('is_allocated')->default(false);
      $table->timestamps();

      $table->unique(['exam_operational_session_id', 'room_id', 'seat_number'], 'exam_generated_seat_number_unique');
      $table->unique(['exam_operational_session_id', 'room_id', 'seat_code'], 'exam_generated_seat_code_unique');
      $table->index(['exam_operational_session_id', 'room_id'], 'exam_generated_seat_session_room_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_room_generated_seats');
    Schema::dropIfExists('exam_operational_sessions');
  }
};
