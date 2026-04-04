<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_marks_locks', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions')->onDelete('cascade');
      $table->unsignedBigInteger('erp_subject_id');
      $table->boolean('is_locked')->default(false);
      $table->unsignedBigInteger('locked_by')->nullable();
      $table->timestamp('locked_at')->nullable();
      $table->unsignedBigInteger('unlocked_by')->nullable();
      $table->timestamp('unlocked_at')->nullable();
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->unique(['exam_session_id', 'erp_subject_id']);
      $table->index('is_locked');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_marks_locks');
  }
};
