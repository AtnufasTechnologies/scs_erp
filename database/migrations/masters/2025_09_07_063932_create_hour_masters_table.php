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
        Schema::create('hour_masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id');
            $table->integer('hour_no');
            $table->string('name');          // Hour 1
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_teaching')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_masters');
    }
};
