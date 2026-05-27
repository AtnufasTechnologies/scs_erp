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
        Schema::create('subject_combination_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('main_subject_id');
            $table->unsignedBigInteger('combo_subject_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('batch_id')->references('id')->on('batch_masters')->onDelete('cascade');
            $table->foreign('campus_id')->references('id')->on('campuses')->onDelete('cascade');
            $table->foreign('main_subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('combo_subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('subject_combination_masters');
        Schema::enableForeignKeyConstraints();
    }
};
