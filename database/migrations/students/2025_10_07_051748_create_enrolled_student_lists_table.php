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
        Schema::create('enrolled_student_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('rollno');
            $table->string('name');
            $table->string('gender');
            $table->string('department');
            $table->string('semester');
            $table->string('session');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrolled_student_lists');
    }
};
