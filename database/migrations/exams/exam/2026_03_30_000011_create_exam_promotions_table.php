<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_promotions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id');
      $table->foreignId('from_session_id')->constrained('exam_sessions');
      $table->foreignId('to_session_id')->constrained('exam_sessions');
      $table->timestamp('promoted_at')->nullable();
      $table->timestamps();
      $table->index(['erp_student_id', 'from_session_id', 'to_session_id'], 'exam_promotions_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_promotions');
  }
};
