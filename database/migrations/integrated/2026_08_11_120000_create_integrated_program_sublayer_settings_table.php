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
    Schema::create('integrated_program_sublayer_settings', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_program_id');
      $table->unsignedTinyInteger('ug_max_year')->default(2)->comment('Year boundary for UG layer in integrated programs.');
      $table->string('ug_label')->nullable();
      $table->string('pg_label')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
      $table->softDeletes();

      $table->unique('student_program_id', 'integrated_program_sublayer_settings_program_unique');
      $table->index('is_active');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('integrated_program_sublayer_settings');
  }
};
