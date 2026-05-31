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
        Schema::create('std_prog_combo_maps', function (Blueprint $table) {
            $table->id();
            $table->integer('student_program_id');
            $table->integer('combo_id_1');
            $table->integer('combo_id_2');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('std_prog_combo_maps');
    }
};
