<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('shift_masters', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->string('slug')->unique();
      $table->boolean('is_active')->default(1);
      $table->boolean('is_system')->default(0);
      $table->unsignedInteger('sort_order')->default(0);
      $table->timestamps();
    });

    DB::table('shift_masters')->insert([
      [
        'title' => 'Morning',
        'slug' => 'morning',
        'is_active' => 1,
        'is_system' => 1,
        'sort_order' => 10,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Day',
        'slug' => 'day',
        'is_active' => 1,
        'is_system' => 1,
        'sort_order' => 20,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Common',
        'slug' => 'common',
        'is_active' => 1,
        'is_system' => 1,
        'sort_order' => 30,
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ]);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('shift_masters');
  }
};
