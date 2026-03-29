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
        Schema::table('po_has_cos', function (Blueprint $table) {
            $table->integer('co_id')->nullable()->after('id');
            $table->integer('lectures_needed')->nullable()->after('desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('po_has_cos', function (Blueprint $table) {
            $table->dropColumn(['co_id', 'lectures_needed']);
        });
    }
};
