<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('quiz_attempts', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quiz_id');
      $table->unsignedBigInteger('student_id');
      $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');
      $table->unsignedInteger('raw_score')->default(0);
      $table->unsignedInteger('total_questions')->default(0);
      $table->decimal('score', 8, 2)->default(0);
      $table->dateTime('started_at')->nullable();
      $table->dateTime('submitted_at')->nullable();
      $table->timestamps();

      $table->unique(['quiz_id', 'student_id']);
      $table->index(['student_id', 'status']);
    });

    Schema::create('quiz_attempt_answers', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quiz_attempt_id');
      $table->unsignedBigInteger('quiz_question_id');
      $table->unsignedBigInteger('quiz_question_option_id')->nullable();
      $table->boolean('is_correct')->default(false);
      $table->timestamps();

      $table->unique(['quiz_attempt_id', 'quiz_question_id'], 'quiz_attempt_question_unique');
      $table->index('quiz_question_option_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('quiz_attempt_answers');
    Schema::dropIfExists('quiz_attempts');
  }
};
