<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('backlogs', function (Blueprint $table) {
      if (!Schema::hasColumn('backlogs', 'exam_session_id')) {
        $table->unsignedBigInteger('exam_session_id')->nullable()->after('exam_id');
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
      if (!Schema::hasColumn('backlogs', 'remarks')) {
        $table->text('remarks')->nullable()->after('previous_grade');
      }
      if (!Schema::hasColumn('backlogs', 'registered_at')) {
        $table->timestamp('registered_at')->nullable()->after('remarks');
      }
      if (!Schema::hasColumn('backlogs', 'cleared_at')) {
        $table->timestamp('cleared_at')->nullable()->after('registered_at');
      }

      // Add foreign key if it doesn't exist
      $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'backlogs' AND CONSTRAINT_NAME = 'backlogs_exam_session_id_foreign' AND TABLE_SCHEMA = DATABASE()");
      if (empty($foreignKeys)) {
        $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
      }
    });
  }

  public function down(): void
  {
    Schema::table('backlogs', function (Blueprint $table) {
      $table->dropForeign(['exam_session_id']);
      $table->dropColumn([
        'exam_session_id',
        'attempt_number',
        'previous_marks',
        'previous_grade',
        'remarks',
        'registered_at',
        'cleared_at',
      ]);
    });
  }
};
