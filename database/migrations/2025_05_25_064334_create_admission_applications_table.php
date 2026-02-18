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
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('registration_id');
            $table->integer('application_code')->nullable();
            $table->string('photo')->nullable();
            $table->string('department')->nullable();
            $table->string('course')->nullable();
            $table->date('dob')->nullable();
            $table->string('bloodgroup')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('mothertongue')->nullable();
            $table->string('phychallenged')->nullable();
            $table->string('caste')->nullable();
            $table->smallInteger('has_laptop')->nullable();
            $table->smallInteger('from_teaestate')->nullable();
            $table->string('baptism')->nullable();
            $table->string('adhaar')->nullable();
            $table->string('national_id_proof')->nullable();

            $table->string('father_name', 255)->nullable();
            $table->string('father_contact', 15)->nullable();
            $table->string('father_occupation', 255)->nullable();
            $table->string('father_qualification', 255)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('mother_contact', 15)->nullable();
            $table->string('mother_occupation', 255)->nullable();
            $table->string('mother_qualification', 255)->nullable();
            $table->integer('income')->nullable();
            $table->string('guardian_name', 255)->nullable();
            $table->string('guardian_contact', 15)->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('district', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->text('local_address')->nullable();
            $table->string('local_district', 255)->nullable();
            $table->string('local_city', 255)->nullable();
            $table->string('local_pincode', 20)->nullable();
            $table->string('local_state', 255)->nullable();

            // Class 10 Details
            $table->string('institution10')->nullable();
            $table->string('rollno10', 255)->nullable();
            $table->string('board10', 255)->nullable();
            $table->integer('passingyear10')->nullable();
            $table->string('certificate10')->nullable(); // stored path/filename

            // Class 10 Subjects
            $table->string('subject10_1', 255)->nullable();
            $table->integer('score10_1')->nullable();

            $table->string('subject10_2', 255)->nullable();
            $table->integer('score10_2')->nullable();

            $table->string('subject10_3', 255)->nullable();
            $table->integer('score10_3')->nullable();

            $table->string('subject10_4', 255)->nullable();
            $table->integer('score10_4')->nullable();

            $table->string('subject10_5', 255)->nullable();
            $table->integer('score10_5')->nullable();


            // Class 12 Details
            $table->string('institution12')->nullable();
            $table->string('rollno12', 255)->nullable();
            $table->string('board12', 255)->nullable();
            $table->integer('passingyear12')->nullable();
            $table->string('certificate12')->nullable(); // stored path/filename

            // Class 12 Subjects
            $table->string('subject12_1', 255)->nullable();
            $table->integer('score12_1')->nullable();

            $table->string('subject12_2', 255)->nullable();
            $table->integer('score12_2')->nullable();

            $table->string('subject12_3', 255)->nullable();
            $table->integer('score12_3')->nullable();

            $table->string('subject12_4', 255)->nullable();
            $table->integer('score12_4')->nullable();

            //College and SGPA Details
            $table->string('college_name')->nullable();
            $table->string('university_name')->nullable();
            $table->integer('graduating_year')->nullable();
            $table->string('graduating_rollno')->nullable();
            $table->string('college_marksheet')->nullable();

            $table->string('sgpa1')->nullable();
            $table->string('sgpa2')->nullable();
            $table->string('sgpa3')->nullable();
            $table->string('sgpa4')->nullable();
            $table->string('sgpa5')->nullable();
            $table->string('sgpa6')->nullable();


            //Gateway Details
            $table->string('gateway_type')->nullable();
            $table->string('payment_gateway_ref')->nullable();
            $table->string('captured_amount')->nullable();
            $table->string('hash')->nullable();
            $table->string('payment_gateway_status')->nullable();
            $table->text('msg')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
