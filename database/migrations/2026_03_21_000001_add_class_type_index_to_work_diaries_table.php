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
    Schema::table('work_diaries', function (Blueprint $table) {
      $table->index(['faculty_id', 'class_type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('work_diaries', function (Blueprint $table) {
      $table->dropIndex(['faculty_id', 'class_type']);
    });
  }
};
