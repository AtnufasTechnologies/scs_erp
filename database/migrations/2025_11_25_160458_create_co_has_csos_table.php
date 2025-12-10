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
        Schema::create('co_has_csos', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_id');
            $table->integer('co_id');
            $table->text('title')->nullable();
            $table->integer('lectures_needed');
            $table->smallInteger('isComplete');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('co_has_csos');
    }
};
