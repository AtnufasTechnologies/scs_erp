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
        Schema::table('faculties', function (Blueprint $table) {
            $table->string('responsibility', 255)->nullable()->after('NATIONALITY')->comment('Role like event coordinator, HR, programmer coordinator, etc.');
            $table->integer('paper_publications_count')->unsigned()->default(0)->after('responsibility')->comment('Number of paper publications');
            $table->string('orcid_id', 50)->nullable()->after('paper_publications_count')->comment('ORCID ID for research identification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn(['responsibility', 'paper_publications_count', 'orcid_id']);
        });
    }
};
