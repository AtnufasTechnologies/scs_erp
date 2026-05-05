<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Skip - exam_sessions table is needed by subsequent migrations
  }

  public function down(): void
  {
    // No action needed
  }
};
