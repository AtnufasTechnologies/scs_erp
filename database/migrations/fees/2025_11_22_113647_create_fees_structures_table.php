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
        Schema::create('fees_structures', function (Blueprint $table) {
            $table->id();
            $table->integer('program_id');
            $table->integer('batch_id');
            $table->integer('course_name');
            $table->string('quarter_title')->nullable();
            $table->smallInteger('yearly_pay_order');
            $table->smallInteger('std_current_year');
            $table->date('due_date');
            $table->date('reminder_date');
            $table->smallInteger('is_payable')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_structures');
    }
};
