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
        Schema::create('student_roster_rule_results', function (Blueprint $table) {

            $table->id();

            $table->foreignId('rule_mapping_id')
                ->nullable()
                ->constrained('student_roster_rule_mappings')
                ->nullOnDelete();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_course_id')->nullable();

            $table->boolean('included')
                ->default(false);

            $table->string('decision', 50);
            /*
             * INCLUDED
             * EXCLUDED
             * REJECTED
             * NO_RULE
             */

            $table->string('reason_code', 100)->nullable();

            $table->text('reason')->nullable();

            /*
             * Useful while developing/debugging the engine.
             */
            $table->json('diagnostic_data')->nullable();

            $table->timestamps();

            $table->index([
                'student_id',
                'subject_course_id'
            ]);

            $table->index([
                'included',
                'reason_code'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_roster_rule_results');
    }
};
