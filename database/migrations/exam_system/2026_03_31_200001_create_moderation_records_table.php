<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('moderation_records', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->string('erp_student_id');
      $table->string('erp_subject_id');
      $table->decimal('evaluator_marks', 6, 2);
      $table->decimal('moderator_marks', 6, 2)->nullable();
      $table->decimal('adjusted_marks', 6, 2)->nullable();
      $table->decimal('difference', 6, 2)->nullable();
      $table->unsignedInteger('moderator_id')->nullable();
      $table->foreignId('adjusted_by')->nullable()->constrained('users');
      $table->string('status')->default('pending');
      $table->text('remarks')->nullable();
      $table->foreignId('exam_marks_entry_id')->nullable()->constrained('exam_marks_entries');
      $table->timestamps();

      $table->foreign('moderator_id')->references('id')->on('faculties');
      $table->unique(['exam_session_id', 'erp_student_id', 'erp_subject_id'], 'moderation_records_unique');
      $table->index(['exam_session_id', 'erp_subject_id']);
      $table->index('status');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('moderation_records');
  }
};
