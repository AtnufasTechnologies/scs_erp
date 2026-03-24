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
        Schema::create('syllabus_subunits', function (Blueprint $table) {
            $table->id();
            $table->integer('syllabus_manager_id');
            $table->integer('unit_id');
            $table->smallInteger('is_completed')->default(0); //faculty Remark
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_subunits');
    }
};
