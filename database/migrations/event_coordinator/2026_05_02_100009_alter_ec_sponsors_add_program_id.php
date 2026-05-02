<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('ec_sponsors', function (Blueprint $table) {
      // Drop existing foreign key
      $table->dropForeign(['event_id']);

      // Make event_id nullable
      $table->unsignedBigInteger('event_id')->nullable()->change();

      // Add program_id
      $table->unsignedBigInteger('program_id')->nullable()->after('event_id');

      // Add back foreign keys
      $table->foreign('event_id')->references('id')->on('ec_events')->cascadeOnDelete();
      $table->foreign('program_id')->references('id')->on('ec_programs')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::table('ec_sponsors', function (Blueprint $table) {
      $table->dropForeign(['event_id']);
      $table->dropForeign(['program_id']);

      $table->unsignedBigInteger('event_id')->nullable(false)->change();
      $table->dropColumn('program_id');

      $table->foreign('event_id')->references('id')->on('ec_events')->cascadeOnDelete();
    });
  }
};
