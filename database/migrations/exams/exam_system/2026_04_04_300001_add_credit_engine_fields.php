<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Add exam_session_id to student_credits for session tracking
    Schema::table('student_credits', function (Blueprint $table) {
      if (!Schema::hasColumn('student_credits', 'exam_session_id')) {
        $table->unsignedBigInteger('exam_session_id')->nullable()->after('exam_subject_id');
        $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
      }
    });

    // Add earned_credits to results for per-session credit snapshot
    Schema::table('results', function (Blueprint $table) {
      if (!Schema::hasColumn('results', 'earned_credits')) {
        $table->integer('earned_credits')->nullable()->after('percentage');
      }
    });
  }

  public function down(): void
  {
    Schema::table('student_credits', function (Blueprint $table) {
      if (Schema::hasColumn('student_credits', 'exam_session_id')) {
        $table->dropForeign(['exam_session_id']);
        $table->dropColumn('exam_session_id');
      }
    });

    Schema::table('results', function (Blueprint $table) {
      if (Schema::hasColumn('results', 'earned_credits')) {
        $table->dropColumn('earned_credits');
      }
    });
  }
};
