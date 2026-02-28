<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TimetableController;
use App\Models\Department;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
Route::get('verify-mail-reset-token/{id}', [LoginController::class, 'verifyResetToken']);
Route::post('update-password', [LoginController::class, 'updatePassword'])->name('update.password');
Route::get('logout', [LoginController::class, 'logout']);

Route::group(['prefix' => '/erp'], function () {

    //admin - superuser routes
    Route::group(['prefix' => '/admin'], function () {
        Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('std-master-sonada', [AdminController::class, 'stdMasterSonada']);
        Route::get('std-master-siliguri', [AdminController::class, 'stdMasterSiliguri']);
        Route::get('faculty-master', [AdminController::class, 'facultyMaster']);
        Route::get('{id}/std-profile/{rollno}', [AdminController::class, 'stdprofile']);
        Route::post('update/faculty', [AdminController::class, 'updateFaculty']);

        //master
        Route::group(['prefix' => '/master'], function () {
            Route::get('campus', [AdminController::class, 'campusMaster']);
            Route::get('programs', [AdminController::class, 'programMaster']);
            Route::get('program-group', [AdminController::class, 'programGroup']);
            Route::post('update-program-group', [AdminController::class, 'updateProgramGroup']);
            Route::post('add-stream-combination', [AdminController::class, 'addStreamCombination'])->name('add.stream.campus.combination');

            Route::get('stream-master', [AdminController::class, 'streamMaster'])->name('stream.master');
            Route::post('stream-master', [AdminController::class, 'addStreamMaster'])->name('add.stream.master');

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


            //Subject (Also known as Academic Departments)
            Route::get('subjects', [SubjectController::class, 'index']);
            Route::get('subject-type', [SubjectController::class, 'subjectType']);

            Route::post('subject', [SubjectController::class, 'addSubject']);
            Route::get('view-subject', [SubjectController::class, 'subjectSingle'])->name('admin.dept-view');
            Route::get('delete-subject/{id}', [SubjectController::class, 'deleteSubject']);
            Route::post('link-student-programs', [SubjectController::class, 'linkStdPrograms'])->name('add.programs.to.subject');
            Route::post('add-subject-semester', [SubjectController::class, 'addSemesterToSubject'])->name('add.semester.to.subject');
            Route::post('update-academic-dept/{id}', [SubjectController::class, 'updateAcademicDept'])->name('admin.master.update.academic-dept');
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
            // Late Fee Revenue Report
            Route::get('late-fee-revenue-report', [FeePaymentController::class, 'lateFeeRevenueReport'])->name('late-fee-revenue-report');
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
            Route::get('latefee', [AdminController::class, 'latefee']);

            Route::get('std-fee-payments', [FeePaymentController::class, 'index'])->name('student.fee.payments');
            Route::post('manual-payment-payment', [FeePaymentController::class, 'manualFeePayment'])->name('manual.fee.payment');

            Route::get('invoice/{id}', [FeePaymentController::class, 'generateInvoice']);
            Route::get('print-feereciept/{studentId}/{feeId}', [FeePaymentController::class, 'generateFeeReciept']);
            Route::get('all-payments', [FeePaymentController::class, 'allPayments'])->name('all.payments');
            Route::get('transaction-info/{id}', [FeePaymentController::class, 'showSuccessPage'])->name('transaction.info');
            Route::get('verify-transaction/{id}', [FeePaymentController::class, 'verifyTransaction']);
            Route::get('defaulters-list', [FeePaymentController::class, 'defaultersList'])->name('defaulters-list');

            // Late Fee Exemption Management
            Route::get('late-fee-exemptions', [FeePaymentController::class, 'lateFeeExemptionIndex'])->name('late.fee.exemptions');
            Route::post('late-fee-exemption/grant', [FeePaymentController::class, 'grantLateFeeExemption'])->name('grant.late.fee.exemption');
            Route::post('late-fee-exemption/{id}/revoke', [FeePaymentController::class, 'revokeLateFeeExemption'])->name('revoke.late.fee.exemption');

            Route::get('defaulters-list', [FeePaymentController::class, 'defaultersList'])->name('defaulters-list');
            Route::get('delete-user-permission/{id}', [AdminController::class, 'deleteUserPermission'])->name('admin.user-access.delete-permission');

            Route::get('admission-application-fee', [FeePaymentController::class, 'admissionApplicationFee'])->name('admin.accounts.admission-application-fee');
        });

        //Academics
        Route::group(['prefix' => '/academics'], function () {
            Route::post('add/subject-semester', [SubjectController::class, 'addSemesterToSubject'])->name('add.semester.to.subject');
            Route::post('add/subject-syllabus', [SubjectController::class, 'addSyllabus'])->name('add.syllabus.to.semester');
            Route::get('all-course-combinations', [SubjectController::class, 'deptAllCourseCombinations'])->name('dept.all.course-combination');
            Route::get('program-course-master', [SubjectController::class, 'programCourseMaster'])->name('program-course.master');
            Route::get('admin/course-master', [SubjectController::class, 'adminCourseMaster'])->name('admin.course-master');
            Route::get('delete-combination/{id}', [SubjectController::class, 'deleteCombination'])->name('admin.delete.combination');
            Route::get('student-program-master', [SubjectController::class, 'studentProgramMaster'])->name('admin.student-program-master');
            Route::post('add/new/student-program', [SubjectController::class, 'addNewStudentProgram'])->name('admin.add.new.student-program');
            Route::post('update/student-program/{id}', [SubjectController::class, 'updateStudentProgram'])->name('admin.update.student-program');
        });

        //user access management
        Route::group(['prefix' => '/access-control'], function () {
            Route::get('access-management', [AdminController::class, 'userList'])->name('admin.user.management');
            Route::post('newuser', [AdminController::class, 'createNewUser'])->name('add.newuser');
            Route::post('update-permission', [AdminController::class, 'updatePermission'])->name('update.user.permission');
            Route::get('remove-user-permission/{id}', [AdminController::class, 'removeUserPermission']);
            Route::get('dept-access', [AccessController::class, 'deptAccess'])->name('dept.erp.access-list');
            Route::post('assign-dept-access', [AccessController::class, 'assignDeptAccess'])->name('dept.erp.grant-access');
            Route::get('getprogramsbydepartment', [AdmissionController::class, 'getProgramsByDepartment']);
            Route::get('delete-user-access/{id}', [AdminController::class, 'deleteUserAccess'])->name('admin.user-access.delete');
            Route::get('user-types', [AdminController::class, 'userTypes'])->name('admin.user-types');
            Route::post('add-usertype', [AdminController::class, 'addUserType'])->name('admin.add.usertype');

            Route::get('menu-access-types', [AdminController::class, 'menuAccessTypes'])->name('admin.menu-access-types');
            Route::post('add-menu-access-type', [AdminController::class, 'addMenuAccessType'])->name('admin.add.menu-access-type');
            Route::get('revoke-access/{id}', [AccessController::class, 'revokeDeptAccess'])->name('dept.erp.revoke-access');
            //impersonate user
            Route::get('impersonate/{id}', [AccessController::class, 'impersonateUser'])->name('impersonate.user');
            Route::get('sms-templates', [AccessController::class, 'smsTemplates'])->name('admin.sms.templates');
            Route::post('sms-templates', [AccessController::class, 'smsTemplateStore'])->name('sms.template.store');
            Route::get('sms-template/delete/{id}', [AccessController::class, 'smsTemplateDelete'])->name('sms.template.delete');
        });

        //admission routes Admin
        Route::group(['prefix' => '/admission'], function () {

            Route::get('registrations/{type}', [AdmissionController::class, 'admissionRegistrations'])->name('admission.registration');
            //UG 
            Route::get('ug-applications', [AdmissionController::class, 'ugApplications'])->name('admission.ug.applications');
            Route::get('application-single/{id}', [AdmissionController::class, 'ugApplicationSingle'])->name('admin.admission.ug.application-single');
            Route::get('phase1', [AdmissionController::class, 'ugPhase1Registrations'])->name('admission.ug.phase1');
            Route::get('phase2', [AdmissionController::class, 'ugPhase2Registrations'])->name('admission.ug.phase2');
            Route::put('phase2/update-status/{id}', [AdmissionController::class, 'updateUgPhase2Status'])->name('admission.ug.phase2.update-status');
            //controls
            Route::post('send-phase1-notification-single', [AdmissionController::class, 'sendPhase1NotificationSingle'])->name('send.phase1.notification.single');
            Route::post('send-phase1-notification', [AdmissionController::class, 'sendPhase1BulkNotification'])->name('send.phase1.notification');
            Route::put('phase1/update-status/{id}', [AdmissionController::class, 'updateUgPhase1Status'])->name('admission.ug.phase1.update-status');
            Route::put('phase1/program-shift/{id}', [AdmissionController::class, 'shiftUgProgram'])->name('admission.ug.phase1.shift-program');
            Route::post('send-phase2-notification', [AdmissionController::class, 'sendPhase2BulkNotification'])->name('send.phase2.notification');
            //Settings
            Route::get('settings', [AdmissionController::class, 'admissionSettings'])->name('admission.settings');
            Route::post('update-admission-settings-ug', [AdmissionController::class, 'updateAdmissionSettingsUg'])->name('update.admission.settings.ug');
            Route::post('update-admission-settings-pg', [AdmissionController::class, 'updateAdmissionSettingsPg'])->name('update.admission.settings.pg');

            //PG
            Route::get('pg-applications', [AdmissionController::class, 'pgApplications'])->name('admission.pg.applications');

            //Edit Application
            Route::get('edit-application/{id}', [AdmissionController::class, 'showEditApplication'])->name('admission.edit.application');
            Route::put('update-application/{id}', [AdmissionController::class, 'updateUgApplication'])->name('admission.update.ug.application');

            Route::get('otp-status-updater/{id}', [AdmissionController::class, 'updateOtpStatus'])->name('otp.status.update');
            Route::get('get-departments/{id}', [AdmissionController::class, 'getDepartmentsByCampusProgram'])->name('get.departments.by.campusprogram');
            Route::get('get-programs-by-department/{id}/{code}', [AdmissionController::class, 'getCoursesByDepartment'])->name('get.programs.bydepartment');

            Route::post('campus-shift', [AdmissionController::class, 'applicantCampusShift'])->name('applicant.campus.shifter');
            Route::get('verify-payment/{id}', [AdmissionController::class, 'verifyPayment'])->name('admission.verify.payment');
            Route::post('update-application-payment', [AdmissionController::class, 'updateApplicationPayment'])->name('admission.update.payment');
            //EASE BUZZ WEBHOOK
            Route::post('admission-payment-webhook-easebuzz', [AdmissionController::class, 'webhookEasebuzz'])->name('admission.payment.webhook.easebuzz');
        });
    });

    //admission student routes
    Route::group(['prefix' => '/new-admission'], function () {
        Route::get('login', [AdmissionController::class, 'login'])->name('new.admission.login');
        Route::get('registration', [AdmissionController::class, 'index'])->name('new.admission.registration');
        Route::post('registration', [AdmissionController::class, 'admissionRegistration'])->name('admission.registration.submit');
        Route::post('applicant-login', [AdmissionController::class, 'applicantLogin'])->name('applicant.login');
        Route::get('getmainprograms', [AdmissionController::class, 'getMainPrograms']);
        Route::get('captcha-refresh', [AdmissionController::class, 'refreshCaptcha'])->name('captcha.refresh');
        Route::get('otp-verification', [AdmissionController::class, 'showOtpVerificationPage'])->name('otp.verification.page');
        Route::post('/otp/verify', [AdmissionController::class, 'verify'])->name('otp.verify');
        Route::post('/otp/resend', [AdmissionController::class, 'otpResend'])->name('otp.resend');
        Route::get('logout', [AdmissionController::class, 'logout'])->name('admission.apply.logout');
        Route::get('application', [AdmissionController::class, 'showApplicationPage'])->name('admission.apply.application');
        Route::post('submit-ug-application-form', [AdmissionController::class, 'ugApplicationSubmit'])->name('submit.ug.application.form');
        Route::get('getcombinations-bydepartment', [AdmissionController::class, 'getCombinationsByDepartment'])->name('get.combinations.bydepartment');
        Route::get('payment-checkout', [AdmissionController::class, 'paymentCheckout'])->name('admission.payment.checkout');
        Route::post('payment-process', [AdmissionController::class, 'initateEaseBuzzPayment'])->name('admission.payment.process');

        Route::post('payment-success', [AdmissionController::class, 'paymentSuccess'])->name('admission.payment.success');
        Route::post('payment-failure', [AdmissionController::class, 'paymentFailure'])->name('admission.payment.failure');
        Route::get('application-success-page', [AdmissionController::class, 'showSuccessPage'])->name('admission.application.success');

        //forgot password
        Route::get('forgot-password', [AdmissionController::class, 'showForgotPasswordForm'])->name('admission.forgot.password');
        Route::post('forgot-password', [AdmissionController::class, 'handleForgotPassword'])->name('admission.handle.forgot.password');
        Route::get('reset-password/{token}', [AdmissionController::class, 'showResetPasswordPage'])->name('admission.reset.password');
        Route::post('reset-password', [AdmissionController::class, 'handleResetPassword'])->name('admission.handle.reset.password');
        //Downloads
        Route::get('download-application-form/{code}', [AdmissionController::class, 'downloadApplicationForm'])->name('download.admission.application-form');
        Route::get('download-payment-invoice/{code}', [AdmissionController::class, 'downloadPaymentInvoice'])->name('download.admission.payment-invoice');

        //PG Application 
        Route::post('submit-pg-application-form', [AdmissionController::class, 'pgApplicationSubmit'])->name('submit.pg.application.form');
    });



    //student
    Route::group(['prefix' => 'student'], function () {
        Route::get('fee-payment', [FeePaymentController::class, 'studentValidation']);
        Route::post('fee-status', [FeePaymentController::class, 'studentFeeStatus']);
        Route::post('fee-payment', [FeePaymentController::class, 'createOrder']);
        Route::get('fee-status', [FeePaymentController::class, 'studentValidation']);

        Route::post('payment-success', [FeePaymentController::class, 'paymentSuccess'])->name('payment.success');
        Route::post('payment-failure', [FeePaymentController::class, 'paymentFailure'])->name('payment.failure');
        Route::get('transaction-success/{id}', [FeePaymentController::class, 'showSuccessPage']);
        Route::get('transaction-success/{id}/download-pdf', [FeePaymentController::class, 'downloadInvoice']);
    });

    //admission
    Route::group(['prefix' => 'admission'], function () {
        Route::get('registration', [AdmissionController::class, 'index']);
        Route::post('registration', [AdmissionController::class, 'admissionRegistration'])->name('admission.registration.submit');
        Route::post('applicant-login', [AdmissionController::class, 'applicantLogin'])->name('applicant.login');
        Route::get('getmainprograms', [AdmissionController::class, 'getMainPrograms']);
    });

    // API routes for late fee exemptions
    Route::group(['prefix' => '/api'], function () {
        Route::get('students/search', [FeePaymentController::class, 'searchStudents']);
        Route::get('students/{id}/fee-structures', [FeePaymentController::class, 'getStudentFeeStructures']);
        Route::get('students/{rollno}/unpaid-fees', [FeePaymentController::class, 'getStudentUnpaidFees']);
    });

    Route::group(['prefix' => '/deptartment'], function () {
        Route::get('dashboard', [SubjectController::class, 'departmentDashboard'])->name('department.dashboard');
        Route::delete('combination/{id}/delete', [SubjectController::class, 'deleteCombination'])->name('department.combination.delete');
        Route::get('course-master/{id}/{slug}', [SubjectController::class, 'courseMaster'])->name('department.course.master');
        Route::post('my-course-master', [SubjectController::class, 'addCourseMaster'])->name('department.add.course.master');
        Route::delete('course-master/{id}/delete', [SubjectController::class, 'deleteCourseMaster'])->name('department.course.delete');
        Route::get('delete-semester/{id}', [SubjectController::class, 'deleteSemesterFromSubject'])->name('department.delete.subject.semester');
        Route::post('add-faculty-master', [SubjectController::class, 'addFacultyMasterToSubject'])->name('dept.add.faculty.master');
        Route::delete('delete-faculty-master/{id}', [SubjectController::class, 'deleteFacultyMasterFromSubject'])->name('department.faculty.delete');

        // Faculty Timetable

        //timetable
        Route::get('timetable/{id}', [TimetableController::class, 'index'])->name('department.timetable');
        Route::get('timetable/{subjectId}/{batchId}/{semesterId}', [TimetableController::class, 'editSemesterTimetable'])->name('department.timetable.edit');
        Route::get('timetable-data/{subjectId}/{batchId}/{semesterId}', [TimetableController::class, 'getTimetableData'])->name('department.timetable.data');
        Route::get('timetable-conflicts/{hourNumber}/{day}', [TimetableController::class, 'getTeacherConflicts'])->name('department.timetable.conflicts');
        Route::delete('timetable-routine/{routineId}', [TimetableController::class, 'deleteRoutineSlot'])->name('department.timetable.delete');
        Route::delete('timetable-clear/{subjectId}/{batchId}/{semesterId}', [TimetableController::class, 'clearAllRoutines'])->name('department.timetable.clear');
        Route::post('timetable/{subjectId}/{batchId}/{semesterId}', [TimetableController::class, 'storeSemesterTimetable'])->name('department.timetable.store');
        Route::get('faculty-timetable/{facultyId}', [TimetableController::class, 'facultyTimetable'])->name('department.faculty.timetable');
        //substitution
        Route::get('substitution/{id}', [TimetableController::class, 'substitution'])->name('department.substitution');
        Route::get('substitution-schedule/{batchId}/{day}', [TimetableController::class, 'getSubstitutionSchedule'])->name('department.substitution.schedule');
        Route::post('substitution-save', [TimetableController::class, 'saveSubstitutions'])->name('department.substitution.save');
        Route::put('substitution-update/{routineId}', [TimetableController::class, 'updateSubstitution'])->name('department.substitution.update');
        Route::get('substitution-history', [TimetableController::class, 'getSubstitutionHistory'])->name('department.substitution.history');
        Route::get('substitution-history-page', [TimetableController::class, 'substitutionHistoryPage'])->name('department.substitution.history.page');
        Route::get('substitution-history-export', [TimetableController::class, 'exportSubstitutionHistory'])->name('department.substitution.history.export');

        //dept-admission-console
        Route::get('application-list', [AdmissionController::class, 'deptApplicationList'])->name('department.admission.list');
        Route::get('interview-list', [AdmissionController::class, 'deptInterviewList'])->name('department.admission.interview-list');
    });


    //Testing route
    Route::group(['prefix' => '/test'], function () {
        //   Route::get('dept-campus-mapping', [TestController::class, 'DeptCampusMapping']);
        Route::get('mailing', [TestController::class, 'mailTest']);
        Route::get('sms', [TestController::class, 'smsTest']);
        Route::get('install-new-programid', [TestController::class, 'studentMasterProgramFixing']);
    });
});

// Faculty routes
Route::group(['prefix' => 'faculty', 'as' => 'faculty.'], function () {
    Route::get('dashboard', [\App\Faculty\Http\Controllers\FacultyDashboardController::class, 'index'])->name('dashboard');
    Route::get('timetable', [\App\Faculty\Http\Controllers\TimetableController::class, 'index'])->name('timetable');
    Route::get('attendance', [\App\Faculty\Http\Controllers\AttendanceController::class, 'index'])->name('attendance');
    Route::get('work-diary', [\App\Faculty\Http\Controllers\WorkDiaryController::class, 'index'])->name('workdiary');
    Route::get('request-application', [\App\Faculty\Http\Controllers\RequestApplicationController::class, 'index'])->name('requestapplication');
    Route::get('payroll', [\App\Faculty\Http\Controllers\PayrollController::class, 'index'])->name('payroll');
    Route::get('payroll/download', [\App\Faculty\Http\Controllers\PayrollController::class, 'download'])->name('payroll.download');
});
