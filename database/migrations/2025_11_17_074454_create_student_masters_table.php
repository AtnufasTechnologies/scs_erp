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
        Schema::create('student_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('user_code');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name')->nullable();
            $table->string('gender');
            $table->string('dob')->nullable();
            $table->string('user_type')->nullable();
            $table->string('community')->nullable();
            $table->string('nationality')->nullable();
            $table->string('caste')->nullable();
            $table->string('religion')->nullable();
            $table->string('department')->nullable();
            $table->string('programme')->nullable();
            $table->string('class_id')->nullable();
            $table->string('batch')->nullable(); //session
            $table->string('category')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('mail_id')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('rfid')->nullable();
            $table->string('doj')->nullable();
            $table->string('dol')->nullable();
            $table->string('campus_id')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('admission_date')->nullable();
            $table->string('register_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('is_active')->default(0);
            $table->string('is_deleted')->default(0);
            $table->string('is_left')->default(0);
            $table->string('hsc_percentage')->nullable();
            $table->string('blood_group_id')->nullable();
            $table->string('is_physically_challenged')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('fr_mobile_no')->nullable();
            $table->string('mr_mobile_no')->nullable();
            $table->string('guardian_mobile_no')->nullable();
            $table->string('fr_occupation')->nullable();
            $table->string('mr_occupation')->nullable();
            $table->string('university_register_no')->nullable();
            $table->string('annual_income')->nullable();
            $table->string('is_roman_catholic')->default(0);
            $table->string('status')->default(0);
            $table->string('graduation_year');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_masters');
    }
};
