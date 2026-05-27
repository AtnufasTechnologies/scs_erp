<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('student_late_fee_exemptions', function (Blueprint $table) {
      $table->decimal('fixed_late_fee', 10, 2)->nullable()->after('reason');
    });
  }

  public function down()
  {
    Schema::table('student_late_fee_exemptions', function (Blueprint $table) {
      $table->dropColumn('fixed_late_fee');
    });
  }
};
