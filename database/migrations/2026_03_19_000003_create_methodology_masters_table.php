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
    Schema::create('methodology_masters', function (Blueprint $table) {
      $table->id();
      $table->string('name', 100);
      $table->string('description', 255)->nullable();
      $table->boolean('is_active')->default(true);
      $table->integer('sort_order')->default(0);
      $table->timestamps();
    });

    // Insert default methodologies
    DB::table('methodology_masters')->insert([
      ['name' => 'Lecture', 'description' => 'Traditional lecture method', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Discussion', 'description' => 'Interactive discussion with students', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Demonstration', 'description' => 'Practical demonstration', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Practical/Lab', 'description' => 'Hands-on practical or lab work', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Group Work', 'description' => 'Collaborative group activities', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Case Study', 'description' => 'Case study analysis', 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Problem-Based Learning', 'description' => 'Problem-solving approach', 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Flipped Classroom', 'description' => 'Pre-study and in-class activities', 'is_active' => true, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Seminar', 'description' => 'Student presentations and discussions', 'is_active' => true, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Workshop', 'description' => 'Interactive workshop sessions', 'is_active' => true, 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Tutorial', 'description' => 'Small group tutorials', 'is_active' => true, 'sort_order' => 11, 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'Project-Based', 'description' => 'Project-based learning', 'is_active' => true, 'sort_order' => 12, 'created_at' => now(), 'updated_at' => now()],
    ]);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('methodology_masters');
  }
};
