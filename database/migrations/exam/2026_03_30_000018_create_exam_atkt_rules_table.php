<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_atkt_rules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('regulation_id')->constrained('exam_regulations');
      $table->integer('max_backlogs');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_atkt_rules');
  }
};
