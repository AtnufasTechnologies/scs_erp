<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('internal_mark_logs', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('internal_mark_id');
      $table->integer('student_id');
      $table->integer('course_id');
      $table->string('semester', 45);
      $table->string('old_mark', 45)->nullable();
      $table->string('new_mark', 45);
      $table->integer('changed_by');
      $table->string('changed_by_name', 100)->nullable();
      $table->string('change_reason', 255)->nullable();
      $table->timestamps();

      $table->foreign('internal_mark_id')->references('id')->on('internal_marks')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->foreign('course_id')->references('id')->on('program_course_masters')->onDelete('cascade');
      $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');

      $table->index(['internal_mark_id']);
      $table->index(['student_id', 'course_id']);
      $table->index(['changed_by']);
      $table->index(['created_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('internal_mark_logs');
  }
};
