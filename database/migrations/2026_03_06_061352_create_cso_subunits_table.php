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
        Schema::create('cso_subunits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cso_id');
            $table->integer('taxonomy_id')->nullable();
            $table->string('title');
            $table->foreign('cso_id')->references('id')->on('co_has_csos')->onDelete('cascade');
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cso_subunits');
    }
};
