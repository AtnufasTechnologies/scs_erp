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
            $table->unsignedBigInteger('user_code')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('gender');
            $table->string('dob')->nullable();
            $table->string('user_type')->nullable();
            $table->string('community')->nullable();
            $table->unsignedBigInteger('nationality')->nullable();
            $table->string('caste')->nullable();
            $table->unsignedBigInteger('religion')->nullable();
            $table->unsignedBigInteger('department')->nullable();
            $table->unsignedBigInteger('academic_dept_id')->nullable();
            $table->unsignedBigInteger('programme')->nullable();
            $table->unsignedBigInteger('new_program_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('batch')->nullable(); // session
            $table->string('category')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('mail_id')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('rfid')->nullable();
            $table->string('doj')->nullable();
            $table->string('dol')->nullable();
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('admission_date')->nullable();
            $table->string('register_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_left')->default(false);
            $table->string('hsc_percentage')->nullable();
            $table->unsignedBigInteger('blood_group_id')->nullable();
            $table->string('is_physically_challenged')->nullable();
            $table->string('mother_tongue')->nullable();
            $table->string('fr_mobile_no')->nullable();
            $table->string('mr_mobile_no')->nullable();
            $table->string('guardian_mobile_no')->nullable();
            $table->string('fr_occupation')->nullable();
            $table->string('mr_occupation')->nullable();
            $table->string('university_register_no')->nullable();
            $table->string('annual_income')->nullable();
            $table->boolean('is_roman_catholic')->default(false);
            $table->unsignedTinyInteger('current_year')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->string('status')->default('0');
            $table->text('remarks')->nullable();
            $table->index('user_code');
            $table->index('roll_no');
            $table->index('register_no');
            $table->index('batch');
            $table->index('campus_id');
            $table->index('department');
            $table->index('academic_dept_id');
            $table->index('programme');
            $table->index('new_program_id');
            $table->index('is_deleted');
            $table->index('is_left');
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
