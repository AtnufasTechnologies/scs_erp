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
        Schema::create('program_track_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('program_id');
            $table->string('coode')->nullable();
            $table->string('title')->nullable();
            $table->smallInteger('effective_semester');
            $table->string('allowed_pathway_id');
            $table->string('allowed_degree_track_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_track_configurations');
    }
};
