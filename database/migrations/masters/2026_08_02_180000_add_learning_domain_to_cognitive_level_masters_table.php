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
    Schema::table('cognitive_level_masters', function (Blueprint $table) {
      if (!Schema::hasColumn('cognitive_level_masters', 'learning_domain')) {
        $table->string('learning_domain', 32)->default('Cognitive')->after('fullname');
      }

      if (!Schema::hasColumn('cognitive_level_masters', 'taxonomy_framework')) {
        $table->string('taxonomy_framework', 32)->default('RBT')->after('learning_domain');
      }
    });

    // Normalize existing levels as Cognitive + RBT.
    DB::table('cognitive_level_masters')
      ->whereNull('learning_domain')
      ->orWhere('learning_domain', '')
      ->update([
        'learning_domain' => 'Cognitive',
        'taxonomy_framework' => 'RBT',
      ]);

    $levels = [
      // Psychomotor - Dave
      ['shortname' => 'IM', 'fullname' => 'Imitation', 'learning_domain' => 'Psychomotor', 'taxonomy_framework' => 'Dave'],
      ['shortname' => 'MA', 'fullname' => 'Manipulation', 'learning_domain' => 'Psychomotor', 'taxonomy_framework' => 'Dave'],
      ['shortname' => 'PR', 'fullname' => 'Precision', 'learning_domain' => 'Psychomotor', 'taxonomy_framework' => 'Dave'],
      ['shortname' => 'AR', 'fullname' => 'Articulation', 'learning_domain' => 'Psychomotor', 'taxonomy_framework' => 'Dave'],
      ['shortname' => 'NA', 'fullname' => 'Naturalization', 'learning_domain' => 'Psychomotor', 'taxonomy_framework' => 'Dave'],

      // Affective - Krathwohl
      ['shortname' => 'RE', 'fullname' => 'Receiving', 'learning_domain' => 'Affective', 'taxonomy_framework' => 'Krathwohl'],
      ['shortname' => 'RS', 'fullname' => 'Responding', 'learning_domain' => 'Affective', 'taxonomy_framework' => 'Krathwohl'],
      ['shortname' => 'VA', 'fullname' => 'Valuing', 'learning_domain' => 'Affective', 'taxonomy_framework' => 'Krathwohl'],
      ['shortname' => 'OR', 'fullname' => 'Organization', 'learning_domain' => 'Affective', 'taxonomy_framework' => 'Krathwohl'],
      ['shortname' => 'CH', 'fullname' => 'Characterization', 'learning_domain' => 'Affective', 'taxonomy_framework' => 'Krathwohl'],
    ];

    foreach ($levels as $level) {
      $exists = DB::table('cognitive_level_masters')
        ->whereRaw('UPPER(TRIM(fullname)) = ?', [strtoupper(trim($level['fullname']))])
        ->whereRaw('UPPER(TRIM(learning_domain)) = ?', [strtoupper(trim($level['learning_domain']))])
        ->exists();

      if (!$exists) {
        DB::table('cognitive_level_masters')->insert([
          'shortname' => $level['shortname'],
          'fullname' => $level['fullname'],
          'learning_domain' => $level['learning_domain'],
          'taxonomy_framework' => $level['taxonomy_framework'],
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Keep seeded rows to avoid accidental data loss; only drop the added metadata columns.
    Schema::table('cognitive_level_masters', function (Blueprint $table) {
      if (Schema::hasColumn('cognitive_level_masters', 'taxonomy_framework')) {
        $table->dropColumn('taxonomy_framework');
      }

      if (Schema::hasColumn('cognitive_level_masters', 'learning_domain')) {
        $table->dropColumn('learning_domain');
      }
    });
  }
};
