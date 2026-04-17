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
        Schema::create('account_office_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assistant_user_id');
            $table->unsignedBigInteger('granted_by_user_id');
            $table->unsignedBigInteger('menu_master_id');
            $table->foreign('assistant_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('granted_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('menu_master_id')->references('id')->on('menu_masters')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_office_permissions');
    }
};
