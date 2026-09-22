<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('rooms', function (Blueprint $table) {
      if (!Schema::hasColumn('rooms', 'name')) {
        $table->string('name')->nullable()->after('room_no');
      }

      if (!Schema::hasColumn('rooms', 'building')) {
        $table->string('building', 120)->nullable()->after('name')->index();
      }

      if (!Schema::hasColumn('rooms', 'room_number')) {
        $table->string('room_number', 60)->nullable()->after('building');
      }

      if (!Schema::hasColumn('rooms', 'rows')) {
        $table->unsignedInteger('rows')->nullable()->after('room_number');
      }

      if (!Schema::hasColumn('rooms', 'columns')) {
        $table->unsignedInteger('columns')->nullable()->after('rows');
      }

      if (!Schema::hasColumn('rooms', 'priority')) {
        $table->unsignedInteger('priority')->default(1)->after('capacity')->index();
      }
    });

    DB::table('rooms')->whereNull('room_number')->update([
      'room_number' => DB::raw('room_no'),
    ]);

    DB::table('rooms')->whereNull('building')->update([
      'building' => DB::raw("COALESCE(NULLIF(location, ''), 'Main Building')"),
    ]);

    DB::table('rooms')->whereNull('rows')->update(['rows' => 1]);
    DB::table('rooms')->whereNull('columns')->update(['columns' => 1]);

    DB::statement('UPDATE `rooms` SET `capacity` = COALESCE(`capacity`, `rows` * `columns`)');

    DB::table('rooms')->whereNull('name')->update([
      'name' => DB::raw('room_number'),
    ]);

    // Enforce unique room number per building for exam room management.
    $duplicateCount = DB::table('rooms')
      ->select('building', 'room_number')
      ->groupBy('building', 'room_number')
      ->havingRaw('COUNT(*) > 1')
      ->count();

    if ($duplicateCount === 0) {
      Schema::table('rooms', function (Blueprint $table) {
        $table->unique(['building', 'room_number'], 'rooms_building_room_number_unique');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('rooms', function (Blueprint $table) {
      try {
        $table->dropUnique('rooms_building_room_number_unique');
      } catch (\Throwable $e) {
        // Ignore when index does not exist.
      }

      if (Schema::hasColumn('rooms', 'priority')) {
        $table->dropColumn('priority');
      }

      if (Schema::hasColumn('rooms', 'columns')) {
        $table->dropColumn('columns');
      }

      if (Schema::hasColumn('rooms', 'rows')) {
        $table->dropColumn('rows');
      }

      if (Schema::hasColumn('rooms', 'room_number')) {
        $table->dropColumn('room_number');
      }

      if (Schema::hasColumn('rooms', 'building')) {
        $table->dropColumn('building');
      }

      if (Schema::hasColumn('rooms', 'name')) {
        $table->dropColumn('name');
      }
    });
  }
};
