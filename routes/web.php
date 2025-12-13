<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::get('forgot-password', [LoginController::class, 'forgotPassword']);
Route::post('login', [LoginController::class, 'login']);
Route::post('forgot-password', [LoginController::class, 'sendPasswordReset']);
Route::get('logout', [LoginController::class, 'logout']);
Route::post('payment-success', [FeePaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::post('payment-failure', [FeePaymentController::class, 'paymentFailure'])->name('payment.failure');

Route::group(['prefix' => '/erp'], function () {

    //admin - superuser routes
    Route::group(['prefix' => '/admin'], function () {
        Route::get('dashboard', [AdminController::class, 'index']);
        Route::get('std-master-sonada', [AdminController::class, 'stdMasterSonada']);
        Route::get('std-master-siliguri', [AdminController::class, 'stdMasterSiliguri']);

        Route::get('{id}/std-profile/{rollno}', [AdminController::class, 'stdprofile']);

        //master
        Route::group(['prefix' => '/master'], function () {
            Route::get('campus', [AdminController::class, 'campusMaster']);
            Route::get('programs', [AdminController::class, 'programMaster']);
            Route::get('program-group', [AdminController::class, 'programGroup']);
            Route::post('update-program-group', [AdminController::class, 'updateProgramGroup']);

            Route::get('batch', [AdminController::class, 'batchMaster']);
            Route::post('batch', [AdminController::class, 'addBatch']);
            Route::get('update-adm-batch-status/{id}', [AdminController::class, 'updateAdmBatchStatus']);

            Route::get('hour', [AdminController::class, 'hourMaster']);
            Route::post('hour', [AdminController::class, 'addHour']);
            Route::get('delhour/{id}', [AdminController::class, 'delHour']);

            Route::get('blood-group', [AdminController::class, 'bloodGroupMaster']);
            Route::post('blood-group', [AdminController::class, 'addBloodGroup']);


            Route::get('cognitive-lvl', [AdminController::class, 'cognitiveLvl']);
            Route::post('cognitive-lvl', [AdminController::class, 'addCognitiveLvl']);
            Route::get('del-coglvl/{id}', [AdminController::class, 'delCogLvl']);

            Route::get('departments', [AdminController::class, 'departmentMaster']);


            Route::get('rooms', [AdminController::class, 'roomTypeMaster']);
            Route::post('rooms', [AdminController::class, 'addRoomTypeMaster']);
            Route::post('update-room', [AdminController::class, 'updateRoomTypeMaster']);


            //Subject
            Route::get('subjects', [SubjectController::class, 'index']);
            Route::get('subject-type', [SubjectController::class, 'subjectType']);

            Route::post('subject', [SubjectController::class, 'addSubject']);
            Route::get('view-subject', [SubjectController::class, 'subjectSingle']);

            //lecture halls

            Route::get('lecturehalls', [AdminController::class, 'lectureHalls']);
            Route::post('add-lecture-hall', [AdminController::class, 'addLectureHall']);

            Route::get('semester', [AdminController::class, 'semesters']);

            Route::get('religion', [AdminController::class, 'religionMaster']);
            Route::post('religion', [AdminController::class, 'addReligionMaster']);
            Route::get('del-religion/{id}', [AdminController::class, 'delReligion']);

            Route::get('deanery', [AdminController::class, 'deanery']);
            Route::post('deanery', [AdminController::class, 'addDeanery']);

            Route::get('academic-dept', [AdminController::class, 'academicDept']);
            Route::post('academic-dept', [AdminController::class, 'addAcademicDept']);
            Route::post('connect-academic-dept', [AdminController::class, 'connectAcademicToDept']);
        });

        //account
        Route::group(['prefix' => '/accounts'], function () {
            Route::get('bankinfo', [AdminController::class, 'bankAccounts']);
            Route::post('bankinfo', [AdminController::class, 'addBankInfo']);
            Route::post('update-bankinfo', [AdminController::class, 'updateBankInfo']);

            Route::get('fee-structure', [AdminController::class, 'feeStructure']);
            Route::post('fee-structure', [AdminController::class, 'addFeeStructure']);
            Route::get('unlink-prog-from-feestructure/{id}', [AdminController::class, 'unlinkStdProgram']);
            Route::post('link-prgs-to-feestructure', [AdminController::class, 'linkProgramtoFeeStructure'])->name('link.feestructure.stdprogram');
            Route::get('update/feestructure-status/{id}', [AdminController::class, 'updateFeeStructureStatus']);

            Route::get('del-feecourse-master/{id}', [AdminController::class, 'delFeeCourseMaster']);
            Route::post('fee-structure-groups', [AdminController::class, 'addFeeStructureGroup']);
            Route::get('unlink/fee-structure-group/{id}', [AdminController::class, 'feeStructureGroupUnlink']);

            Route::get('student-fee/{id}', [AdminController::class, 'getFeeStructure']);
            Route::get('fee-course-master', [AdminController::class, 'feeCourseMaster']);
            Route::post('fee-course-master', [AdminController::class, 'addCourseFeeMaster']);
            Route::post('update-fee-course-master', [AdminController::class, 'updateCourseFeeMaster']);
            Route::get('delete-feestructure/{id}', [AdminController::class, 'deleteFeeStructure']);

            Route::get('fee-heads', [AdminController::class, 'feeHeads']);
            Route::post('fee-heads', [AdminController::class, 'addFeeHead']);
            Route::post('update-feehead', [AdminController::class, 'updateFeeHead']);
            Route::get('del-feehead/{id}', [AdminController::class, 'delFeeHead']);
            Route::post('update-head-single', [AdminController::class, 'updateHeadSingle']);
            Route::post('update-fee-structure', [AdminController::class, 'updateFeeStructure']);
            Route::get('del-headpvt/{id}', [AdminController::class, 'delFeeHeadPvt']);
            Route::post('add-coursemaster-group', [AdminController::class, 'addCourseMasterGroup'])->name('link.coursemaster.prggroup');

            Route::get('std-fee-payments', [FeePaymentController::class, 'index']);
            Route::post('manual-payment-payment', [FeePaymentController::class, 'manualFeePayment'])->name('manual.fee.payment');

            Route::get('invoice/{id}', [FeePaymentController::class, 'generateInvoice']);
            Route::get('print-feereciept/{studentId}/{feeId}', [FeePaymentController::class, 'generateFeeReciept']);
        });
    });

    //student
    Route::group(['prefix' => 'student'], function () {
        Route::get('fee-payment', [FeePaymentController::class, 'studentValidation']);
        Route::post('fee-status', [FeePaymentController::class, 'studentFeeStatus']);
        Route::post('fee/create-order', [FeePaymentController::class, 'createOrder']);
    });
});
