<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('payment_batch_items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('faculty_remuneration_id');
      $table->timestamps();

      $table->foreign('batch_id')->references('id')->on('payment_batches')->onDelete('cascade');
      $table->foreign('faculty_remuneration_id')->references('id')->on('faculty_remunerations')->onDelete('cascade');
      $table->index(['batch_id', 'faculty_remuneration_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('payment_batch_items');
  }
};
