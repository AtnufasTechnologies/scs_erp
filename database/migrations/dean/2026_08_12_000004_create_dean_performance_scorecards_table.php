<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dean_performance_scorecards', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->index();
      $table->string('category');
      $table->text('covers')->nullable();
      $table->decimal('max_score', 8, 2)->default(0);
      $table->decimal('score_given', 8, 2)->default(0);
      $table->string('verified_by')->nullable();
      $table->string('reviewed_by')->nullable();
      $table->text('remarks')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dean_performance_scorecards');
  }
};
