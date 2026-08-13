<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('quizzes', function (Blueprint $table) {
      $table->boolean('shuffle_questions')->default(false)->after('close_at');
      $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
      $table->unsignedInteger('time_limit_minutes')->nullable()->after('shuffle_options');
    });

    Schema::table('quiz_questions', function (Blueprint $table) {
      $table->string('question_image')->nullable()->after('question_text');
    });

    Schema::table('quiz_question_options', function (Blueprint $table) {
      $table->string('option_image')->nullable()->after('option_text');
    });
  }

  public function down(): void
  {
    Schema::table('quiz_question_options', function (Blueprint $table) {
      $table->dropColumn('option_image');
    });

    Schema::table('quiz_questions', function (Blueprint $table) {
      $table->dropColumn('question_image');
    });

    Schema::table('quizzes', function (Blueprint $table) {
      $table->dropColumn(['shuffle_questions', 'shuffle_options', 'time_limit_minutes']);
    });
  }
};
