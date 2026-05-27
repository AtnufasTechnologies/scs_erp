<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('subject_course_offerings', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('semester_id');
      $table->unsignedBigInteger('course_type_id');  // FK → subject_type_masters
      $table->unsignedInteger('intake_capacity');
      $table->boolean('is_registration_open')->default(false);
      $table->timestamp('registration_opens_at')->nullable();
      $table->timestamp('registration_closes_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
      $table->foreign('batch_id')->references('id')->on('batch_masters')->onDelete('cascade');
      $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
      $table->foreign('course_type_id')->references('id')->on('subject_type_masters')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('subject_course_offerings');
    Schema::enableForeignKeyConstraints();
  }
};
