<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('quizzes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id');
      $table->unsignedBigInteger('course_id');
      $table->unsignedBigInteger('syllabus_id')->nullable();
      $table->unsignedBigInteger('batch_id')->nullable();
      $table->unsignedBigInteger('semester_id')->nullable();
      $table->unsignedBigInteger('faculty_id')->nullable();
      $table->unsignedBigInteger('sup_cia_component_id');
      $table->unsignedBigInteger('cia_group_id');
      $table->string('title');
      $table->decimal('total_marks', 8, 2);
      $table->dateTime('open_at');
      $table->dateTime('close_at')->nullable();
      $table->boolean('is_published')->default(true);
      $table->unsignedBigInteger('created_by');
      $table->timestamps();

      $table->index(['subject_id', 'course_id']);
      $table->index(['open_at', 'close_at']);
      $table->index('cia_group_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('quizzes');
  }
};
