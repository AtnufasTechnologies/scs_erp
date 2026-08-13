<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('quiz_attempt_answers', function (Blueprint $table) {
      $table->index(['quiz_attempt_id', 'is_correct'], 'qaa_attempt_correct_idx');
    });
  }

  public function down(): void
  {
    Schema::table('quiz_attempt_answers', function (Blueprint $table) {
      $table->dropIndex('qaa_attempt_correct_idx');
    });
  }
};
