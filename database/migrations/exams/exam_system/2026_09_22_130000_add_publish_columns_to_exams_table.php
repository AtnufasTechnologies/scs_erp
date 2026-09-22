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
      if (!Schema::hasColumn('exams', 'is_published')) {
        $table->boolean('is_published')->default(false)->after('status');
      }

      if (!Schema::hasColumn('exams', 'published_at')) {
        $table->timestamp('published_at')->nullable()->after('is_published');
      }
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('exams')) {
      return;
    }

    Schema::table('exams', function (Blueprint $table) {
      if (Schema::hasColumn('exams', 'published_at')) {
        $table->dropColumn('published_at');
      }

      if (Schema::hasColumn('exams', 'is_published')) {
        $table->dropColumn('is_published');
      }
    });
  }
};
