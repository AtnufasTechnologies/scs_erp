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
        Schema::create('faculty_cso_analytics', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id');
            $table->integer('cso_id');
            $table->integer('lectures_given')->default(0);
            $table->smallInteger('isComplete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_cso_analytics');
    }
};
