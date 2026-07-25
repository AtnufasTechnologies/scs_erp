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
        Schema::create('time_table_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekday_id');
            $table->foreignId('time_slot_id');
            $table->foreignId('teaching_allocation_id');
            $table->foreignId('room_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_table_entries');
    }
};
