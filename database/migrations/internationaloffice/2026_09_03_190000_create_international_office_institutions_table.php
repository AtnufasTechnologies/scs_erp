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
    Schema::create('international_office_institutions', function (Blueprint $table) {
      $table->id();
      $table->string('institution_name');
      $table->string('contact_person')->nullable();
      $table->string('contact_number', 100)->nullable();
      $table->string('email')->nullable();
      $table->text('address')->nullable();
      $table->boolean('has_mou')->default(true);
      $table->string('mou_document_path')->nullable();
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('created_by_user_id')->nullable();
      $table->timestamps();

      $table->index('institution_name');
      $table->index('has_mou');
      $table->index('created_by_user_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('international_office_institutions');
  }
};
