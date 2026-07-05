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
        Schema::create('annual_promotion_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('batch')->nullable();
            $table->integer('campus')->nullable();
            $table->integer('student_id')->nullable();
            $table->smallInteger('promoted_from_year')->nullable();
            $table->smallInteger('promoted_to_year')->nullable();
            $table->string('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annual_promotion_logs');
    }
};
