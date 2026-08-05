<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('api_metrix_components', function (Blueprint $table) {
      if (!Schema::hasColumn('api_metrix_components', 'verifier_role_master_id')) {
        $table->unsignedBigInteger('verifier_role_master_id')->nullable()->after('score');
        $table->index('verifier_role_master_id', 'api_metrix_components_verifier_role_idx');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('api_metrix_components', function (Blueprint $table) {
      if (Schema::hasColumn('api_metrix_components', 'verifier_role_master_id')) {
        $table->dropIndex('api_metrix_components_verifier_role_idx');
        $table->dropColumn('verifier_role_master_id');
      }
    });
  }
};
