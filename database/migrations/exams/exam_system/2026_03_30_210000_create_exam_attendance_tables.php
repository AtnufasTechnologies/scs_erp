<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_attendances', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_id');
      $table->unsignedBigInteger('student_id');
      $table->unsignedBigInteger('subject_id');
      $table->unsignedBigInteger('room_id')->nullable();
      $table->string('seat_no')->nullable();
      $table->string('dummy_no')->nullable();
      $table->enum('status', ['present', 'absent', 'malpractice']);
      $table->unsignedBigInteger('marked_by')->nullable(); // faculty_id
      $table->timestamp('marked_at')->nullable();
      $table->string('remarks')->nullable();
      $table->timestamps();

      $table->index('exam_id');
      $table->index('student_id');
      $table->index('room_id');
    });

    Schema::create('attendance_sessions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_id');
      $table->unsignedBigInteger('room_id');
      $table->unsignedBigInteger('faculty_id');
      $table->enum('session', ['morning', 'evening']);
      $table->date('date');
      $table->enum('status', ['open', 'closed'])->default('open');
      $table->timestamps();
    });

    Schema::create('attendance_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('attendance_id');
      $table->enum('action', ['marked', 'updated']);
      $table->unsignedBigInteger('performed_by');
      $table->timestamp('timestamp');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('attendance_logs');
    Schema::dropIfExists('attendance_sessions');
    Schema::dropIfExists('exam_attendances');
  }
};
