<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_faculty_duties', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('event_id');
      $table->unsignedBigInteger('program_id')->nullable()->comment('null = event-wide duty');
      $table->unsignedBigInteger('faculty_id');
      $table->string('duty_title');
      $table->text('responsibility')->nullable();
      $table->enum('status', ['assigned', 'acknowledged', 'completed'])->default('assigned');
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('assigned_by')->nullable();
      $table->timestamps();

      $table->foreign('event_id')->references('id')->on('ec_events')->cascadeOnDelete();
      $table->foreign('program_id')->references('id')->on('ec_programs')->nullOnDelete();
      $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_faculty_duties');
  }
};
