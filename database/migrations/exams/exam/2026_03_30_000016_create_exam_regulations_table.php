<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_regulations', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('type'); // NEP, AICTE, NCTE
      $table->text('description')->nullable();
      $table->timestamps();
      $table->engine = 'InnoDB';
      $table->charset = 'utf8mb4';
      $table->collation = 'utf8mb4_unicode_ci';
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_regulations');
  }
};
