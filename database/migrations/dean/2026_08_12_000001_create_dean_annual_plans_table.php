<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dean_annual_plans', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->index();
      $table->string('activity_goal');
      $table->string('category')->nullable();
      $table->string('target')->nullable();
      $table->date('expected_completion_date')->nullable();
      $table->string('priority')->nullable();
      $table->string('semester_month')->nullable();
      $table->text('expected_outcome')->nullable();
      $table->text('achievement_actual_result')->nullable();
      $table->text('evidence_required')->nullable();
      $table->string('status')->default('pending');
      $table->string('verified_by')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dean_annual_plans');
  }
};
