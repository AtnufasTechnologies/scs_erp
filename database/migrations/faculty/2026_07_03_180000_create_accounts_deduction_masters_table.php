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
    Schema::create('accounts_deduction_masters', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->integer('TDS')->nullable('0');
      $table->integer('EPF')->nullable('0');
      $table->integer('PT')->nullable('0');
      $table->integer('ESIC')->nullable('0');
      $table->integer('LWF')->nullable('0');
      $table->boolean('status')->default('1'); //active 1 , inactive 0
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('accounts_deduction_masters');
    Schema::enableForeignKeyConstraints();
  }
};
