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
    Schema::create('api_metrix_category_role', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('api_metrix_category_id');
      $table->unsignedBigInteger('role_master_id');
      $table->timestamps();

      $table->unique(['api_metrix_category_id', 'role_master_id'], 'api_metrix_category_role_unique');
      $table->index('role_master_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('api_metrix_category_role');
  }
};
