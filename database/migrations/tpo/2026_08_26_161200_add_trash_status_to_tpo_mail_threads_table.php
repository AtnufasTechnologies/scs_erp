<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    // Keep any unexpected/invalid values in a known state before enum change.
    DB::table('tpo_mail_threads')
      ->whereNotIn('status', ['open', 'closed'])
      ->update(['status' => 'open']);

    DB::statement("ALTER TABLE tpo_mail_threads MODIFY status ENUM('open','closed','trash') NOT NULL DEFAULT 'open'");
  }

  public function down(): void
  {
    // Convert trash rows before removing the enum option.
    DB::table('tpo_mail_threads')
      ->where('status', 'trash')
      ->update(['status' => 'closed']);

    DB::statement("ALTER TABLE tpo_mail_threads MODIFY status ENUM('open','closed') NOT NULL DEFAULT 'open'");
  }
};
