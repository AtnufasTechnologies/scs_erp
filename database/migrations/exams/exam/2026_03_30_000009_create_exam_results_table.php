<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_results', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->unsignedBigInteger('erp_student_id');
      $table->decimal('sgpa', 4, 2)->nullable();
      $table->decimal('cgpa', 4, 2)->nullable();
      $table->string('result_status')->default('pending');
      $table->timestamps();
      $table->index(['exam_session_id', 'erp_student_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_results');
  }
};
