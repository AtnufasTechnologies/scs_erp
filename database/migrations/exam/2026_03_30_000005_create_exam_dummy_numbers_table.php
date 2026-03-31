<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_dummy_numbers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->unsignedBigInteger('erp_student_id');
      $table->string('dummy_number')->unique();
      $table->timestamps();
      $table->index(['exam_session_id', 'erp_student_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_dummy_numbers');
  }
};
