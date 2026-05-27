<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_credit_promotions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('regulation_id')->constrained('exam_regulations');
      $table->integer('min_credits');
      $table->string('promotion_rule');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_credit_promotions');
  }
};
