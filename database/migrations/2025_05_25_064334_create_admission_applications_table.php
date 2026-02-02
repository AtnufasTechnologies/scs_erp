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
            $table->string('laptop', 20)->nullable();
            $table->string('teaestate', 20)->nullable();
            $table->string('baptism')->nullable();

            $table->string('father_name', 255)->nullable();
            $table->string('father_contact', 15)->nullable();
            $table->string('father_occupation', 255)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('mother_contact', 15)->nullable();
            $table->string('mother_occupation', 255)->nullable();
            $table->integer('income')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('district', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->text('local_address')->nullable();
            $table->string('local_district', 255)->nullable();
            $table->string('local_city', 255)->nullable();
            $table->string('local_pincode', 20)->nullable();

            // Class 10 Details
            $table->string('institution10')->nullable();
            $table->string('rollno10', 255)->nullable();
            $table->string('board10', 255)->nullable();
            $table->integer('passingyear10')->nullable();
            $table->string('certificate10')->nullable(); // stored path/filename
            $table->integer('percentage10')->nullable();
            $table->integer('fullmark10')->nullable();
            $table->integer('passmark10')->nullable();

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
            $table->integer('percentage12')->nullable();
            $table->integer('fullmark12')->nullable();
            $table->integer('passmark12')->nullable();
            // Class 12 Subjects
            $table->string('subject12_1', 255)->nullable();
            $table->integer('score12_1')->nullable();

            $table->string('subject12_2', 255)->nullable();
            $table->integer('score12_2')->nullable();

            $table->string('subject12_3', 255)->nullable();
            $table->integer('score12_3')->nullable();

            $table->string('subject12_4', 255)->nullable();
            $table->integer('score12_4')->nullable();

            $table->string('subject12_5', 255)->nullable();
            $table->integer('score12_5')->nullable();


            //SGPA Details
            $table->string('sem1')->nullable();
            $table->string('sgpa1')->nullable();
            $table->integer('percentage1')->nullable();
            $table->string('grade1', 10)->nullable();

            $table->string('sem2')->nullable();
            $table->string('sgpa2')->nullable();
            $table->integer('percentage2')->nullable();
            $table->string('grade2', 10)->nullable();

            $table->string('sem3')->nullable();
            $table->string('sgpa3')->nullable();
            $table->integer('percentage3')->nullable();
            $table->string('grade3', 10)->nullable();

            $table->string('sem4')->nullable();
            $table->string('sgpa4')->nullable();
            $table->integer('percentage4')->nullable();
            $table->string('grade4', 10)->nullable();

            $table->string('sem5')->nullable();
            $table->string('sgpa5')->nullable();
            $table->integer('percentage5')->nullable();
            $table->string('grade5', 10)->nullable();

            $table->string('sem6')->nullable();
            $table->string('sgpa6')->nullable();
            $table->integer('percentage6')->nullable();
            $table->string('grade6', 10)->nullable();

            //Gateway Details
            $table->string('gateway_type')->nullable();
            $table->string('payment_gateway_ref')->nullable();
            $table->string('captured_amount')->nullable();
            $table->string('hash')->nullable();
            $table->string('payment_gateway_status')->nullable();

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
