<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (Schema::hasTable('exam_batch_semester_scopes')) {
      return;
    }

    Schema::create('exam_batch_semester_scopes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('semester_id');
      $table->timestamps();

      $table->unique(['exam_id', 'batch_id', 'semester_id'], 'ebss_exam_batch_sem_unique');
      $table->index(['batch_id', 'semester_id'], 'ebss_batch_sem_idx');

      $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
      $table->foreign('batch_id')->references('id')->on('batch_masters')->onDelete('cascade');
      $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('exam_batch_semester_scopes')) {
      return;
    }

    Schema::dropIfExists('exam_batch_semester_scopes');
  }
};
