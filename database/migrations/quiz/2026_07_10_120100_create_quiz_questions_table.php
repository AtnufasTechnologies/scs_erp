<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('quiz_questions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quiz_id');
      $table->text('question_text');
      $table->unsignedInteger('position')->default(1);
      $table->timestamps();

      $table->index(['quiz_id', 'position']);
    });

    Schema::create('quiz_question_options', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quiz_question_id');
      $table->string('option_text');
      $table->boolean('is_correct')->default(false);
      $table->unsignedInteger('position')->default(1);
      $table->timestamps();

      $table->index(['quiz_question_id', 'position']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('quiz_question_options');
    Schema::dropIfExists('quiz_questions');
  }
};
