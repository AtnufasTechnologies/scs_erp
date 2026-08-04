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
    if (Schema::hasTable('department_teaching_allocation_settings')) {
      return;
    }

    Schema::create('department_teaching_allocation_settings', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id')->unique();
      $table->boolean('allow_multiple_primary_faculty')->default(false);
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();

      $table->index('subject_id');
      $table->index('allow_multiple_primary_faculty');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('department_teaching_allocation_settings');
  }
};
