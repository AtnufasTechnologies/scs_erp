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
    Schema::create('receptionist_work_diaries', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->date('entry_date');
      $table->text('work_summary');
      $table->text('notes')->nullable();
      $table->string('status', 20)->default('completed');
      $table->timestamps();

      $table->index(['user_id', 'entry_date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('receptionist_work_diaries');
  }
};
