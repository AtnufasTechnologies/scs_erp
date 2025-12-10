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
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->integer('department_id');
            $table->string('emp_id'); 
            $table->string('designation');
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname')->nullable();
            $table->tinyText('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->text('specialization')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculties');
    }
};
