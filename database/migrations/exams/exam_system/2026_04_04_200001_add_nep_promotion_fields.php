<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Add NEP fields to backlogs table
    Schema::table('backlogs', function (Blueprint $table) {
      if (!Schema::hasColumn('backlogs', 'semester')) {
        $table->unsignedTinyInteger('semester')->nullable()->after('exam_session_id');
      }
      if (!Schema::hasColumn('backlogs', 'credits')) {
        $table->unsignedTinyInteger('credits')->default(0)->after('semester');
      }
      if (!Schema::hasColumn('backlogs', 'max_attempts')) {
        $table->unsignedTinyInteger('max_attempts')->nullable()->after('attempt_number');
      }
      if (!Schema::hasColumn('backlogs', 'cleared_exam_session_id')) {
        $table->unsignedBigInteger('cleared_exam_session_id')->nullable()->after('previous_grade');
        $table->foreign('cleared_exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
      }
      if (!Schema::hasColumn('backlogs', 'cleared_marks')) {
        $table->decimal('cleared_marks', 6, 2)->nullable()->after('cleared_exam_session_id');
      }
      if (!Schema::hasColumn('backlogs', 'cleared_grade')) {
        $table->string('cleared_grade', 10)->nullable()->after('cleared_marks');
      }
    });

    // Add NEP fields to promotions table
    Schema::table('promotions', function (Blueprint $table) {
      if (!Schema::hasColumn('promotions', 'earned_credits')) {
        $table->integer('earned_credits')->default(0)->after('total_credits');
      }
      if (!Schema::hasColumn('promotions', 'transferred_credits')) {
        $table->integer('transferred_credits')->default(0)->after('earned_credits');
      }
      if (!Schema::hasColumn('promotions', 'backlog_subjects')) {
        $table->json('backlog_subjects')->nullable()->after('pending_backlogs')->comment('List of failed subject IDs');
      }
    });

    // Add cumulative credits to student_promotion_histories
    Schema::table('student_promotion_histories', function (Blueprint $table) {
      if (!Schema::hasColumn('student_promotion_histories', 'earned_credits')) {
        $table->integer('earned_credits')->default(0)->after('total_credits_earned');
      }
      if (!Schema::hasColumn('student_promotion_histories', 'transferred_credits')) {
        $table->integer('transferred_credits')->default(0)->after('earned_credits');
      }
      if (!Schema::hasColumn('student_promotion_histories', 'backlog_subjects')) {
        $table->json('backlog_subjects')->nullable()->after('pending_backlogs');
      }
    });
  }

  public function down(): void
  {
    Schema::table('backlogs', function (Blueprint $table) {
      $columns = ['semester', 'credits', 'max_attempts', 'cleared_marks', 'cleared_grade'];
      foreach ($columns as $col) {
        if (Schema::hasColumn('backlogs', $col)) {
          $table->dropColumn($col);
        }
      }
      if (Schema::hasColumn('backlogs', 'cleared_exam_session_id')) {
        $table->dropForeign(['cleared_exam_session_id']);
        $table->dropColumn('cleared_exam_session_id');
      }
    });

    Schema::table('promotions', function (Blueprint $table) {
      $columns = ['earned_credits', 'transferred_credits', 'backlog_subjects'];
      foreach ($columns as $col) {
        if (Schema::hasColumn('promotions', $col)) {
          $table->dropColumn($col);
        }
      }
    });

    Schema::table('student_promotion_histories', function (Blueprint $table) {
      $columns = ['earned_credits', 'transferred_credits', 'backlog_subjects'];
      foreach ($columns as $col) {
        if (Schema::hasColumn('student_promotion_histories', $col)) {
          $table->dropColumn($col);
        }
      }
    });
  }
};
