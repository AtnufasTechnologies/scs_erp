<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('program_regulations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('regulation_name');
      $table->string('regulation_type'); // NEP, AICTE, NCTE
      $table->year('start_year');
      $table->year('end_year')->nullable();
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('program_regulations');
  }
};
