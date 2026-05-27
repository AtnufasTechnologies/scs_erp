<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('question_bank', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('syllabus_subunit_id');
      $table->integer('batch_id')->nullable();
      $table->integer('semester_id')->nullable();
      $table->integer('subject_id')->nullable();
      $table->integer('course_id')->nullable();          // program_course_masters.id (co_id)
      $table->unsignedBigInteger('cognitive_level_master_id')->nullable();
      $table->unsignedBigInteger('user_id');             // faculty user_id
      $table->text('question_text');
      $table->enum('question_type', ['MCQ', 'Short Answer', 'Long Answer', 'True/False', 'Fill in the Blank'])->default('Short Answer');
      $table->unsignedTinyInteger('marks')->default(1);
      $table->enum('difficulty', ['Easy', 'Medium', 'Hard'])->default('Medium');
      $table->timestamps();
      $table->softDeletes();

      $table->index('syllabus_subunit_id');
      $table->index('batch_id');
      $table->index('semester_id');
      $table->index('subject_id');
      $table->index('course_id');
      $table->index('user_id');
      $table->index('cognitive_level_master_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('question_bank');
  }
};
