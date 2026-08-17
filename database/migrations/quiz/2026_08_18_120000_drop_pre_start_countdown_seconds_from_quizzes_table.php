<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (Schema::hasColumn('quizzes', 'pre_start_countdown_seconds')) {
      Schema::table('quizzes', function (Blueprint $table) {
        $table->dropColumn('pre_start_countdown_seconds');
      });
    }
  }

  public function down(): void
  {
    if (!Schema::hasColumn('quizzes', 'pre_start_countdown_seconds')) {
      Schema::table('quizzes', function (Blueprint $table) {
        $table->unsignedSmallInteger('pre_start_countdown_seconds')->default(10);
      });
    }
  }
};
