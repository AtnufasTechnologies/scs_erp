<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('student_attendances', function (Blueprint $table) {
      $table->id();
      $table->foreignId('routine_id')->constrained('subject_has_routines')->onDelete('cascade');
      $table->unsignedBigInteger('student_id');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->date('attendance_date');
      $table->integer('course_id')->nullable();
      $table->integer('hour_id')->nullable();
      $table->integer('semester_id')->nullable();
      $table->string('batch')->nullable();
      $table->time('lecture_start_time')->nullable();
      $table->time('lecture_end_time')->nullable();
      $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
      $table->text('remarks')->nullable();
      $table->integer('faculty_id')->nullable();
      $table->string('qr_url')->nullable();
      $table->enum('attendance_method', ['manual', 'qr'])->default('manual');
      $table->timestamps();
      $table->softDeletes();

      // Create unique index to prevent duplicate attendance records
      $table->unique(['course_id', 'student_id', 'attendance_date', 'hour_id', 'faculty_id'], 'unique_attendance_record');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('student_attendances');
    Schema::enableForeignKeyConstraints();
  }
};
