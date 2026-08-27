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
    Schema::create('training_placement_form_templates', function (Blueprint $table) {
      $table->id();
      $table->string('title')->nullable();
      $table->string('file_path');
      $table->unsignedBigInteger('uploaded_by')->nullable();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->index('is_active', 'tp_form_templates_active_idx');
      $table->index('uploaded_by', 'tp_form_templates_uploaded_by_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('training_placement_form_templates');
  }
};
