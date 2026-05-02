<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_program_colleges', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('program_id');
      $table->string('college_name');
      $table->string('college_code')->nullable();
      $table->string('contact_person')->nullable();
      $table->string('contact_email')->nullable();
      $table->string('contact_phone', 20)->nullable();
      $table->string('address')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('program_id')->references('id')->on('ec_programs')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_program_colleges');
  }
};
