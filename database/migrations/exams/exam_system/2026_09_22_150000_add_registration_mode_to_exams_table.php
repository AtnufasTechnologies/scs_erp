<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('exams', function (Blueprint $table) {
      if (!Schema::hasColumn('exams', 'registration_mode')) {
        $table->string('registration_mode', 30)
          ->default('registration_required')
          ->after('status');
      }
    });
  }

  public function down(): void
  {
    Schema::table('exams', function (Blueprint $table) {
      if (Schema::hasColumn('exams', 'registration_mode')) {
        $table->dropColumn('registration_mode');
      }
    });
  }
};
