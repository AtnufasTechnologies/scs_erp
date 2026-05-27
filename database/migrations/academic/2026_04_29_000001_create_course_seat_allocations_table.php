<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('course_seat_allocations', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('semester_id');
      $table->unsignedInteger('course_master_id');
      $table->unsignedInteger('total_seats');
      $table->boolean('is_open')->default(false);
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
      $table->foreign('batch_id')->references('id')->on('batch_masters')->onDelete('cascade');
      $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
      // Note: program_course_masters uses a different collation, so no DB-level FK; enforced at app level.

      $table->unique(['subject_id', 'batch_id', 'semester_id', 'course_master_id'], 'uniq_seat_alloc');
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('course_seat_allocations');
    Schema::enableForeignKeyConstraints();
  }
};
