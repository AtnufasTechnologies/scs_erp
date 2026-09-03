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
    Schema::create('international_office_activity_type_masters', function (Blueprint $table) {
      $table->id();
      $table->string('title', 150);
      $table->string('slug', 160)->unique();
      $table->text('description')->nullable();
      $table->unsignedInteger('sort_order')->default(100);
      $table->boolean('is_active')->default(1);
      $table->boolean('is_system')->default(0);
      $table->timestamps();

      $table->index('sort_order');
      $table->index('is_active');
    });

    DB::table('international_office_activity_type_masters')->insert([
      [
        'title' => 'Faculty Exchange',
        'slug' => 'faculty-exchange',
        'description' => 'Faculty exchange activities with partner institutions.',
        'sort_order' => 10,
        'is_active' => 1,
        'is_system' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Student Exchange',
        'slug' => 'student-exchange',
        'description' => 'Student mobility and exchange programs.',
        'sort_order' => 20,
        'is_active' => 1,
        'is_system' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Faculty Research Collaboration',
        'slug' => 'faculty-research-collaboration',
        'description' => 'Joint research collaboration between institutions.',
        'sort_order' => 30,
        'is_active' => 1,
        'is_system' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Curriculum Development',
        'slug' => 'curriculum-development',
        'description' => 'Collaborative curriculum design and development.',
        'sort_order' => 40,
        'is_active' => 1,
        'is_system' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Joint Academic Event',
        'slug' => 'joint-academic-event',
        'description' => 'Seminars, conferences, and symposiums organized jointly.',
        'sort_order' => 50,
        'is_active' => 1,
        'is_system' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'title' => 'Study Trip / Symposium',
        'slug' => 'study-trip-symposium',
        'description' => 'Study tours and symposium activities.',
        'sort_order' => 60,
        'is_active' => 1,
        'is_system' => 1,
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
    Schema::dropIfExists('international_office_activity_type_masters');
  }
};
