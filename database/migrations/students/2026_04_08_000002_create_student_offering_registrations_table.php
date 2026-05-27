<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('student_offering_registrations', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('offering_id');
      $table->unsignedBigInteger('student_id');
      $table->unsignedInteger('queue_position');   // FIFO position (1-based)
      $table->enum('status', ['confirmed', 'waitlisted', 'cancelled'])->default('confirmed');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('offering_id')->references('id')->on('subject_course_offerings')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->unique(['offering_id', 'student_id'], 'sor_offering_student_unique');
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('student_offering_registrations');
    Schema::enableForeignKeyConstraints();
  }
};
