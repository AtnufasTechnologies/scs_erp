<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_marks_weightages', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->unsignedBigInteger('erp_subject_id');
      $table->string('component'); // e.g., theory, practical, internal
      $table->decimal('weightage', 5, 2);
      $table->timestamps();
      $table->index(['exam_session_id', 'erp_subject_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_marks_weightages');
  }
};
