<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (!Schema::hasColumn('teaching_group_items', 'room_no')) {
      Schema::table('teaching_group_items', function (Blueprint $table) {
        $table->string('room_no', 80)->nullable()->after('faculty_id');
      });
    }
  }

  public function down(): void
  {
    if (Schema::hasColumn('teaching_group_items', 'room_no')) {
      Schema::table('teaching_group_items', function (Blueprint $table) {
        $table->dropColumn('room_no');
      });
    }
  }
};
