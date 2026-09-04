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
    if (!Schema::hasTable('principal_appointments')) {
      return;
    }

    Schema::table('principal_appointments', function (Blueprint $table) {
      $table->unique(['appointment_date', 'appointment_time'], 'principal_appointments_slot_unique');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('principal_appointments')) {
      return;
    }

    Schema::table('principal_appointments', function (Blueprint $table) {
      $table->dropUnique('principal_appointments_slot_unique');
    });
  }
};
