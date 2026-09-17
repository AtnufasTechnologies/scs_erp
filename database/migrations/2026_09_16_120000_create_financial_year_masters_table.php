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
    if (!Schema::hasTable('financial_year_masters')) {
      Schema::create('financial_year_masters', function (Blueprint $table) {
        $table->id();
        $table->string('title', 100)->unique();
        $table->date('start_date');
        $table->date('end_date');
        $table->boolean('is_active')->default(false)->index();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('financial_year_masters');
  }
};
