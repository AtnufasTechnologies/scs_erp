<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasTable('student_quarter_fee_exemptions')) {
      Schema::create('student_quarter_fee_exemptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('fee_structure_id');
        $table->string('reason', 500);
        $table->unsignedBigInteger('approved_by')->nullable();
        $table->timestamp('approved_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('revoked_by')->nullable();
        $table->timestamp('revoked_at')->nullable();
        $table->timestamps();

        $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
        $table->foreign('fee_structure_id')->references('id')->on('fees_structures')->onDelete('cascade');
        $table->unique(['student_id', 'fee_structure_id']);
      });
      return;
    }

    Schema::table('student_quarter_fee_exemptions', function (Blueprint $table) {
      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'student_id')) {
        $table->unsignedBigInteger('student_id')->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'fee_structure_id')) {
        $table->unsignedBigInteger('fee_structure_id')->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'reason')) {
        $table->string('reason', 500)->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'approved_by')) {
        $table->unsignedBigInteger('approved_by')->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'approved_at')) {
        $table->timestamp('approved_at')->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'is_active')) {
        $table->boolean('is_active')->default(true);
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'revoked_by')) {
        $table->unsignedBigInteger('revoked_by')->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'revoked_at')) {
        $table->timestamp('revoked_at')->nullable();
      }

      if (!Schema::hasColumn('student_quarter_fee_exemptions', 'created_at') || !Schema::hasColumn('student_quarter_fee_exemptions', 'updated_at')) {
        $table->timestamps();
      }
    });

    $uniqueIndexName = 'student_quarter_fee_exemptions_student_id_fee_structure_id_unique';
    $hasUnique = DB::table('information_schema.statistics')
      ->where('table_schema', DB::raw('DATABASE()'))
      ->where('table_name', 'student_quarter_fee_exemptions')
      ->where('index_name', $uniqueIndexName)
      ->exists();

    if (!$hasUnique) {
      Schema::table('student_quarter_fee_exemptions', function (Blueprint $table) {
        $table->unique(['student_id', 'fee_structure_id']);
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('student_quarter_fee_exemptions')) {
      Schema::drop('student_quarter_fee_exemptions');
    }
  }
};
