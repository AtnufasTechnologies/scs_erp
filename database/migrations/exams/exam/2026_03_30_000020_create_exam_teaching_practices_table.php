<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_teaching_practices', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id');
      $table->foreignId('session_id')->constrained('exam_sessions');
      $table->string('school_name');
      $table->integer('duration'); // in days
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->index(['erp_student_id', 'session_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_teaching_practices');
  }
};
