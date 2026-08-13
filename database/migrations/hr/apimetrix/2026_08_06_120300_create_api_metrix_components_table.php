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
    Schema::create('api_metrix_components', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('api_metrix_category_id');
      $table->string('title');
      $table->decimal('score', 8, 2)->default(0);
      $table->unsignedInteger('sort_order')->default(0);
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->index('api_metrix_category_id');
      $table->index('is_active');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('api_metrix_components');
  }
};
