<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('backlogs', function (Blueprint $table) {
      $table->unsignedBigInteger('exam_session_id')->nullable()->after('exam_id');
      $table->unsignedTinyInteger('attempt_number')->default(1)->after('status');
      $table->decimal('previous_marks', 6, 2)->nullable()->after('attempt_number');
      $table->string('previous_grade', 10)->nullable()->after('previous_marks');
      $table->text('remarks')->nullable()->after('previous_grade');
      $table->timestamp('registered_at')->nullable()->after('remarks');
      $table->timestamp('cleared_at')->nullable()->after('registered_at');

      $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
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
