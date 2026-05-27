<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_revaluations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->unsignedBigInteger('erp_student_id');
      $table->unsignedBigInteger('erp_subject_id');
      $table->timestamp('applied_at')->nullable();
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->index(['exam_session_id', 'erp_student_id', 'erp_subject_id'], 'exam_revaluations_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_revaluations');
  }
};
