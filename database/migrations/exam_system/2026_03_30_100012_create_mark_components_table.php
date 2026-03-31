<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('mark_components', function (Blueprint $table) {
      $table->id();
      $table->foreignId('mark_id')->constrained('marks');
      $table->string('component_type'); // internal, external, viva, etc.
      $table->decimal('marks', 6, 2);
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('mark_components');
  }
};
