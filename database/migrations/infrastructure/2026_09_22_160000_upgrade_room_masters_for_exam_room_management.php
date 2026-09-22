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
    Schema::table('room_masters', function (Blueprint $table) {
      if (!Schema::hasColumn('room_masters', 'block_id')) {
        $table->unsignedBigInteger('block_id')->nullable()->after('title')->index();
      }

      if (!Schema::hasColumn('room_masters', 'room_number')) {
        $table->string('room_number', 100)->nullable()->after('block_id');
      }

      if (!Schema::hasColumn('room_masters', 'rows')) {
        $table->unsignedInteger('rows')->nullable()->after('room_number');
      }

      if (!Schema::hasColumn('room_masters', 'columns')) {
        $table->unsignedInteger('columns')->nullable()->after('rows');
      }

      if (!Schema::hasColumn('room_masters', 'capacity')) {
        $table->unsignedInteger('capacity')->nullable()->after('columns');
      }

      if (!Schema::hasColumn('room_masters', 'priority')) {
        $table->unsignedInteger('priority')->default(1)->after('capacity')->index();
      }

      if (!Schema::hasColumn('room_masters', 'room_code')) {
        $table->string('room_code', 100)->nullable()->after('priority');
      }

      if (!Schema::hasColumn('room_masters', 'floor')) {
        $table->string('floor', 50)->nullable()->after('room_code');
      }

      if (!Schema::hasColumn('room_masters', 'room_type')) {
        $table->string('room_type', 100)->nullable()->after('floor');
      }

      if (!Schema::hasColumn('room_masters', 'status')) {
        $table->string('status', 20)->default('active')->after('room_type');
      }
    });

    DB::table('room_masters')
      ->whereNull('room_number')
      ->update(['room_number' => DB::raw('title')]);

    DB::table('room_masters')
      ->whereNull('rows')
      ->update(['rows' => 1]);

    DB::table('room_masters')
      ->whereNull('columns')
      ->update(['columns' => 1]);

    DB::statement('UPDATE room_masters SET capacity = COALESCE(capacity, rows * columns)');

    DB::table('room_masters')
      ->whereNull('status')
      ->update(['status' => 'active']);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('room_masters', function (Blueprint $table) {
      if (Schema::hasColumn('room_masters', 'status')) {
        $table->dropColumn('status');
      }

      if (Schema::hasColumn('room_masters', 'room_type')) {
        $table->dropColumn('room_type');
      }

      if (Schema::hasColumn('room_masters', 'floor')) {
        $table->dropColumn('floor');
      }

      if (Schema::hasColumn('room_masters', 'room_code')) {
        $table->dropColumn('room_code');
      }

      if (Schema::hasColumn('room_masters', 'priority')) {
        $table->dropColumn('priority');
      }

      if (Schema::hasColumn('room_masters', 'capacity')) {
        $table->dropColumn('capacity');
      }

      if (Schema::hasColumn('room_masters', 'columns')) {
        $table->dropColumn('columns');
      }

      if (Schema::hasColumn('room_masters', 'rows')) {
        $table->dropColumn('rows');
      }

      if (Schema::hasColumn('room_masters', 'room_number')) {
        $table->dropColumn('room_number');
      }

      if (Schema::hasColumn('room_masters', 'block_id')) {
        $table->dropColumn('block_id');
      }
    });
  }
};
