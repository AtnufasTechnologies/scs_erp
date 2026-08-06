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
    Schema::table('placement_opportunities', function (Blueprint $table) {
      $table->string('category')->nullable()->after('title');
      $table->unsignedTinyInteger('month')->nullable()->after('category');
      $table->string('location')->nullable()->after('description');
      $table->string('country')->nullable()->after('location');
      $table->string('logo_path')->nullable()->after('country');
      $table->string('student_year')->nullable()->after('logo_path');
      $table->unsignedBigInteger('subject_id')->nullable()->after('student_year');

      $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
      $table->index(['category', 'month'], 'placement_category_month_idx');
      $table->index('student_year');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('placement_opportunities', function (Blueprint $table) {
      $table->dropForeign(['subject_id']);
      $table->dropIndex('placement_category_month_idx');
      $table->dropIndex(['student_year']);

      $table->dropColumn([
        'category',
        'month',
        'location',
        'country',
        'logo_path',
        'student_year',
        'subject_id',
      ]);
    });
  }
};
