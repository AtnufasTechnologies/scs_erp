<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasTable('exams')) {
      return;
    }

    Schema::table('exams', function (Blueprint $table) {
      if (!Schema::hasColumn('exams', 'selected_batch_ids')) {
        $table->json('selected_batch_ids')->nullable()->after('semester');
      }
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('exams')) {
      return;
    }

    Schema::table('exams', function (Blueprint $table) {
      if (Schema::hasColumn('exams', 'selected_batch_ids')) {
        $table->dropColumn('selected_batch_ids');
      }
    });
  }
};
