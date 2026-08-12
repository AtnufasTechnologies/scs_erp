<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dean_comparative_reports', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->index();
      $table->string('metric_code');
      $table->string('title');
      $table->text('remarks')->nullable();
      $table->string('status')->default('open');
      $table->timestamps();

      $table->unique(['user_id', 'metric_code'], 'dean_cmp_user_metric_unique');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dean_comparative_reports');
  }
};
