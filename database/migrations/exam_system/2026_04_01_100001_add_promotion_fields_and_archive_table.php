<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Add semester and current_status to exam_students
    Schema::table('exam_students', function (Blueprint $table) {
      $table->unsignedTinyInteger('current_semester')->default(1)->after('enrollment_no');
      $table->string('promotion_status')->nullable()->after('status')->comment('promoted, detained, withheld');
    });

    // Add promotion_status and from/to semester to promotions
    Schema::table('promotions', function (Blueprint $table) {
      $table->unsignedBigInteger('exam_session_id')->nullable()->after('exam_student_id');
      $table->string('regulation_type')->nullable()->after('to_exam_id');
      $table->string('promotion_status')->default('promoted')->after('regulation_type')->comment('promoted, detained');
      $table->unsignedTinyInteger('from_semester')->nullable()->after('promotion_status');
      $table->unsignedTinyInteger('to_semester')->nullable()->after('from_semester');
      $table->integer('total_credits')->nullable()->after('to_semester');
      $table->integer('pending_backlogs')->nullable()->after('total_credits');
      $table->text('reason')->nullable()->after('pending_backlogs');

      $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
    });

    // Add session fields to backlogs
    Schema::table('backlogs', function (Blueprint $table) {
      if (!Schema::hasColumn('backlogs', 'exam_session_id')) {
        $table->unsignedBigInteger('exam_session_id')->nullable()->after('exam_id');
        $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
      }
      if (!Schema::hasColumn('backlogs', 'attempt_number')) {
        $table->unsignedTinyInteger('attempt_number')->default(1)->after('status');
      }
      if (!Schema::hasColumn('backlogs', 'previous_marks')) {
        $table->decimal('previous_marks', 6, 2)->nullable()->after('attempt_number');
      }
      if (!Schema::hasColumn('backlogs', 'previous_grade')) {
        $table->string('previous_grade', 10)->nullable()->after('previous_marks');
      }
    });

    // Create student_promotion_histories table for archiving
    Schema::create('student_promotion_histories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students')->cascadeOnDelete();
      $table->unsignedBigInteger('exam_session_id');
      $table->unsignedBigInteger('promotion_id')->nullable();
      $table->unsignedTinyInteger('semester');
      $table->string('result_status')->nullable()->comment('pass, fail, withheld');
      $table->string('promotion_status')->nullable()->comment('promoted, detained');
      $table->decimal('sgpa', 4, 2)->nullable();
      $table->decimal('percentage', 5, 2)->nullable();
      $table->integer('total_credits_earned')->default(0);
      $table->integer('pending_backlogs')->default(0);
      $table->json('subjects_snapshot')->nullable()->comment('archived subject results');
      $table->timestamps();

      $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->cascadeOnDelete();
      $table->foreign('promotion_id')->references('id')->on('promotions')->nullOnDelete();
      $table->index(['exam_student_id', 'semester']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('student_promotion_histories');

    Schema::table('backlogs', function (Blueprint $table) {
      if (Schema::hasColumn('backlogs', 'exam_session_id')) {
        $table->dropForeign(['exam_session_id']);
        $table->dropColumn('exam_session_id');
      }
      if (Schema::hasColumn('backlogs', 'attempt_number')) {
        $table->dropColumn('attempt_number');
      }
      if (Schema::hasColumn('backlogs', 'previous_marks')) {
        $table->dropColumn('previous_marks');
      }
      if (Schema::hasColumn('backlogs', 'previous_grade')) {
        $table->dropColumn('previous_grade');
      }
    });

    Schema::table('promotions', function (Blueprint $table) {
      $table->dropForeign(['exam_session_id']);
      $table->dropColumn([
        'exam_session_id',
        'regulation_type',
        'promotion_status',
        'from_semester',
        'to_semester',
        'total_credits',
        'pending_backlogs',
        'reason'
      ]);
    });

    Schema::table('exam_students', function (Blueprint $table) {
      $table->dropColumn(['current_semester', 'promotion_status']);
    });
  }
};
