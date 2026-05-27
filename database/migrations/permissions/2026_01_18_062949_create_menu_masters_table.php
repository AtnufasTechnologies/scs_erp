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
        Schema::create('menu_masters', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('menu_name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('module_type', [
                'master',
                'admission-ug',
                'admission-pg',
                'academics',
                'accounts',
                'hr',
                'examination',
                'access-control',
                'pre-exam',
                'during-exam',
                'post-exam',
                'student-dashboard',
                'faculty-dashboard',
                'alumni-dashboard',
            ]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('menu_masters');
        Schema::enableForeignKeyConstraints();
    }
};
