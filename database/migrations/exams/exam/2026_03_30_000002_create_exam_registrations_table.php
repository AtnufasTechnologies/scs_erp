<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_registrations', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id');
      $table->foreignId('exam_session_id')->constrained('exam_sessions');
      $table->string('program_type');
      $table->boolean('is_backlog')->default(false);
      $table->string('status')->default('pending');
      $table->timestamp('registered_at')->nullable();
      $table->timestamps();
      $table->index(['erp_student_id', 'exam_session_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_registrations');
  }
};
