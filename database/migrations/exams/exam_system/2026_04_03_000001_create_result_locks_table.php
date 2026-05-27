<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('result_locks', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_session_id');
      $table->boolean('is_locked')->default(false);
      $table->unsignedBigInteger('locked_by')->nullable();
      $table->timestamp('locked_at')->nullable();
      $table->unsignedBigInteger('unlocked_by')->nullable();
      $table->timestamp('unlocked_at')->nullable();
      $table->string('remarks')->nullable();
      $table->timestamps();

      $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->cascadeOnDelete();
      $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
      $table->foreign('unlocked_by')->references('id')->on('users')->nullOnDelete();
      $table->unique('exam_session_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('result_locks');
  }
};
