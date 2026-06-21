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
    Schema::create('api_publications', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('faculty_id');
      $table->unsignedBigInteger('academic_year_id');
      $table->enum('publication_type', [
        'journal_article',
        'book',
        'book_chapter',
        'research_project',
        'case_study',
        'patent',
        'invited_lecture'
      ]);
      $table->string('title');
      $table->string('journal_book_name')->nullable();
      $table->string('isbn_issn')->nullable();
      $table->string('doi')->nullable();
      $table->text('authors')->nullable();
      $table->date('publication_date')->nullable();
      $table->string('document_path')->nullable();
      $table->decimal('api_score', 5, 2)->default(0);
      $table->timestamps();
      $table->softDeletes();

      $table->index('faculty_id');
      $table->index('academic_year_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('api_publications');
  }
};
