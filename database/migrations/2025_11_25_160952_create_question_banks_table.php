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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id');
            $table->integer('program_id');
            $table->integer('cso_id');
            $table->integer('faculty_id');
            $table->integer('cognitive_lvl_id');
            $table->integer('challenge_lvl'); //1-5;
            $table->text('riddle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
