<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('api_category_scores', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('api_faculty_score_id');
      $table->integer('category_number'); // 1 to 7
      $table->string('component_code')->nullable(); // e.g., "IA", "IB", "IIA", etc.
      $table->decimal('score', 5, 2)->default(0);
      $table->decimal('max_score', 5, 2)->default(0);
      $table->text('description')->nullable();
      $table->json('supporting_data')->nullable(); // Store additional details
      $table->timestamps();

      $table->index('api_faculty_score_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('api_category_scores');
  }
};
