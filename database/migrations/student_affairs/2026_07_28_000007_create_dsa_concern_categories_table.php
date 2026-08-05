<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dsa_concern_categories', function (Blueprint $table) {
      $table->id();
      $table->string('name', 100)->unique();
      $table->text('description')->nullable();
      $table->unsignedInteger('sort_order')->default(0)->index();
      $table->boolean('is_active')->default(true)->index();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();
    });

    Schema::table('dsa_counselling_cases', function (Blueprint $table) {
      $table->unsignedBigInteger('concern_category_id')->nullable()->after('concern_category')->index();
      $table->foreign('concern_category_id')->references('id')->on('dsa_concern_categories')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::table('dsa_counselling_cases', function (Blueprint $table) {
      $table->dropForeign(['concern_category_id']);
      $table->dropColumn('concern_category_id');
    });

    Schema::dropIfExists('dsa_concern_categories');
  }
};
