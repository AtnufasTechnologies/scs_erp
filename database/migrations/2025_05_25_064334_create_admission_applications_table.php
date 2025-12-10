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
            $table->string('application_id');
            $table->integer('user_id');
            $table->integer('reg_id'); //gives you program UG or PG / and Campus
            $table->integer('dept_id');
            $table->integer('course_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->date('dob');
            $table->string('bloodgroup');
            $table->string('gender');
            $table->integer('religion_id');
            $table->string('baptism')->nullable();
            $table->string('mothertongue');
            $table->enum('physically_challanged', ['No', 'Yes'])->default('No');
            $table->string('pic');
            $table->string('fname')->nullable();
            $table->string('foccupation')->nullable();
            $table->string('fcontact')->nullable();
            $table->string('mname')->nullable();
            $table->string('moccupation')->nullable();
            $table->string('mcontact')->nullable();
            $table->string('gname')->nullable();
            $table->string('gcontact')->nullable();
            $table->integer('monthly_income');
            $table->text('permanent_address');
            $table->integer('per_pin');
            $table->text('local_address');
            $table->integer('loc_pin');
            $table->text('institution10');
            $table->text('institution12');
            $table->string('sub1');
            $table->string('sub2');
            $table->string('sub3');
            $table->string('sub4');
            $table->string('sub5');
            $table->string('score1');
            $table->string('score2');
            $table->string('score3');
            $table->string('score4');
            $table->string('score5');
            $table->string('certificate_10');
            $table->string('certificate_12');
            $table->string('payment_gateway_id')->nullable(); //gateway
            $table->integer('captured_amount')->nullable(); //gateway
            $table->string('payment_gateway_status')->nullable(); //gateway status check    
            $table->integer('amount_refunded')->nullable(); //gateway
            $table->string('captured_currency')->nullable(); //gateway
            $table->string('mode')->nullable(); //gateway
            $table->text('hash')->nullable(); //gateway
            $table->string('msg')->nullable(); //gateway
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
