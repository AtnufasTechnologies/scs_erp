<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('remuneration_rates', function (Blueprint $table) {
      $table->id();
      $table->enum('duty_type', ['invigilation', 'evaluation', 'moderation']);
      $table->enum('rate_type', ['per_day', 'per_copy', 'per_session']);
      $table->decimal('amount', 10, 2);
      $table->enum('program_type', ['NEP', 'AICTE', 'PG', 'ITEP']);
      $table->timestamps();
      $table->index(['duty_type', 'program_type']);
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('remuneration_rates');
    Schema::enableForeignKeyConstraints();
  }
};
