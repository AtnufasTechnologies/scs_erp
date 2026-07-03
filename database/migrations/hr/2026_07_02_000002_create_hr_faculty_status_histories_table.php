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
    Schema::create('hr_faculty_status_histories', function (Blueprint $table) {
      $table->id();
      $table->integer('faculty_id');
      $table->string('event_type', 30); // deactivated, reactivated
      $table->date('status_on')->nullable();
      $table->tinyInteger('old_status')->nullable(); // 0 active, 1 left
      $table->tinyInteger('new_status')->nullable(); // 0 active, 1 left
      $table->text('remark')->nullable();
      $table->unsignedBigInteger('acted_by')->nullable();
      $table->timestamps();

      $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
      $table->index(['faculty_id', 'status_on']);
      $table->index('event_type');
      $table->index('acted_by');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hr_faculty_status_histories');
  }
};
