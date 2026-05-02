<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_events', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->text('description')->nullable();
      $table->date('start_date');
      $table->date('end_date');
      $table->string('venue')->nullable();
      $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
      $table->decimal('total_budget', 12, 2)->default(0);
      $table->string('banner_image')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_events');
  }
};
