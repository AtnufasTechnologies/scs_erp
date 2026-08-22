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
    Schema::table('api_metrix_subcomponents', function (Blueprint $table) {
      if (!Schema::hasColumn('api_metrix_subcomponents', 'verifier_role_master_id')) {
        $table->unsignedBigInteger('verifier_role_master_id')->nullable()->after('score');
        $table->index('verifier_role_master_id', 'api_metrix_subcomponents_verifier_role_idx');
      }
    });

    $iqacRoleId = DB::table('role_masters')
      ->whereRaw('LOWER(role_name) = ?', ['iqac'])
      ->orWhereRaw('LOWER(slug) = ?', ['iqac'])
      ->orWhereRaw('LOWER(role_name) like ?', ['%iqac%'])
      ->orWhereRaw('LOWER(slug) like ?', ['%iqac%'])
      ->orderBy('id')
      ->value('id');

    if (!empty($iqacRoleId)) {
      DB::table('api_metrix_subcomponents')
        ->whereNull('verifier_role_master_id')
        ->update(['verifier_role_master_id' => $iqacRoleId]);
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('api_metrix_subcomponents', function (Blueprint $table) {
      if (Schema::hasColumn('api_metrix_subcomponents', 'verifier_role_master_id')) {
        $table->dropIndex('api_metrix_subcomponents_verifier_role_idx');
        $table->dropColumn('verifier_role_master_id');
      }
    });
  }
};
