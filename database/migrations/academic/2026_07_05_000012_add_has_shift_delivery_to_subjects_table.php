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
    Schema::table('subjects', function (Blueprint $table) {
      $table->boolean('has_shift_delivery')->default(0)->after('display_in_admission_form');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('subjects', function (Blueprint $table) {
      $table->dropColumn('has_shift_delivery');
    });
  }
};
