<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_program_participants', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('program_id');
      $table->unsignedBigInteger('college_id')->nullable()->comment('Null for intra-college participants');
      $table->string('first_name');
      $table->string('last_name');
      $table->string('email')->nullable();
      $table->string('phone', 20)->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('program_id')->references('id')->on('ec_programs')->cascadeOnDelete();
      $table->foreign('college_id')->references('id')->on('ec_program_colleges')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_program_participants');
  }
};
