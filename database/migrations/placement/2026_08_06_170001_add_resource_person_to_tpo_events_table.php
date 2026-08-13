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
    if (!Schema::hasTable('tpo_events')) {
      return;
    }

    if (!Schema::hasColumn('tpo_events', 'resource_person')) {
      Schema::table('tpo_events', function (Blueprint $table) {
        $table->string('resource_person')->nullable()->after('title');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('tpo_events')) {
      return;
    }

    if (Schema::hasColumn('tpo_events', 'resource_person')) {
      Schema::table('tpo_events', function (Blueprint $table) {
        $table->dropColumn('resource_person');
      });
    }
  }
};
