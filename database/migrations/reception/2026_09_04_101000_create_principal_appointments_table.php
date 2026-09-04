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
    Schema::create('principal_appointments', function (Blueprint $table) {
      $table->id();
      $table->string('visitor_name');
      $table->string('visitor_phone', 20)->nullable();
      $table->string('visitor_email')->nullable();
      $table->date('appointment_date');
      $table->time('appointment_time')->nullable();
      $table->string('purpose', 255);
      $table->text('notes')->nullable();
      $table->string('status', 20)->default('scheduled');
      $table->unsignedBigInteger('created_by');
      $table->timestamps();

      $table->index(['appointment_date', 'status']);
      $table->index('created_by');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('principal_appointments');
  }
};
