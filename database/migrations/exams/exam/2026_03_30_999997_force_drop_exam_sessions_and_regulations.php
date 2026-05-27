<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    // Skip - these tables are needed by subsequent migrations
    // This migration was intended for cleanup but breaks the migration chain
  }

  public function down(): void
  {
    // No action needed
  }
};
