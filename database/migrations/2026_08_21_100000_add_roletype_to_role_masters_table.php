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
    Schema::table('role_masters', function (Blueprint $table) {
      $table->enum('roletype', ['academic', 'non-academic', 'technical', 'student', 'alumni', 'Administrative', 'AcademicAdministrative', 'NA'])
        ->default('NA')
        ->after('description');
    });

    // Start from a safe default, then classify known academic and technical roles.
    DB::table('role_masters')->update(['roletype' => 'NA']);

    DB::table('role_masters')
      ->where(function ($query) {
        $query->where('slug', 'like', '%faculty%')
          ->orWhere('slug', 'like', '%teacher%')
          ->orWhere('slug', 'like', '%professor%')
          ->orWhere('slug', 'like', '%lecturer%')
          ->orWhere('slug', 'like', '%dean%')
          ->orWhere('slug', 'like', '%principal%')
          ->orWhere('slug', 'like', '%hod%')
          ->orWhere('slug', 'like', '%head-of-department%')
          ->orWhere('slug', 'like', '%vice-principal%')
          ->orWhere('slug', 'like', '%invigilator%')
          ->orWhere('slug', 'like', '%examiner%');
      })
      ->update(['roletype' => 'academic']);

    DB::table('role_masters')
      ->where(function ($query) {
        $query->where('slug', 'like', '%it%')
          ->orWhere('slug', 'like', '%tech%')
          ->orWhere('slug', 'like', '%developer%')
          ->orWhere('slug', 'like', '%engineer%')
          ->orWhere('slug', 'like', '%network%')
          ->orWhere('slug', 'like', '%system-admin%')
          ->orWhere('slug', 'like', '%lab-assistant%')
          ->orWhere('slug', 'like', '%programmer%')
          ->orWhere('slug', 'like', '%support%');
      })
      ->update(['roletype' => 'technical']);

    DB::table('role_masters')
      ->where('slug', 'like', '%student%')
      ->update(['roletype' => 'student']);

    DB::table('role_masters')
      ->where('slug', 'like', '%alumni%')
      ->update(['roletype' => 'alumni']);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('role_masters', function (Blueprint $table) {
      $table->dropColumn('roletype');
    });
  }
};
