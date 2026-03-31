<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::dropIfExists('exam_sessions');
    Schema::dropIfExists('exam_regulations');
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_sessions');
    Schema::dropIfExists('exam_regulations');
  }
};
