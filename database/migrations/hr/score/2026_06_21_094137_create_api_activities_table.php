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
    Schema::create('api_activities', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('faculty_id');
      $table->unsignedBigInteger('academic_year_id');
      $table->enum('activity_type', [
        'cocurricular',
        'managerial',
        'professional_development',
        'seminar_conference'
      ]);
      $table->string('activity_name');
      $table->string('role')->nullable();
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->integer('duration_days')->nullable();
      $table->enum('level', ['International', 'National', 'Institutional'])->nullable();
      $table->text('description')->nullable();
      $table->string('document_path')->nullable();
      $table->decimal('api_score', 5, 2)->default(0);
      $table->timestamps();
      $table->softDeletes();

      $table->index('faculty_id');
      $table->index('academic_year_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('api_activities');
  }
};
