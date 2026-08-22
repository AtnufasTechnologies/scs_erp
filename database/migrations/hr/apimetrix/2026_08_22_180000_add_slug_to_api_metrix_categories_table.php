<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('api_metrix_categories', function (Blueprint $table) {
      $table->string('slug')->nullable()->after('title');
    });

    $existingSlugs = [];

    DB::table('api_metrix_categories')
      ->select('id', 'title')
      ->orderBy('id')
      ->chunkById(100, function ($rows) use (&$existingSlugs) {
        foreach ($rows as $row) {
          $base = Str::slug((string) $row->title);
          if ($base === '') {
            $base = 'category';
          }

          $slug = $base;
          $suffix = 2;
          while (in_array($slug, $existingSlugs, true)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
          }

          $existingSlugs[] = $slug;

          DB::table('api_metrix_categories')
            ->where('id', $row->id)
            ->update(['slug' => $slug]);
        }
      }, 'id');

    Schema::table('api_metrix_categories', function (Blueprint $table) {
      $table->unique('slug');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('api_metrix_categories', function (Blueprint $table) {
      $table->dropUnique('api_metrix_categories_slug_unique');
      $table->dropColumn('slug');
    });
  }
};
