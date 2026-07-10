<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('quiz_attempts', function (Blueprint $table) {
      $table->dropUnique('quiz_attempts_quiz_id_student_id_unique');
      $table->unsignedInteger('attempt_no')->default(1)->after('student_id');
      $table->dateTime('expires_at')->nullable()->after('started_at');
      $table->boolean('submitted_by_timeout')->default(false)->after('submitted_at');

      $table->unique(['quiz_id', 'student_id', 'attempt_no'], 'quiz_attempt_unique_per_number');
      $table->index(['quiz_id', 'student_id']);
    });
  }

  public function down(): void
  {
    Schema::table('quiz_attempts', function (Blueprint $table) {
      $table->dropUnique('quiz_attempt_unique_per_number');
      $table->dropIndex('quiz_attempts_quiz_id_student_id_index');
      $table->dropColumn(['attempt_no', 'expires_at', 'submitted_by_timeout']);
      $table->unique(['quiz_id', 'student_id']);
    });
  }
};
