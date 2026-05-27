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
        Schema::create('applicant_program_change_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('registration_id');
            $table->integer('application_id');
            $table->integer('old_program_id');
            $table->integer('new_program_id');
            $table->integer('changed_by');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('applicant_program_change_infos');
        Schema::enableForeignKeyConstraints();
    }
};
