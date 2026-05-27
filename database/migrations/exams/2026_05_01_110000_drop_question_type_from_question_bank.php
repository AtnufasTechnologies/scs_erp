<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('question_bank', function (Blueprint $table) {
      $table->dropColumn('question_type');
    });
  }

  public function down(): void
  {
    Schema::table('question_bank', function (Blueprint $table) {
      $table->enum('question_type', ['MCQ', 'Short Answer', 'Long Answer', 'True/False', 'Fill in the Blank'])
        ->default('Short Answer')
        ->after('question_text');
    });
  }
};
