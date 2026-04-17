<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_dissertations', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_student_id');
      $table->foreignId('session_id')->constrained('exam_sessions');
      $table->string('title');
      $table->string('supervisor')->nullable();
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->index(['erp_student_id', 'session_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_dissertations');
  }
};
