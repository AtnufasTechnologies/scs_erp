<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_exit_certifications', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id');
      $table->string('exit_level');
      $table->foreignId('session_id')->constrained('exam_sessions');
      $table->timestamp('issued_at')->nullable();
      $table->timestamps();
      $table->index(['erp_student_id', 'exit_level', 'session_id'], 'exam_exit_cert_idx');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_exit_certifications');
  }
};
