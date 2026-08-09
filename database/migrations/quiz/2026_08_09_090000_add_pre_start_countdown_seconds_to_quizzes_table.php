<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('quizzes', function (Blueprint $table) {
      if (!Schema::hasColumn('quizzes', 'pre_start_countdown_seconds')) {
        $table->unsignedSmallInteger('pre_start_countdown_seconds')->default(10)->after('time_limit_minutes');
      }
    });
  }

  public function down(): void
  {
    Schema::table('quizzes', function (Blueprint $table) {
      if (Schema::hasColumn('quizzes', 'pre_start_countdown_seconds')) {
        $table->dropColumn('pre_start_countdown_seconds');
      }
    });
  }
};
