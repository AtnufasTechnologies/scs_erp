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
        Schema::create('student_late_fee_exemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id')->nullable(); // null = blanket exemption
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fees_structures')->onDelete('cascade');

            // Prevent duplicate exemptions
            $table->unique(['student_id', 'fee_structure_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_late_fee_exemptions');
    }
};
