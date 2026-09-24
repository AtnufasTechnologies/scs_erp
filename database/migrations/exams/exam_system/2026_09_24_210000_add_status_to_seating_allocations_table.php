<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasTable('seating_allocations')) {
      return;
    }

    if (!Schema::hasColumn('seating_allocations', 'status')) {
      Schema::table('seating_allocations', function (Blueprint $table) {
        $table->string('status', 20)->default('finalized')->after('seat_no');
        $table->index('status', 'seating_allocations_status_idx');
      });

      DB::table('seating_allocations')->update(['status' => 'finalized']);
    }
  }

  public function down(): void
  {
    if (!Schema::hasTable('seating_allocations') || !Schema::hasColumn('seating_allocations', 'status')) {
      return;
    }

    Schema::table('seating_allocations', function (Blueprint $table) {
      $table->dropIndex('seating_allocations_status_idx');
      $table->dropColumn('status');
    });
  }
};
