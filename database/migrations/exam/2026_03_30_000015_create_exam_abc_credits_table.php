<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_abc_credits', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id');
      $table->unsignedBigInteger('erp_subject_id');
      $table->integer('credits_earned');
      $table->foreignId('session_id')->constrained('exam_sessions');
      $table->timestamps();
      $table->index(['erp_student_id', 'erp_subject_id', 'session_id'], 'exam_abc_credits_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_abc_credits');
  }
};
