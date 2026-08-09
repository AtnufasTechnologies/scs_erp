<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('quiz_attempt_permissions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('quiz_id');
      $table->unsignedBigInteger('student_id');
      $table->unsignedInteger('max_attempts')->default(2);
      $table->unsignedBigInteger('allowed_by');
      $table->timestamps();

      $table->unique(['quiz_id', 'student_id']);
      $table->index(['quiz_id', 'max_attempts']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('quiz_attempt_permissions');
  }
};
