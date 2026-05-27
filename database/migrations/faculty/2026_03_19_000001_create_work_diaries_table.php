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
    Schema::create('work_diaries', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('faculty_id');
      $table->date('date');
      $table->integer('hour'); // Hour of the day (0-23)
      $table->text('description');
      $table->string('status')->default('pending'); // pending, completed
      $table->timestamps();

      $table->index(['faculty_id', 'date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('work_diaries');
  }
};
