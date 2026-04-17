<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('condonation_rules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('rule_name');
      $table->text('description')->nullable();
      $table->integer('max_absences');
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('condonation_rules');
  }
};
