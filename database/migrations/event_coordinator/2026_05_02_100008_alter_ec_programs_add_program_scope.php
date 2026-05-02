<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('ec_programs', function (Blueprint $table) {
      $table->enum('program_scope', ['national', 'international'])->default('national')->after('program_type');
    });
  }

  public function down(): void
  {
    Schema::table('ec_programs', function (Blueprint $table) {
      $table->dropColumn('program_scope');
    });
  }
};
