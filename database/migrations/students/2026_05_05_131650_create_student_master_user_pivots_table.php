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
        Schema::create('student_master_user_pivots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_master_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('student_master_id')->references('id')->on('student_masters')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('student_master_user_pivots');
        Schema::enableForeignKeyConstraints();
    }
};
