<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('duty_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('faculty_id')->constrained('faculty_profiles');
      $table->string('duty_type'); // invigilation/evaluation/moderation
      $table->unsignedBigInteger('reference_id');
      $table->string('action');
      $table->timestamp('timestamp');
      $table->timestamps();
      $table->index(['faculty_id', 'duty_type', 'reference_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('duty_logs');
  }
};
