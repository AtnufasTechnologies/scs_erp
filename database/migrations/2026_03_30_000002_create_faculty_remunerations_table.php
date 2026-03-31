<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('faculty_remunerations', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('faculty_id');
      $table->enum('duty_type', ['invigilation', 'evaluation', 'moderation']);
      $table->unsignedBigInteger('reference_id'); // links to duty table (polymorphic or specific)
      $table->integer('quantity');
      $table->decimal('rate', 10, 2);
      $table->decimal('total_amount', 12, 2);
      $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
      $table->timestamp('generated_at')->nullable();
      $table->timestamps();

      $table->index(['faculty_id', 'duty_type', 'status']);
      $table->foreign('faculty_id')->references('id')->on('faculty')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('faculty_remunerations');
  }
};
