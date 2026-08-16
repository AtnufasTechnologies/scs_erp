<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_roster_rule_mappings', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Rule Master
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('rule_id');

            /*
            |--------------------------------------------------------------------------
            | Academic Pathway
            |--------------------------------------------------------------------------
            |
            | Existing master:
            |
            | 1 = Single MAJOR
            | 2 = Dual Major
            |
            */
            $table->unsignedBigInteger('academic_pathway_id');

            /*
            |--------------------------------------------------------------------------
            | Degree Track
            |--------------------------------------------------------------------------
            |
            | Existing master:
            |
            | 1 = Regular
            | 2 = Honours
            | 3 = Honours with Research
            |
            | Nullable because a rule may eventually apply to all
            | degree tracks within a pathway.
            |
            */
            $table->unsignedBigInteger('degree_track_id')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Course Delivery
            |--------------------------------------------------------------------------
            */
            $table->string('delivery_type', 50);

            /*
            |--------------------------------------------------------------------------
            | Selection Type
            |--------------------------------------------------------------------------
            |
            | AUTO
            | STUDENT_CHOICE
            | NULL = not applicable
            |
            */
            $table->string('selection_type', 50)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Roster Source
            |--------------------------------------------------------------------------
            |
            | COMBO1
            | COMBO2
            | STUDENT_SELECTION
            | CURRICULUM
            | TEACHING_GROUP
            |
            */
            $table->string('roster_source', 100);

            /*
            |--------------------------------------------------------------------------
            | Scope
            |--------------------------------------------------------------------------
            */
            $table->string('semester_scope', 30)
                ->default('SAME');

            $table->string('batch_scope', 30)
                ->default('SAME');

            $table->string('program_scope', 30)
                ->default('MULTIPLE');

            $table->string('specialization_scope', 30)
                ->default('ANY');

            /*
            |--------------------------------------------------------------------------
            | Major Restriction
            |--------------------------------------------------------------------------
            |
            | NONE
            | EXCLUDE_MAJOR_DEPARTMENTS
            |
            */
            $table->string('major_restriction', 50)
                ->default('NONE');

            /*
            |--------------------------------------------------------------------------
            | Student Selection
            |--------------------------------------------------------------------------
            */
            $table->boolean('student_selection_required')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Teaching Group
            |--------------------------------------------------------------------------
            |
            | Explicit Deanery/Teaching Group can override normal
            | semester/batch restrictions.
            |
            */
            $table->boolean('teaching_group_override')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Rule Priority
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('priority')
                ->default(100);

            $table->boolean('is_active')
                ->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index([
                'academic_pathway_id',
                'degree_track_id',
                'delivery_type',
                'selection_type',
            ], 'roster_rule_lookup_idx');

            $table->index([
                'academic_pathway_id',
                'delivery_type',
                'is_active',
            ], 'roster_rule_active_idx');

            $table->index([
                'priority',
                'is_active',
            ]);
        });

        Schema::table('student_roster_rule_mappings', function (Blueprint $table) {
            if (Schema::hasTable('student_roster_rule_masters')) {
                $table->foreign('rule_id')
                    ->references('id')
                    ->on('student_roster_rule_masters')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('academic_pathway_masters')) {
                $table->foreign('academic_pathway_id')
                    ->references('id')
                    ->on('academic_pathway_masters')
                    ->onDelete('restrict');
            }

            if (Schema::hasTable('degree_track_masters')) {
                $table->foreign('degree_track_id')
                    ->references('id')
                    ->on('degree_track_masters')
                    ->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_roster_rule_mappings');
    }
};
