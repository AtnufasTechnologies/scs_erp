<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('grade_mappings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('grade');
      $table->decimal('min_marks', 5, 2);
      $table->decimal('max_marks', 5, 2);
      $table->decimal('grade_point', 3, 2);
      $table->timestamps();
      $table->unique(['program_id', 'grade']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('grade_mappings');
  }
};
