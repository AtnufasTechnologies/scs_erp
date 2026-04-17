<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_subjects', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_subject_id');
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->boolean('is_backlog')->default(false);
      $table->timestamps();
      $table->index(['erp_subject_id', 'exam_session_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_subjects');
  }
};
