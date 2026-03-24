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
    Schema::create('leave_masters', function (Blueprint $table) {
      $table->id();
      $table->string('leave_type_name'); // Casual Leave, Sick Leave, etc.
      $table->string('leave_type_code')->unique(); // casual, sick, earned
      $table->text('description')->nullable();
      $table->integer('allowed_days_per_year')->nullable(); // null for unlimited
      $table->boolean('requires_attachment')->default(false);
      $table->boolean('is_active')->default(true);
      $table->integer('display_order')->default(0);
      $table->string('badge_color')->default('primary'); // primary, success, danger, etc.
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('leave_masters');
  }
};
