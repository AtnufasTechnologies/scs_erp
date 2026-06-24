<?php

use App\Faculty\Http\Controllers\FacultyDashboardController;
use App\Faculty\Http\Controllers\TimetableController as FacultyTimetableController;
use App\Faculty\Http\Controllers\AttendanceController as FacultyAttendanceController;
use App\Faculty\Http\Controllers\WorkDiaryController as WorkDiaryController;
use App\Faculty\Http\Controllers\FacultyLeaveController;
use App\Faculty\Http\Controllers\InternalMarksController;
use App\Faculty\Http\Controllers\MentorshipController;
use App\Faculty\Http\Controllers\PayrollController as FacultyPayrollController;
use App\Faculty\Http\Controllers\RequestApplicationController as FacultyRequestApplicationController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\AccountOfficeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPayrollController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AdmitCardController;
use App\Http\Controllers\BacklogsController;
use App\Http\Controllers\CoeDashboardController;
use App\Http\Controllers\CoeInternalMarksReviewController;
use App\Http\Controllers\CoeAttendanceController;
use App\Http\Controllers\CoeExamController;
use App\Http\Controllers\DcoeManagementController;
use App\Http\Controllers\CoeRegulationController;
use App\Http\Controllers\DepartmentActivityController;
use App\Http\Controllers\DeptLeaveController;
use App\Http\Controllers\DummyNumberController;
use App\Http\Controllers\EvaluationDutyController;
use App\Http\Controllers\ExamAttendanceController;
use App\Http\Controllers\ExamMarksController;
use App\Http\Controllers\ExamPacketController;
use App\Http\Controllers\ExamPacketBarcodeController;
use App\Http\Controllers\ExamRegistrationController;
use App\Http\Controllers\ExamRemunerationController;
use App\Http\Controllers\ExamReportsController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\ExitCertificationController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\InvigilationDutyController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\ModerationDutyController;
use App\Http\Controllers\PaymentBatchController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SeatingAllocationController;
use App\Http\Controllers\StudentCreditController;
use App\Http\Controllers\CourseOfferingController;
use App\Http\Controllers\CourseSeatController;
use App\Http\Controllers\EventCoordinatorController;
use App\Http\Controllers\HrFacultyController;
use App\Http\Controllers\HrLeaveController;
use App\Http\Controllers\HrFdpController;
use App\Http\Controllers\HrVacancyController;
use App\Http\Controllers\HrPayMatrixController;
use App\Http\Controllers\HrPayrollController;
use App\Http\Controllers\HrDesignationController;
use App\Http\Controllers\HrGradeLevelController;
use App\Http\Controllers\Hr\ApiScoreController;
use App\Http\Controllers\SyllabusPdfController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\ITCellController;
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
Route::get('forgot-password', [LoginController::class, 'forgotPassword'])->name('scms.forgot.password');
Route::post('login', [LoginController::class, 'login']);
Route::post('forgot-password', [LoginController::class, 'sendPasswordReset']);
Route::get('verify-mail-reset-token/{id}', [LoginController::class, 'verifyResetToken']);
Route::post('update-password', [LoginController::class, 'updatePassword'])->name('update.password');
Route::get('logout', [LoginController::class, 'logout'])->name('scms.logout');

// Student Authentication Routes
Route::get('student-login', [StudentAuthController::class, 'index'])->name('student.login');
Route::post('student-login', [StudentAuthController::class, 'login'])->name('student.login.submit');
Route::get('student-forgot-password', [StudentAuthController::class, 'forgotPassword'])->name('student.forgot.password');
Route::post('student-forgot-password', [StudentAuthController::class, 'sendPasswordReset'])->name('student.password.reset.send');
Route::get('student-verify-reset-token/{code}', [StudentAuthController::class, 'verifyResetToken'])->name('student.verify.reset.token');
Route::post('student-update-password', [StudentAuthController::class, 'updatePassword'])->name('student.password.update');
Route::get('student-logout', [StudentAuthController::class, 'logout'])->name('student.logout');

Route::group(['prefix' => '/erp'], function () {

    //ITCELL - superuser routes
    Route::group(['prefix' => '/admin',], function () {
        Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('std-master-sonada', [AdminController::class, 'stdMasterSonada']);
        Route::get('std-master-siliguri', [AdminController::class, 'stdMasterSiliguri']);
        Route::get('student-search', [AdminController::class, 'searchStudents'])->name('admin.student.search');
        Route::get('faculty-master', [AdminController::class, 'facultyMaster']);
        Route::get('{id}/std-profile/{rollno}', [AdminController::class, 'stdprofile'])->name('admin.student.profile');
        Route::put('{id}/std-update', [AdminController::class, 'stdUpdate'])->name('admin.student.update');
        Route::post('{studentId}/courses', [AdminController::class, 'stdCourseStore'])->name('admin.student.courses.store');
        Route::put('{studentId}/courses/{sciId}', [AdminController::class, 'stdCourseUpdate'])->name('admin.student.courses.update');
        Route::delete('{studentId}/courses/{sciId}', [AdminController::class, 'stdCourseDestroy'])->name('admin.student.courses.destroy');
        Route::post('student/{studentId}/create-access', [AdminController::class, 'createStudentAccess'])->name('admin.student.create-access');
        Route::post('update/faculty', [AdminController::class, 'updateFaculty']);
        Route::get('itcell-admission-applications', [AdmissionController::class, 'itcellAdmissionApplications'])->name('itcell.admission.applications');
        Route::get('verify-payment/{id}', [ITCellController::class, 'verifyPayment'])->name('itcell.admission.verify.payment');
        Route::post('update-application-payment', [ITCellController::class, 'updateApplicationPayment'])->name('itcell.admission.update.payment');


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

            Route::get('paper-type', [AdminController::class, 'paperTypeMaster']);
            Route::post('paper-type', [AdminController::class, 'addPaperType']);
            Route::get('del-paper-type/{id}', [AdminController::class, 'delPaperType']);


            Route::get('cognitive-lvl', [AdminController::class, 'cognitiveLvl']);
            Route::post('cognitive-lvl', [AdminController::class, 'addCognitiveLvl']);
            Route::get('del-coglvl/{id}', [AdminController::class, 'delCogLvl']);
            Route::put('cognitive-lvl/{id}', [AdminController::class, 'updateCognitiveLvl'])->name('update.rbt.level');

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

            // Subject Combination Master
            Route::get('subject-combination-master', [SubjectController::class, 'subjectCombinationMaster'])->name('admin.subject-combination-master');
            Route::post('subject-combination', [SubjectController::class, 'storeSubjectCombination'])->name('admin.subject-combination.store');
            Route::get('delete-subject-combination/{id}', [SubjectController::class, 'deleteSubjectCombination'])->name('admin.subject-combination.delete');

            Route::get('student-program-master', [AdminController::class, 'studentProgramMaster'])->name('itcell.student-program-master');
            Route::post('student-program-type/multi-update', [AdminController::class, 'studentProgramTypeMultiUpdate'])->name('itcell.student-program-type.multi.update');
        });

        //account
        Route::group(['prefix' => '/accounts'], function () {
            Route::get('dashboard', [AccountOfficeController::class, 'dashboard'])->name('account-office.dashboard');
            Route::get('assistant-access', [AccountOfficeController::class, 'assistantAccess'])->name('account-office.assistant-access');
            Route::post('create-assistant', [AccountOfficeController::class, 'createAssistant'])->name('account-office.create-assistant');
            Route::post('update-permissions/{id}', [AccountOfficeController::class, 'updateAssistantPermissions'])->name('account-office.update-permissions');
            Route::get('toggle-status/{id}', [AccountOfficeController::class, 'toggleAssistantStatus'])->name('account-office.toggle-status');
            Route::get('delete-assistant/{id}', [AccountOfficeController::class, 'deleteAssistant'])->name('account-office.delete-assistant');
            Route::get('remove-permission/{id}', [AccountOfficeController::class, 'removeAssistantPermission'])->name('account-office.remove-permission');
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
            Route::get('direct-unlink-prog-from-feestructuregroup/{id}', [AdminController::class, 'unlinkStdProgramDirect'])->name('direct.unlink.feestructure.stdprogram');

            Route::get('del-feecourse-master/{id}', [AdminController::class, 'delFeeCourseMaster']);
            Route::post('fee-structure-groups', [AdminController::class, 'addFeeStructureGroup']);
            Route::get('unlink/fee-structure-group/{id}', [AdminController::class, 'feeStructureGroupUnlink'])->name('unlink.fee-structure-group');
            Route::get('unlink/fee-structure-studentprogram/{id}', [AdminController::class, 'feeStructureStdProgramUnlink'])->name('delete.fee-structure.studentprogram');
            Route::post('connect/fee-structure-studentprogram', [AdminController::class, 'connectFeesStructureSingleWithStdProgram'])->name('connect.fees-structure.studentprogram');

            Route::get('student-fee/{id}', [AdminController::class, 'getFeeStructure']);
            Route::get('fee-course-master', [AdminController::class, 'feeCourseMaster'])->name('fee.course.master');
            Route::post('fee-course-master', [AdminController::class, 'addCourseFeeMaster']);
            Route::post('update-fee-course-master', [AdminController::class, 'updateCourseFeeMaster']);
            Route::get('delete-feestructure/{id}', [AdminController::class, 'deleteFeeStructure']);
            Route::post('clone-feestructure/{id}', [AdminController::class, 'cloneFeeStructure'])->name('fee-structure.clone');
            Route::post('clone-all-feestructures', [AdminController::class, 'cloneAllFeeStructures'])->name('fee-structure.clone-all');

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
            Route::get('print-feereciept/{feeId}', [FeePaymentController::class, 'generateFeeReciept']);
            Route::get('all-payments', [FeePaymentController::class, 'allPayments'])->name('all.payments');
            Route::get('transaction-info/{id}', [FeePaymentController::class, 'showSuccessPage'])->name('transaction.info');
            Route::get('verify-transaction/{id}', [FeePaymentController::class, 'verifyTransaction']);
            Route::get('defaulters-list', [FeePaymentController::class, 'defaultersList'])->name('defaulters-list');
            Route::post('update-transaction-date', [FeePaymentController::class, 'updateTransactionDate'])->name('update.transaction.date');
            // Late Fee Exemption Management
            Route::get('late-fee-exemptions', [FeePaymentController::class, 'lateFeeExemptionIndex'])->name('late.fee.exemptions');
            Route::post('late-fee-exemption/grant', [FeePaymentController::class, 'grantLateFeeExemption'])->name('grant.late.fee.exemption');

            // Faculty Payroll Management
            Route::get('payroll', [AdminPayrollController::class, 'index'])->name('admin.payroll.index');
            Route::get('payroll/create', [AdminPayrollController::class, 'create'])->name('admin.payroll.create');
            Route::post('payroll', [AdminPayrollController::class, 'store'])->name('admin.payroll.store');
            Route::post('payroll/bulk-generate', [AdminPayrollController::class, 'bulkGenerate'])->name('admin.payroll.bulk-generate');

            // Faculty Loans Management (must come before {id} routes)
            Route::get('payroll/loans', [AdminPayrollController::class, 'loans'])->name('admin.payroll.loans');
            Route::post('payroll/loans', [AdminPayrollController::class, 'storeLoan'])->name('admin.payroll.loans.store');
            Route::post('payroll/loans/{id}/update-status', [AdminPayrollController::class, 'updateLoanStatus'])->name('admin.payroll.loans.update-status');

            // Salary Masters Management (must come before {id} routes)
            Route::get('payroll/salary-masters', [AdminPayrollController::class, 'salaryMasters'])->name('admin.payroll.salary-masters');
            Route::get('payroll/salary-masters/create', [AdminPayrollController::class, 'createSalaryMaster'])->name('admin.payroll.salary-masters.create');
            Route::post('payroll/salary-masters', [AdminPayrollController::class, 'storeSalaryMaster'])->name('admin.payroll.salary-masters.store');
            Route::get('payroll/salary-masters/{id}/edit', [AdminPayrollController::class, 'editSalaryMaster'])->name('admin.payroll.salary-masters.edit');
            Route::put('payroll/salary-masters/{id}', [AdminPayrollController::class, 'updateSalaryMaster'])->name('admin.payroll.salary-masters.update');
            Route::delete('payroll/salary-masters/{id}', [AdminPayrollController::class, 'destroySalaryMaster'])->name('admin.payroll.salary-masters.destroy');
            Route::post('payroll/salary-masters/{id}/toggle-status', [AdminPayrollController::class, 'toggleSalaryMasterStatus'])->name('admin.payroll.salary-masters.toggle-status');

            // Get faculty info API (must come before {id} routes)
            Route::get('payroll/faculty-info/{facultyId}', [AdminPayrollController::class, 'getFacultyInfo'])->name('admin.payroll.faculty-info');

            // Payroll specific routes with {id}
            Route::get('payroll/{id}', [AdminPayrollController::class, 'show'])->name('admin.payroll.show');
            Route::get('payroll/{id}/edit', [AdminPayrollController::class, 'edit'])->name('admin.payroll.edit');
            Route::put('payroll/{id}', [AdminPayrollController::class, 'update'])->name('admin.payroll.update');
            Route::delete('payroll/{id}', [AdminPayrollController::class, 'destroy'])->name('admin.payroll.destroy');
            Route::post('payroll/{id}/approve', [AdminPayrollController::class, 'approve'])->name('admin.payroll.approve');
            Route::post('payroll/{id}/mark-paid', [AdminPayrollController::class, 'markAsPaid'])->name('admin.payroll.mark-paid');
            Route::post('late-fee-exemption/{id}/revoke', [FeePaymentController::class, 'revokeLateFeeExemption'])->name('revoke.late.fee.exemption');

            Route::get('defaulters-list', [FeePaymentController::class, 'defaultersList'])->name('defaulters-list');
            Route::get('delete-user-permission/{id}', [AdminController::class, 'deleteUserPermission'])->name('admin.user-access.delete-permission');

            Route::get('admission-application-fee', [FeePaymentController::class, 'admissionApplicationFee'])->name('admin.accounts.admission-application-fee');

            // Payment Reports
            Route::get('fee-head-wise-report', [FeePaymentController::class, 'feeHeadWiseReport'])->name('accounts.fee-head-wise-report');
            Route::get('bank-account-wise-report', [FeePaymentController::class, 'bankAccountWiseReport'])->name('accounts.bank-account-wise-report');
            Route::get('payment-report-by-date', [FeePaymentController::class, 'paymentReportByDate'])->name('accounts.payment-report-by-date');
            Route::get('payment-type-report', [FeePaymentController::class, 'paymentTypeReport'])->name('accounts.payment-type-report');
        });

        //Academics
        Route::group(['prefix' => '/academics'], function () {
            Route::post('add/subject-semester', [SubjectController::class, 'addSemesterToSubject'])->name('add.semester.to.subject');
            Route::post('add/subject-syllabus', [SubjectController::class, 'addSyllabus'])->name('add.syllabus.to.semester');
            Route::get('all-course-combinations', [SubjectController::class, 'deptAllCourseCombinations'])->name('dept.all.course-combination');
            Route::get('program-course-master', [SubjectController::class, 'programCourseMaster'])->name('program-course.master');
            Route::get('course-master', [SubjectController::class, 'adminCourseMaster'])->name('admin.course-master');
            Route::post('link-std-programs', [SubjectController::class, 'linkStdPrograms'])->name('admin.link.std.programs');
            Route::delete('delete-combination/{id}', [SubjectController::class, 'deleteCombination'])->name('admin.delete.combination');
            Route::put('update-combination/{id}', [SubjectController::class, 'updateCombination'])->name('admin.update.combination');
            Route::get('student-program-master', [SubjectController::class, 'studentProgramMaster'])->name('admin.student-program-master');
            Route::post('add/new/student-program', [SubjectController::class, 'addNewStudentProgram'])->name('admin.add.new.student-program');
            Route::post('update/student-program/{id}', [SubjectController::class, 'updateStudentProgram'])->name('admin.update.student-program');
            Route::get('admission-combinations', [SubjectController::class, 'getAdmissionCombination'])->name('itcell.admission.combination-master');
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

            Route::get('role-master', [AdminController::class, 'roleMaster'])->name('admin.role-master');
            Route::post('add-role', [AdminController::class, 'addRole'])->name('admin.add.role');
            Route::post('update-role/{id}', [AdminController::class, 'updateRole'])->name('admin.update.role');
            Route::get('delete-role/{id}', [AdminController::class, 'deleteRole'])->name('admin.delete.role');

            Route::get('revoke-access/{id}', [AccessController::class, 'revokeDeptAccess'])->name('dept.erp.revoke-access');
            //impersonate user
            Route::get('impersonate/{id}', [AccessController::class, 'impersonateUser'])->name('impersonate.user');
            Route::get('sms-templates', [AccessController::class, 'smsTemplates'])->name('admin.sms.templates');
            Route::post('sms-templates', [AccessController::class, 'smsTemplateStore'])->name('sms.template.store');
            Route::get('sms-template/delete/{id}', [AccessController::class, 'smsTemplateDelete'])->name('sms.template.delete');
            Route::get('activity-logs-dashboard', [AccessController::class, 'activityLogsDashboard'])->name('admin.activity-logs.dashboard');
            Route::get('activity-logs', [AccessController::class, 'userActivityLogs'])->name('admin.user.activityapplication-single-logs');
        });

        //admission routes Admin
        Route::group(['prefix' => '/admission'], function () {

            Route::get('dashboard', [AdmissionController::class, 'dashboard'])->name('admission.dashboard');
            Route::get('search', [AdmissionController::class, 'admissionGlobalSearch'])->name('admission.search');
            Route::get('registrations/{type}', [AdmissionController::class, 'admissionRegistrations'])->name('admission.registration');
            //UG 
            Route::get('ug-applications', [AdmissionController::class, 'ugApplications'])->name('admission.ug.applications');
            Route::get('application-single/{id}', [AdmissionController::class, 'ugApplicationSingle'])->name('admin.admission.ug.application-single');
            Route::get('phase1', [AdmissionController::class, 'ugPhase1Registrations'])->name('admission.ug.phase1');
            Route::get('phase1/export-all', [AdmissionController::class, 'exportPhase1AllApplicants'])->name('admission.ug.phase1.export-all');
            Route::get('phase1/export-selected', [AdmissionController::class, 'exportPhase1SelectedApplicants'])->name('admission.ug.phase1.export-selected');
            Route::get('phase2', [AdmissionController::class, 'ugPhase2Registrations'])->name('admission.ug.phase2');
            Route::get('phase2/export', [AdmissionController::class, 'exportUgPhase2'])->name('admission.ug.phase2.export');
            Route::put('phase2/update-status/{id}', [AdmissionController::class, 'updateUgPhase2Status'])->name('admission.ug.phase2.update-status');
            //controls
            Route::post('send-phase1-notification-single', [AdmissionController::class, 'sendPhase1NotificationSingle'])->name('send.phase1.notification.single');
            Route::post('send-phase1-notification', [AdmissionController::class, 'sendPhase1BulkNotification'])->name('send.phase1.notification');
            Route::put('phase1/update-status/{id}', [AdmissionController::class, 'updateUgPhase1Status'])->name('admission.ug.phase1.update-status');
            Route::put('phase1/program-shift/{id}', [AdmissionController::class, 'shiftUgProgram'])->name('admission.ug.phase1.shift-program');
            Route::post('phase1/program-transfer', [AdmissionController::class, 'transferUgProgram'])->name('admission.ug.phase1.transfer-program'); //new route

            Route::post('send-phase2-notification', [AdmissionController::class, 'sendPhase2BulkNotification'])->name('send.phase2.notification');
            //Settings
            Route::get('settings', [AdmissionController::class, 'admissionSettings'])->name('admission.settings');
            Route::post('update-admission-settings-ug', [AdmissionController::class, 'updateAdmissionSettingsUg'])->name('update.admission.settings.ug');
            Route::post('update-admission-settings-pg', [AdmissionController::class, 'updateAdmissionSettingsPg'])->name('update.admission.settings.pg');
            Route::get('phase1/override/{id}', [AdmissionController::class, 'overrideUgPhase1Status'])->name('admission.ug.phase1.override');
            Route::post('phase1/bulk-override', [AdmissionController::class, 'bulkOverrideUgPhase1Status'])->name('admission.ug.phase1.bulk-override');

            //PG
            Route::get('pg-applications', [AdmissionController::class, 'pgApplications'])->name('admission.pg.applications');
            Route::get('pg-phase1', [AdmissionController::class, 'pgPhase1Registrations'])->name('admission.pg.phase1');
            Route::get('pg-phase1/export-all', [AdmissionController::class, 'exportPgPhase1AllApplicants'])->name('admission.pg.phase1.export-all');
            Route::get('pg-phase1/export-selected', [AdmissionController::class, 'exportPgPhase1SelectedApplicants'])->name('admission.pg.phase1.export-selected');
            Route::put('pg-phase1/update-status/{id}', [AdmissionController::class, 'updatePgPhase1Status'])->name('admission.pg.phase1.update-status');
            Route::get('pg-phase2', [AdmissionController::class, 'pgPhase2Registrations'])->name('admission.pg.enrollment');
            Route::get('pg-phase2/export', [AdmissionController::class, 'exportPgPhase2'])->name('admission.pg.phase2.export');


            //Edit Application
            Route::get('edit-application/{id}', [AdmissionController::class, 'showEditApplication'])->name('admission.edit.application');
            Route::put('update-application/{id}', [AdmissionController::class, 'updateUgApplication'])->name('admission.update.ug.application');

            // Registration Edit CRUD
            Route::get('registration/edit/{id}', [AdmissionController::class, 'editRegistration'])->name('admin.registration.edit');
            Route::put('registration/update/{id}', [AdmissionController::class, 'updateRegistration'])->name('admin.registration.update');

            Route::get('otp-status-updater/{id}', [AdmissionController::class, 'updateOtpStatus'])->name('otp.status.update');
            Route::get('get-departments/{id}', [AdmissionController::class, 'getDepartmentsByCampusProgram'])->name('get.departments.by.campusprogram');
            Route::get('get-programs-by-department/{id}/{code}', [AdmissionController::class, 'getCoursesByDepartment'])->name('get.programs.bydepartment');

            Route::post('campus-shift', [AdmissionController::class, 'applicantCampusShift'])->name('applicant.campus.shifter');
            Route::get('verify-payment/{id}', [AdmissionController::class, 'verifyPayment'])->name('admission.verify.payment');
            Route::post('update-application-payment', [AdmissionController::class, 'updateApplicationPayment'])->name('admission.update.payment');
            //EASE BUZZ WEBHOOK
            Route::post('admission-payment-webhook-easebuzz', [AdmissionController::class, 'webhookEasebuzz'])->name('admission.payment.webhook.easebuzz');

            Route::get('admin-show-student-application-ug/{id}', [AdmissionController::class, 'adminFillStudentApplicationUg'])->name('admin.fill.student.application.ug');
            Route::post('admin-apply-student-application-ug', [AdmissionController::class, 'adminSubmitStudentApplicationUg'])->name('admin.submit.student.application.ug');

            //Test Incharge Dashboard
            Route::get('test-incharge/dashboard', [AdmissionController::class, 'testInchargeDashboard'])->name('admission.testincharge.dashboard');
            Route::get('activate-application-payment/{id}', [AdmissionController::class, 'activateApplicationPayment'])->name('activate.admission.payment');
        });

        // Exam Registrations Management
        Route::group(['prefix' => '/exam-registrations'], function () {
            Route::get('/', [ExamRegistrationController::class, 'index'])->name('admin.exam-registrations.index');
            Route::get('/create', [ExamRegistrationController::class, 'create'])->name('admin.exam-registrations.create');
            Route::post('/', [ExamRegistrationController::class, 'store'])->name('admin.exam-registrations.store');
            Route::get('/{id}', [ExamRegistrationController::class, 'show'])->name('admin.exam-registrations.show');
            Route::get('/{id}/edit', [ExamRegistrationController::class, 'edit'])->name('admin.exam-registrations.edit');
            Route::put('/{id}', [ExamRegistrationController::class, 'update'])->name('admin.exam-registrations.update');
            Route::delete('/{id}', [ExamRegistrationController::class, 'destroy'])->name('admin.exam-registrations.destroy');
            Route::post('/bulk-approve', [ExamRegistrationController::class, 'bulkApprove'])->name('admin.exam-registrations.bulk-approve');
            Route::post('/bulk-reject', [ExamRegistrationController::class, 'bulkReject'])->name('admin.exam-registrations.bulk-reject');
            Route::post('/check-clearances', [ExamRegistrationController::class, 'checkClearances'])->name('admin.exam-registrations.check-clearances');
            Route::post('/{id}/update-clearance', [ExamRegistrationController::class, 'updateClearance'])->name('admin.exam-registrations.update-clearance');
            Route::get('/export', [ExamRegistrationController::class, 'export'])->name('admin.exam-registrations.export');
        });

        // Seating Allocation Management
        Route::group(['prefix' => '/seating-allocation'], function () {
            Route::get('/', [SeatingAllocationController::class, 'index'])->name('admin.seating-allocation.index');
            Route::get('/create', [SeatingAllocationController::class, 'create'])->name('admin.seating-allocation.create');
            Route::post('/', [SeatingAllocationController::class, 'store'])->name('admin.seating-allocation.store');
            Route::get('/{id}', [SeatingAllocationController::class, 'show'])->name('admin.seating-allocation.show');
            Route::get('/{id}/edit', [SeatingAllocationController::class, 'edit'])->name('admin.seating-allocation.edit');
            Route::put('/{id}', [SeatingAllocationController::class, 'update'])->name('admin.seating-allocation.update');
            Route::delete('/{id}', [SeatingAllocationController::class, 'destroy'])->name('admin.seating-allocation.destroy');
            Route::post('/auto-allocate', [SeatingAllocationController::class, 'autoAllocate'])->name('admin.seating-allocation.auto-allocate');
            Route::get('/export', [SeatingAllocationController::class, 'export'])->name('admin.seating-allocation.export');
        });

        // Dummy Numbers Management
        Route::group(['prefix' => '/dummy-numbers'], function () {
            Route::get('/', [DummyNumberController::class, 'index'])->name('admin.dummy-numbers.index');
            Route::get('/create', [DummyNumberController::class, 'create'])->name('admin.dummy-numbers.create');
            Route::post('/', [DummyNumberController::class, 'store'])->name('admin.dummy-numbers.store');
            Route::get('/{id}', [DummyNumberController::class, 'show'])->name('admin.dummy-numbers.show');
            Route::get('/{id}/edit', [DummyNumberController::class, 'edit'])->name('admin.dummy-numbers.edit');
            Route::put('/{id}', [DummyNumberController::class, 'update'])->name('admin.dummy-numbers.update');
            Route::delete('/{id}', [DummyNumberController::class, 'destroy'])->name('admin.dummy-numbers.destroy');
            Route::post('/auto-generate', [DummyNumberController::class, 'autoGenerate'])->name('admin.dummy-numbers.auto-generate');
            Route::post('/lock', [DummyNumberController::class, 'lock'])->name('admin.dummy-numbers.lock');
            Route::post('/unlock', [DummyNumberController::class, 'unlock'])->name('admin.dummy-numbers.unlock');
            Route::get('/export', [DummyNumberController::class, 'export'])->name('admin.dummy-numbers.export');
        });

        // Exam Attendance Management
        Route::group(['prefix' => '/exam-attendance'], function () {
            Route::get('/', [ExamAttendanceController::class, 'index'])->name('admin.exam-attendance.index');
            Route::get('/create', [ExamAttendanceController::class, 'create'])->name('admin.exam-attendance.create');
            Route::post('/', [ExamAttendanceController::class, 'store'])->name('admin.exam-attendance.store');
            Route::get('/{id}', [ExamAttendanceController::class, 'show'])->name('admin.exam-attendance.show');
            Route::get('/{id}/edit', [ExamAttendanceController::class, 'edit'])->name('admin.exam-attendance.edit');
            Route::put('/{id}', [ExamAttendanceController::class, 'update'])->name('admin.exam-attendance.update');
            Route::delete('/{id}', [ExamAttendanceController::class, 'destroy'])->name('admin.exam-attendance.destroy');
            Route::post('/bulk-mark', [ExamAttendanceController::class, 'bulkMark'])->name('admin.exam-attendance.bulk-mark');
            Route::get('/export', [ExamAttendanceController::class, 'export'])->name('admin.exam-attendance.export');
        });

        // Exam Marks Management
        Route::group(['prefix' => '/exam-marks'], function () {
            Route::get('/', [ExamMarksController::class, 'index'])->name('admin.exam-marks.index');
            Route::get('/create', [ExamMarksController::class, 'create'])->name('admin.exam-marks.create');
            Route::post('/', [ExamMarksController::class, 'store'])->name('admin.exam-marks.store');
            Route::get('/{id}', [ExamMarksController::class, 'show'])->name('admin.exam-marks.show');
            Route::get('/{id}/edit', [ExamMarksController::class, 'edit'])->name('admin.exam-marks.edit');
            Route::put('/{id}', [ExamMarksController::class, 'update'])->name('admin.exam-marks.update');
            Route::delete('/{id}', [ExamMarksController::class, 'destroy'])->name('admin.exam-marks.destroy');
            Route::post('/bulk-entry', [ExamMarksController::class, 'bulkEntry'])->name('admin.exam-marks.bulk-entry');
            Route::get('/export', [ExamMarksController::class, 'export'])->name('admin.exam-marks.export');
        });

        // Invigilation Duties Management
        Route::group(['prefix' => '/invigilation-duties'], function () {
            Route::get('/', [InvigilationDutyController::class, 'index'])->name('admin.invigilation-duties.index');
            Route::get('/create', [InvigilationDutyController::class, 'create'])->name('admin.invigilation-duties.create');
            Route::post('/', [InvigilationDutyController::class, 'store'])->name('admin.invigilation-duties.store');
            Route::get('/{id}', [InvigilationDutyController::class, 'show'])->name('admin.invigilation-duties.show');
            Route::get('/{id}/edit', [InvigilationDutyController::class, 'edit'])->name('admin.invigilation-duties.edit');
            Route::put('/{id}', [InvigilationDutyController::class, 'update'])->name('admin.invigilation-duties.update');
            Route::delete('/{id}', [InvigilationDutyController::class, 'destroy'])->name('admin.invigilation-duties.destroy');
            Route::post('/{id}/mark-completed', [InvigilationDutyController::class, 'markCompleted'])->name('admin.invigilation-duties.mark-completed');
            Route::post('/auto-assign', [InvigilationDutyController::class, 'autoAssign'])->name('admin.invigilation-duties.auto-assign');
            Route::get('/export', [InvigilationDutyController::class, 'export'])->name('admin.invigilation-duties.export');
        });

        // Evaluation Duties Management
        Route::group(['prefix' => '/evaluation-duties'], function () {
            Route::get('/', [EvaluationDutyController::class, 'index'])->name('admin.evaluation-duties.index');
            Route::get('/create', [EvaluationDutyController::class, 'create'])->name('admin.evaluation-duties.create');
            Route::post('/', [EvaluationDutyController::class, 'store'])->name('admin.evaluation-duties.store');
            Route::get('/subjects-by-exam/{examId}', [EvaluationDutyController::class, 'getSubjectsByExam'])->name('admin.evaluation-duties.subjects-by-exam');
            Route::get('/{id}', [EvaluationDutyController::class, 'show'])->name('admin.evaluation-duties.show');
            Route::get('/{id}/edit', [EvaluationDutyController::class, 'edit'])->name('admin.evaluation-duties.edit');
            Route::put('/{id}', [EvaluationDutyController::class, 'update'])->name('admin.evaluation-duties.update');
            Route::delete('/{id}', [EvaluationDutyController::class, 'destroy'])->name('admin.evaluation-duties.destroy');
            Route::post('/{id}/mark-completed', [EvaluationDutyController::class, 'markCompleted'])->name('admin.evaluation-duties.mark-completed');
            Route::post('/{id}/update-progress', [EvaluationDutyController::class, 'updateProgress'])->name('admin.evaluation-duties.update-progress');
            Route::post('/auto-assign', [EvaluationDutyController::class, 'autoAssign'])->name('admin.evaluation-duties.auto-assign');
            Route::get('/export', [EvaluationDutyController::class, 'export'])->name('admin.evaluation-duties.export');
        });

        // Moderation Duties Management
        Route::group(['prefix' => '/moderation-duties'], function () {
            Route::get('/', [ModerationDutyController::class, 'index'])->name('admin.moderation-duties.index');
            Route::get('/create', [ModerationDutyController::class, 'create'])->name('admin.moderation-duties.create');
            Route::post('/', [ModerationDutyController::class, 'store'])->name('admin.moderation-duties.store');
            Route::get('/compare', [ModerationDutyController::class, 'compare'])->name('admin.moderation-duties.compare');
            Route::post('/import-marks', [ModerationDutyController::class, 'importMarks'])->name('admin.moderation-duties.import-marks');
            Route::post('/bulk-adjust', [ModerationDutyController::class, 'bulkAdjust'])->name('admin.moderation-duties.bulk-adjust');
            Route::post('/finalize', [ModerationDutyController::class, 'finalize'])->name('admin.moderation-duties.finalize');
            Route::get('/subjects-by-exam/{examId}', [ModerationDutyController::class, 'getSubjectsByExam'])->name('admin.moderation-duties.subjects-by-exam');
            Route::get('/export', [ModerationDutyController::class, 'export'])->name('admin.moderation-duties.export');
            Route::post('/auto-assign', [ModerationDutyController::class, 'autoAssign'])->name('admin.moderation-duties.auto-assign');
            Route::get('/{id}', [ModerationDutyController::class, 'show'])->name('admin.moderation-duties.show');
            Route::get('/{id}/edit', [ModerationDutyController::class, 'edit'])->name('admin.moderation-duties.edit');
            Route::put('/{id}', [ModerationDutyController::class, 'update'])->name('admin.moderation-duties.update');
            Route::delete('/{id}', [ModerationDutyController::class, 'destroy'])->name('admin.moderation-duties.destroy');
            Route::post('/{id}/mark-completed', [ModerationDutyController::class, 'markCompleted'])->name('admin.moderation-duties.mark-completed');
            Route::post('/{id}/moderator-marks', [ModerationDutyController::class, 'storeModeratorMarks'])->name('admin.moderation-duties.moderator-marks');
            Route::post('/{id}/adjust', [ModerationDutyController::class, 'adjustMarks'])->name('admin.moderation-duties.adjust');
        });

        // Exam Results Management
        Route::group(['prefix' => '/exam-results'], function () {
            Route::get('/', [ExamResultController::class, 'index'])->name('admin.exam-results.index');
            Route::get('/generate', [ExamResultController::class, 'generate'])->name('admin.exam-results.generate');
            Route::post('/generate', [ExamResultController::class, 'doGenerate'])->name('admin.exam-results.do-generate');
            Route::get('/export', [ExamResultController::class, 'export'])->name('admin.exam-results.export');
            Route::get('/semester-wise', [ExamResultController::class, 'semesterWise'])->name('admin.exam-results.semester-wise');
            Route::post('/publish', [ExamResultController::class, 'publish'])->name('admin.exam-results.publish');
            Route::post('/unpublish', [ExamResultController::class, 'unpublish'])->name('admin.exam-results.unpublish');
            Route::post('/lock', [ExamResultController::class, 'lockResults'])->name('admin.exam-results.lock');
            Route::post('/unlock', [ExamResultController::class, 'unlockResults'])->name('admin.exam-results.unlock');
            Route::get('/{id}', [ExamResultController::class, 'show'])->name('admin.exam-results.show');
            Route::delete('/{id}', [ExamResultController::class, 'destroy'])->name('admin.exam-results.destroy');
        });
        //Backlogs Management
        Route::group(['prefix' => '/backlogs'], function () {
            Route::get('/', [BacklogsController::class, 'index'])->name('coe.backlogs.index');
            Route::get('/failed-subjects', [BacklogsController::class, 'failedSubjects'])->name('coe.backlogs.failed-subjects');
            Route::post('/register', [BacklogsController::class, 'registerBacklog'])->name('coe.backlogs.register');
            Route::get('/report', [BacklogsController::class, 'report'])->name('coe.backlogs.report');
            Route::get('/export', [BacklogsController::class, 'export'])->name('coe.backlogs.export');
            Route::get('/{id}', [BacklogsController::class, 'show'])->name('coe.backlogs.show');
            Route::post('/{id}/mark-cleared', [BacklogsController::class, 'markCleared'])->name('coe.backlogs.mark-cleared');
            Route::delete('/{id}', [BacklogsController::class, 'destroy'])->name('coe.backlogs.destroy');
        });

        // 
        // Student Promotions Management (NEP - auto-generated on result publish)
        Route::group(['prefix' => '/promotions'], function () {
            Route::get('/export', [PromotionController::class, 'export'])->name('admin.promotions.export');
            Route::get('/', [PromotionController::class, 'index'])->name('admin.promotions.index');
            Route::get('/{id}', [PromotionController::class, 'show'])->name('admin.promotions.show');
        });

        // Student Credits (ABC) Management
        Route::group(['prefix' => '/student-credits'], function () {
            Route::get('/', [StudentCreditController::class, 'index'])->name('admin.student-credits.index');
            Route::get('/create', [StudentCreditController::class, 'create'])->name('admin.student-credits.create');
            Route::post('/', [StudentCreditController::class, 'store'])->name('admin.student-credits.store');
            Route::get('/export', [StudentCreditController::class, 'export'])->name('admin.student-credits.export');
            Route::get('/transcript/{studentId}', [StudentCreditController::class, 'transcript'])->name('admin.student-credits.transcript');
            Route::get('/{id}', [StudentCreditController::class, 'show'])->name('admin.student-credits.show');
            Route::get('/{id}/edit', [StudentCreditController::class, 'edit'])->name('admin.student-credits.edit');
            Route::put('/{id}', [StudentCreditController::class, 'update'])->name('admin.student-credits.update');
            Route::post('/{id}/verify', [StudentCreditController::class, 'verify'])->name('admin.student-credits.verify');
            Route::post('/{id}/reject', [StudentCreditController::class, 'reject'])->name('admin.student-credits.reject');
        });

        // Exit Certification Management
        Route::group(['prefix' => '/exit-certification'], function () {
            Route::get('/', [ExitCertificationController::class, 'index'])->name('admin.exit-certification.index');
            Route::get('/create', [ExitCertificationController::class, 'create'])->name('admin.exit-certification.create');
            Route::post('/', [ExitCertificationController::class, 'store'])->name('admin.exit-certification.store');
            Route::get('/{id}', [ExitCertificationController::class, 'show'])->name('admin.exit-certification.show');
            Route::post('/{id}/approve', [ExitCertificationController::class, 'approve'])->name('admin.exit-certification.approve');
            Route::post('/{id}/issue', [ExitCertificationController::class, 'issue'])->name('admin.exit-certification.issue');
            Route::post('/{id}/revoke', [ExitCertificationController::class, 'revoke'])->name('admin.exit-certification.revoke');
            Route::get('/{id}/download', [ExitCertificationController::class, 'downloadCertificate'])->name('admin.exit-certification.download');
            Route::delete('/{id}', [ExitCertificationController::class, 'destroy'])->name('admin.exit-certification.destroy');
        });

        // Exam Remuneration Management
        Route::group(['prefix' => '/exam-remuneration'], function () {
            Route::get('/', [ExamRemunerationController::class, 'index'])->name('admin.exam-remuneration.index');
            Route::get('/create', [ExamRemunerationController::class, 'create'])->name('admin.exam-remuneration.create');
            Route::post('/', [ExamRemunerationController::class, 'store'])->name('admin.exam-remuneration.store');
            Route::post('/auto-calculate', [ExamRemunerationController::class, 'autoCalculate'])->name('admin.exam-remuneration.auto-calculate');
            Route::get('/export', [ExamRemunerationController::class, 'export'])->name('admin.exam-remuneration.export');
            Route::get('/{id}', [ExamRemunerationController::class, 'show'])->name('admin.exam-remuneration.show');
            Route::post('/{id}/approve', [ExamRemunerationController::class, 'approve'])->name('admin.exam-remuneration.approve');
            Route::post('/{id}/mark-paid', [ExamRemunerationController::class, 'markPaid'])->name('admin.exam-remuneration.mark-paid');
        });

        // Payment Batches Management
        Route::group(['prefix' => '/payment-batches'], function () {
            Route::get('/', [PaymentBatchController::class, 'index'])->name('admin.payment-batches.index');
            Route::get('/create', [PaymentBatchController::class, 'create'])->name('admin.payment-batches.create');
            Route::post('/', [PaymentBatchController::class, 'store'])->name('admin.payment-batches.store');
            Route::get('/{id}', [PaymentBatchController::class, 'show'])->name('admin.payment-batches.show');
            Route::post('/{id}/approve', [PaymentBatchController::class, 'approve'])->name('admin.payment-batches.approve');
            Route::post('/{id}/mark-paid', [PaymentBatchController::class, 'markPaid'])->name('admin.payment-batches.mark-paid');
        });

        // Exam Reports
        Route::group(['prefix' => '/exam-reports'], function () {
            Route::get('/', [ExamReportsController::class, 'index'])->name('admin.exam-reports.index');
            Route::get('/dashboard', [ExamReportsController::class, 'dashboard'])->name('admin.exam-reports.dashboard');
            Route::get('/registrations', [ExamReportsController::class, 'registrationReport'])->name('admin.exam-reports.registrations');
            Route::get('/attendance', [ExamReportsController::class, 'attendanceReport'])->name('admin.exam-reports.attendance');
            Route::get('/marks', [ExamReportsController::class, 'marksReport'])->name('admin.exam-reports.marks');
            Route::get('/results', [ExamReportsController::class, 'resultsReport'])->name('admin.exam-reports.results');
            Route::get('/backlogs', [ExamReportsController::class, 'backlogReport'])->name('admin.exam-reports.backlogs');
            Route::get('/remuneration', [ExamReportsController::class, 'remunerationReport'])->name('admin.exam-reports.remuneration');
            Route::get('/duties', [ExamReportsController::class, 'dutyReport'])->name('admin.exam-reports.duties');
            Route::get('/student-progress', [ExamReportsController::class, 'studentProgressReport'])->name('admin.exam-reports.student-progress');
            Route::post('/export-pdf', [ExamReportsController::class, 'exportPdf'])->name('admin.exam-reports.export-pdf');
            Route::post('/export-excel', [ExamReportsController::class, 'exportExcel'])->name('admin.exam-reports.export-excel');

            // Admit Cards Management
            Route::group(['prefix' => '/admit-cards'], function () {
                Route::get('/', [AdmitCardController::class, 'index'])->name('coe.admit-cards.index');
                Route::get('/generate', [AdmitCardController::class, 'generate'])->name('coe.admit-cards.generate');
                Route::post('/bulk-download', [AdmitCardController::class, 'bulkDownload'])->name('coe.admit-cards.bulk-download');
                Route::get('/{id}', [AdmitCardController::class, 'show'])->name('coe.admit-cards.show');
                Route::get('/{id}/download', [AdmitCardController::class, 'downloadPdf'])->name('coe.admit-cards.download');
            });
        });
    });

    //new admission routes
    Route::group(['prefix' => '/new-admission'], function () {
        Route::get('login', [AdmissionController::class, 'login'])->name('new.admission.login');
        Route::get('registration', [AdmissionController::class, 'index'])->name('new.admission.registration');
        // Route::get('login', [AdmissionController::class, 'technicalMode'])->name('new.admission.login');
        // Route::get('registration', [AdmissionController::class, 'technicalMode'])->name('new.admission.registration');

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

    //existing student
    Route::group(['prefix' => 'student',], function () {

        //==Exclusive Console Access ONLY via Login ================= New Working Routes 04/05/2026
        Route::group(['prefix' => 'console'], function () {
            Route::get('dashboard', [StudentDashboardController::class, 'index'])->name('student.console.dashboard');
        });

        Route::get('feedback', [StudentDashboardController::class, 'feedbackList'])->name('student.feedback.list');
        Route::post('feedback/{id}', [StudentDashboardController::class, 'submitFeedback'])->name('student.feedback.submit');

        // Mentorship Assignment Upload
        Route::post('mentorship/assignment/{id}/upload', [StudentDashboardController::class, 'uploadAssignment'])->name('student.mentorship.assignment.upload');

        // Course Offerings (FIFO)
        Route::get('course-offerings', [CourseOfferingController::class, 'studentView'])->name('student.offerings.index');
        Route::post('course-offerings/register', [CourseOfferingController::class, 'studentRegister'])->name('student.offerings.register');
        Route::post('course-offerings/cancel/{id}', [CourseOfferingController::class, 'studentCancel'])->name('student.offerings.cancel');

        //===================Old Working Routes === * Don Not Disturb * ============================
        Route::get('results', [StudentResultController::class, 'lookup'])->name('student.results.lookup');
        Route::post('results/search', [StudentResultController::class, 'search'])->name('student.results.search');
        Route::get('results/{id}', [StudentResultController::class, 'detail'])->name('student.results.detail');

        Route::get('fee-payment', [FeePaymentController::class, 'studentValidation'])->name('student.fee.payment');
        Route::post('fee-status', [FeePaymentController::class, 'studentFeeStatus'])->name('student.fee.validation');
        Route::post('fee-payment', [FeePaymentController::class, 'createOrder'])->name('student.fee.payment.store');
        Route::get('fee-status', [FeePaymentController::class, 'studentValidation'])->name('student.fee.status');

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
    // ========================================================
    // Department routes

    Route::group(['prefix' => '/deptartment',], function () {
        Route::get('dashboard', [SubjectController::class, 'departmentDashboard'])->name('department.dashboard');
        Route::get('combo-master', [SubjectController::class, 'comboMaster'])->name('department.combo.master');
        Route::delete('combination/{id}/delete', [SubjectController::class, 'deleteCombination'])->name('department.combination.delete');
        Route::get('course-master/{id}/{slug}', [SubjectController::class, 'courseMaster'])->name('department.course.master');
        Route::post('my-course-master', [SubjectController::class, 'addCourseMaster'])->name('department.add.course.master');
        Route::delete('delete/course-master/{id}', [SubjectController::class, 'deleteCourseMaster'])->name('department.course.delete');
        Route::get('delete-semester/{id}', [SubjectController::class, 'deleteSemesterFromSubject'])->name('department.delete.subject.semester');
        Route::post('add-faculty-master', [SubjectController::class, 'addFacultyMasterToSubject'])->name('dept.add.faculty.master');
        Route::put('combination-update/{id}', [SubjectController::class, 'updateCombination'])->name('department.combination.update');
        // Course Objectives
        Route::get('course/{id}/cso', [SubjectController::class, 'viewCourseSpecificObjective'])->name('department.view.cso');
        Route::get('course/{id}/cso-list', [SubjectController::class, 'getCsoListForCourse'])->name('department.get.cso.list');
        Route::post('course/objective/create', [SubjectController::class, 'createCourseSpecificObjective'])->name('department.create.course.specific.objective');
        Route::delete('delete-faculty-master/{id}', [SubjectController::class, 'deleteFacultyMasterFromSubject'])->name('department.faculty.delete');
        Route::post('add-new-course-master', [SubjectController::class, 'addNewCourseMaster'])->name('department.create.course.master');
        Route::put('/course-master/{id}', [SubjectController::class, 'updateCourseMaster'])->name('department.update.course.master');

        Route::put('/course-specific-objective/{id}', [SubjectController::class, 'updateCourseSpecificObjective'])->name('department.update.cso');
        Route::get('cso/{id}/delete', [SubjectController::class, 'deleteCourseSpecificObjective'])->name('department.delete.cso');

        Route::post('add-cso-subunit', [SubjectController::class, 'addCsoSubunit'])->name('department.add.cso.subunit');
        Route::get('cso-subunit/{id}/delete', [SubjectController::class, 'deleteCsoSubunit'])->name('department.delete.cso.subunit');
        Route::get('delete-subunit-taxonomy/{id}', [SubjectController::class, 'deleteCsoSubunitTaxonomy'])->name('department.delete.cso.subunit.taxonomy');
        Route::put('update-cso-subunit/{id}', [SubjectController::class, 'updateCsoSubunit'])->name('department.update.cso.subunit');

        Route::get('syllabus-manager', [SubjectController::class, 'syllabusManager'])->name('department.syllabus.manager');
        Route::get('course/{id}/cso-list', [SubjectController::class, 'getCsoListForCourse'])->name('department.get.cso.list');
        Route::post('create-syllabus', [SubjectController::class, 'createSyllabus'])->name('department.create.syllabus');
        Route::delete('syllabus-subunit/{id}', [SubjectController::class, 'deleteSyllabusSubunit'])->name('department.syllabus.subunit.delete');
        Route::delete('syllabus-co/{subjectId}/{batchId}/{semesterId}/{coId}', [SubjectController::class, 'deleteSyllabusCo'])->name('department.syllabus.co.delete');
        Route::get('syllabus-download-pdf', [SubjectController::class, 'downloadSyllabusPdf'])->name('department.syllabus.download.pdf');

        // Syllabus PDF Store
        Route::post('syllabus-pdf/store', [SyllabusPdfController::class, 'store'])->name('department.syllabus.pdf.store');
        Route::delete('syllabus-pdf/{id}', [SyllabusPdfController::class, 'destroy'])->name('department.syllabus.pdf.destroy');

        // Course Seat Manager
        Route::get('course-seats', [CourseSeatController::class, 'index'])->name('department.seats.index');
        Route::post('course-seats', [CourseSeatController::class, 'store'])->name('department.seats.store');
        Route::put('course-seats/{id}', [CourseSeatController::class, 'update'])->name('department.seats.update');
        Route::delete('course-seats/{id}', [CourseSeatController::class, 'destroy'])->name('department.seats.destroy');
        Route::post('course-seats/{id}/toggle', [CourseSeatController::class, 'toggle'])->name('department.seats.toggle');
        Route::post('course-seats/bulk-toggle', [CourseSeatController::class, 'bulkToggle'])->name('department.seats.bulk-toggle');

        // Course Offerings (FIFO registration module)
        Route::get('course-offerings', [CourseOfferingController::class, 'index'])->name('department.offerings.index');
        Route::post('course-offerings', [CourseOfferingController::class, 'store'])->name('department.offerings.store');
        Route::put('course-offerings/{id}', [CourseOfferingController::class, 'update'])->name('department.offerings.update');
        Route::delete('course-offerings/{id}', [CourseOfferingController::class, 'destroy'])->name('department.offerings.destroy');
        Route::post('course-offerings/{id}/toggle', [CourseOfferingController::class, 'toggleRegistration'])->name('department.offerings.toggle');
        Route::get('course-offerings/{id}/registrations', [CourseOfferingController::class, 'registrationList'])->name('department.offerings.registrations');
        Route::post('course-offerings/cancel-registration/{id}', [CourseOfferingController::class, 'adminCancelRegistration'])->name('department.offerings.cancel-registration');
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
        Route::get('substitution-available-teachers', [TimetableController::class, 'getAvailableTeachersForSubstitution'])->name('department.substitution.available-teachers');
        Route::post('substitution-save', [TimetableController::class, 'saveSubstitutions'])->name('department.substitution.save');
        Route::put('substitution-update/{routineId}', [TimetableController::class, 'updateSubstitution'])->name('department.substitution.update');
        Route::get('substitution-history', [TimetableController::class, 'getSubstitutionHistory'])->name('department.substitution.history');
        Route::get('substitution-history-page', [TimetableController::class, 'substitutionHistoryPage'])->name('department.substitution.history.page');
        Route::get('substitution-history-export', [TimetableController::class, 'exportSubstitutionHistory'])->name('department.substitution.history.export');

        //dept-admission-console
        Route::get('application-list', [AdmissionController::class, 'deptApplicationList'])->name('department.admission.list');
        Route::get('application-single/{id}', [AdmissionController::class, 'deptApplicationSingle'])->name('department.admission.application-single');
        Route::get('interview-list', [AdmissionController::class, 'deptInterviewList'])->name('department.admission.interview-list');

        //Faculty Access
        Route::get('faculty-access/{departmentId}/{departmentSlug}', [AccessController::class, 'facultyAccessList'])->name('department.faculty.access');
        Route::post('faculty-access', [AccessController::class, 'grantFacultyAccess'])->name('department.faculty.grant-access');
        Route::get('faculty-access-revoke/{id}', [AccessController::class, 'revokeFacultyAccess'])->name('department.faculty.revoke-access');
        Route::get('show-student-list', [SubjectController::class, 'showStudentList'])->name('department.show.student.list');
        Route::get('all-students', [SubjectController::class, 'allStudents'])->name('department.all.students');
        Route::get('student-profile', [SubjectController::class, 'studentProfile'])->name('department.student.profile');
        Route::get('faculty-list/{subjectId}', [SubjectController::class, 'deptFacultyList'])->name('department.faculty.list');
        // Department Activities
        Route::get('activities/{subjectId}', [DepartmentActivityController::class, 'index'])->name('department.activities.index');
        Route::post('activities', [DepartmentActivityController::class, 'store'])->name('department.activities.store');
        Route::get('activities/{id}/show', [DepartmentActivityController::class, 'show'])->name('department.activities.show');
        Route::put('activities/{id}', [DepartmentActivityController::class, 'update'])->name('department.activities.update');
        Route::delete('activities/{id}', [DepartmentActivityController::class, 'destroy'])->name('department.activities.destroy');
        Route::post('activities/{id}/status', [DepartmentActivityController::class, 'updateStatus'])->name('department.activities.status');
        Route::get('activities/{subjectId}/by-type', [DepartmentActivityController::class, 'getByType'])->name('department.activities.by-type');
        Route::get('acivity/participants/{activityId}', [DepartmentActivityController::class, 'activityParticipants'])->name('department.activities.participants');
        Route::post('activity/participants/store/{activityId}', [DepartmentActivityController::class, 'addParticipant'])->name('department.activities.participants.store');
        Route::delete('activity/participants/{id}', [DepartmentActivityController::class, 'removeParticipant'])->name('department.activities.participants.remove');
        Route::post('activity/participants/upload-report/{activityId}', [DepartmentActivityController::class, 'uploadActivityReport'])->name('department.activities.participants.upload-report');

        // Faculty Leave Sanction
        Route::get('leave', [DeptLeaveController::class, 'index'])->name('department.leave.index');
        Route::get('leave/{id}', [DeptLeaveController::class, 'show'])->name('department.leave.show');
        Route::post('leave/{id}/reject', [DeptLeaveController::class, 'reject'])->name('department.leave.reject');
        Route::post('leave/{id}/forward', [DeptLeaveController::class, 'forward'])->name('department.leave.forward');

        // Faculty Attendance Monitoring
        Route::get('attendance-monitor', [SubjectController::class, 'attendanceMonitor'])->name('department.attendance.monitor');
    });
    // ========================================================
    // Faculty routes

    Route::group(['prefix' => 'faculty',], function () {
        Route::get('dashboard', [FacultyDashboardController::class, 'index'])->name('faculty.dashboard');
        Route::get('timetable', [FacultyDashboardController::class, 'facultyTimetable'])->name('faculty.timetable');

        // Attendance Routes
        Route::get('attendance', [FacultyAttendanceController::class, 'index'])->name('faculty.attendance.index');
        Route::get('attendance/take/{routineId}', [FacultyAttendanceController::class, 'takeAttendance'])->name('faculty.attendance.take');
        Route::post('attendance/store', [FacultyAttendanceController::class, 'storeAttendance'])->name('faculty.attendance.store');
        Route::get('attendance/view', [FacultyAttendanceController::class, 'viewAttendance'])->name('faculty.attendance.view');
        Route::delete('attendance/{id}', [FacultyAttendanceController::class, 'deleteAttendance'])->name('faculty.attendance.delete');
        Route::get('attendance/create', [FacultyAttendanceController::class, 'getStudentList'])->name('faculty.attendance.create');
        Route::put('attendance/{id}', [FacultyAttendanceController::class, 'updateAttendance'])->name('faculty.attendance.update');
        // Remedial classes
        Route::get('remedial-classes', [FacultyAttendanceController::class, 'extraClasses'])->name('faculty.remedial.classes');
        Route::post('remedial-classes', [FacultyAttendanceController::class, 'storeRemedialAttendance'])->name('faculty.remedial.classes.store');
        Route::delete('remedial-classes/{id}', [FacultyAttendanceController::class, 'deleteExtraClass'])->name('faculty.remedial.classes.delete');
        // Route::get('attendance/create/remedial-class', [FacultyAttendanceController::class, 'getStudentListExtraClass'])->name('faculty.attendance.create.remedial-class');
        Route::get('attendance/view/remedial-class', [FacultyAttendanceController::class, 'viewExtraClassAttendance'])->name('faculty.attendance.view.remedial-class');

        Route::get('work-diary', [WorkDiaryController::class, 'index'])->name('faculty.workdiary');
        Route::get('work-diary/monthly-report', [WorkDiaryController::class, 'monthlyReport'])->name('faculty.workdiary.monthly.report');
        Route::get('work-diary/monthly-report/pdf', [WorkDiaryController::class, 'downloadMonthlyReportPdf'])->name('faculty.workdiary.monthly.report.pdf');
        Route::post('work-diary', [WorkDiaryController::class, 'store'])->name('faculty.workdiary.store');
        Route::put('work-diary/{id}', [WorkDiaryController::class, 'update'])->name('faculty.workdiary.update');
        Route::delete('work-diary/{id}', [WorkDiaryController::class, 'destroy'])->name('faculty.workdiary.destroy');
        Route::post('work-diary/{id}/toggle-status', [WorkDiaryController::class, 'toggleStatus'])->name('faculty.workdiary.toggle');
        Route::post('work-diary/holidays', [WorkDiaryController::class, 'storeHoliday'])->name('faculty.workdiary.holidays.store');
        Route::get('work-diary/holidays', [WorkDiaryController::class, 'getHolidays'])->name('faculty.workdiary.holidays.get');
        Route::delete('work-diary/holidays/{id}', [WorkDiaryController::class, 'deleteHoliday'])->name('faculty.workdiary.holidays.delete');

        // ── Mentorship ──────────────────────────────────────────────
        Route::prefix('mentorship')->group(function () {
            Route::get('/', [MentorshipController::class, 'index'])->name('faculty.mentorship.index');
            Route::post('group/{groupId}/add-by-roll', [MentorshipController::class, 'addStudentByRoll'])->name('faculty.mentorship.add-by-roll');
            Route::get('groups/create', [MentorshipController::class, 'createGroup'])->name('faculty.mentorship.group.create');
            Route::post('groups', [MentorshipController::class, 'storeGroup'])->name('faculty.mentorship.group.store');
            Route::get('groups/{id}', [MentorshipController::class, 'showGroup'])->name('faculty.mentorship.group.show');
            Route::get('groups/{id}/edit', [MentorshipController::class, 'editGroup'])->name('faculty.mentorship.group.edit');
            Route::put('groups/{id}', [MentorshipController::class, 'updateGroup'])->name('faculty.mentorship.group.update');
            Route::delete('groups/{id}', [MentorshipController::class, 'destroyGroup'])->name('faculty.mentorship.group.destroy');
            Route::get('students/search', [MentorshipController::class, 'searchStudents'])->name('faculty.mentorship.students.search');
            Route::post('groups/{groupId}/students', [MentorshipController::class, 'addStudents'])->name('faculty.mentorship.students.add');
            Route::delete('groups/{groupId}/students/{studentId}', [MentorshipController::class, 'removeStudent'])->name('faculty.mentorship.students.remove');
            Route::get('groups/{groupId}/sessions/create', [MentorshipController::class, 'createSession'])->name('faculty.mentorship.session.create');
            Route::post('groups/{groupId}/sessions', [MentorshipController::class, 'storeSession'])->name('faculty.mentorship.session.store');
            Route::get('sessions/{id}', [MentorshipController::class, 'showSession'])->name('faculty.mentorship.session.show');
            Route::post('sessions/{id}/attendance', [MentorshipController::class, 'saveAttendance'])->name('faculty.mentorship.session.attendance');
            Route::delete('sessions/{id}', [MentorshipController::class, 'destroySession'])->name('faculty.mentorship.session.destroy');
            Route::get('groups/{groupId}/assignments/create', [MentorshipController::class, 'createAssignment'])->name('faculty.mentorship.assignment.create');
            Route::post('groups/{groupId}/assignments', [MentorshipController::class, 'storeAssignment'])->name('faculty.mentorship.assignment.store');
            Route::get('assignments/{id}', [MentorshipController::class, 'showAssignment'])->name('faculty.mentorship.assignment.show');
            Route::post('submissions/{id}/grade', [MentorshipController::class, 'gradeSubmission'])->name('faculty.mentorship.submission.grade');
            Route::delete('assignments/{id}', [MentorshipController::class, 'destroyAssignment'])->name('faculty.mentorship.assignment.destroy');
            Route::post('groups/{groupId}/notes', [MentorshipController::class, 'storeNote'])->name('faculty.mentorship.note.store');
            Route::delete('notes/{id}', [MentorshipController::class, 'destroyNote'])->name('faculty.mentorship.note.destroy');
            Route::get('groups/{groupId}/students/{studentId}/profile', [MentorshipController::class, 'studentProfile'])->name('faculty.mentorship.student.profile');
        });

        Route::get('request-application', [FacultyRequestApplicationController::class, 'index'])->name('faculty.requestapplication');
        Route::get('payroll', [FacultyPayrollController::class, 'index'])->name('faculty.payroll');
        Route::get('payroll/bulk/download', [FacultyPayrollController::class, 'downloadBulk'])->name('faculty.payroll.bulk.download');
        Route::get('payroll/{id}', [FacultyPayrollController::class, 'show'])->name('faculty.payroll.show');
        Route::get('payroll/{id}/download', [FacultyPayrollController::class, 'download'])->name('faculty.payroll.download');
        Route::get('subjects', [FacultyDashboardController::class, 'subjects'])->name('faculty.subjects');
        Route::get('toggle-subunit-completion/{id}', [FacultyDashboardController::class, 'toggleSubunitCompletion'])->name('faculty.toggle.subunitcompletion');

        // Learning Resources Routes
        Route::get('learning-resources/subunit/{subunitId}', [App\Faculty\Http\Controllers\LearningResourceController::class, 'index'])->name('faculty.resources.index');
        Route::post('learning-resources', [App\Faculty\Http\Controllers\LearningResourceController::class, 'store'])->name('faculty.resources.store');
        Route::get('learning-resources/{id}', [App\Faculty\Http\Controllers\LearningResourceController::class, 'show'])->name('faculty.resources.show');
        Route::delete('learning-resources/{id}', [App\Faculty\Http\Controllers\LearningResourceController::class, 'destroy'])->name('faculty.resources.destroy');
        Route::get('learning-resources/subunit/{subunitId}/ajax', [App\Faculty\Http\Controllers\LearningResourceController::class, 'getResourcesBySubunit'])->name('faculty.resources.ajax');

        // Question Bank Routes
        Route::post('question-bank', [App\Faculty\Http\Controllers\QuestionBankController::class, 'store'])->name('faculty.questions.store');
        Route::delete('question-bank/{id}', [App\Faculty\Http\Controllers\QuestionBankController::class, 'destroy'])->name('faculty.questions.destroy');

        Route::get('profile', [FacultyDashboardController::class, 'profile'])->name('faculty.profile');
        Route::put('profile/update', [FacultyDashboardController::class, 'updateProfile'])->name('faculty.profile.update');
        Route::post('profile/photo', [FacultyDashboardController::class, 'updatePhoto'])->name('faculty.profile.photo');

        // Leave Application Routes
        Route::get('leave', [FacultyLeaveController::class, 'index'])->name('faculty.leave.index');
        Route::get('leave/history', [FacultyLeaveController::class, 'history'])->name('faculty.leave.history');
        Route::get('leave/create', [FacultyLeaveController::class, 'create'])->name('faculty.leave.create');
        Route::post('leave', [FacultyLeaveController::class, 'store'])->name('faculty.leave.store');
        Route::get('leave/{id}', [FacultyLeaveController::class, 'show'])->name('faculty.leave.show');
        Route::get('leave/{id}/edit', [FacultyLeaveController::class, 'edit'])->name('faculty.leave.edit');
        Route::put('leave/{id}', [FacultyLeaveController::class, 'update'])->name('faculty.leave.update');
        Route::post('leave/{id}/cancel', [FacultyLeaveController::class, 'cancel'])->name('faculty.leave.cancel');
        Route::delete('leave/{id}', [FacultyLeaveController::class, 'destroy'])->name('faculty.leave.destroy');

        // Internal Marks (FA) Routes
        Route::get('internal-marks', [InternalMarksController::class, 'index'])->name('faculty.internal-marks.index');
        Route::get('internal-marks/enter', [InternalMarksController::class, 'enter'])->name('faculty.internal-marks.enter');
        Route::post('internal-marks', [InternalMarksController::class, 'store'])->name('faculty.internal-marks.store');
        Route::get('internal-marks/view', [InternalMarksController::class, 'view'])->name('faculty.internal-marks.view');
    });


    // ========================================================
    //COE route

    Route::group(['prefix' => '/coe',], function () {
        Route::get('dashboard', [CoeDashboardController::class, 'index'])->name('coe.dashboard');
        // AJAX filter route for COE Dashboard
        Route::get('dashboard/filter', [CoeDashboardController::class, 'filter'])->name('coe.dashboard.filter');

        // COE Student Master
        Route::get('students', [CoeDashboardController::class, 'studentMaster'])->name('coe.students.index');

        // COE Attendance Routes
        Route::get('attendance', [CoeAttendanceController::class, 'index'])->name('coe.attendance.index');
        Route::get('attendance/take', [CoeAttendanceController::class, 'take'])->name('coe.attendance.take');
        Route::post('attendance/store', [CoeAttendanceController::class, 'store'])->name('coe.attendance.store');
        Route::get('attendance/view', [CoeAttendanceController::class, 'view'])->name('coe.attendance.view');
        Route::delete('attendance/delete/{id}', [CoeAttendanceController::class, 'delete'])->name('coe.attendance.delete');
        Route::get('attendance/room-wise/{examId}', [CoeAttendanceController::class, 'roomWise'])->name('coe.attendance.room-wise');
        Route::post('attendance/update-status', [CoeAttendanceController::class, 'updateStatus'])->name('coe.attendance.update-status');

        // COE Exam Management Routes
        Route::get('exams', [CoeExamController::class, 'index'])->name('coe.exams.index');
        Route::get('exams/create', [CoeExamController::class, 'create'])->name('coe.exams.create');
        Route::post('exams', [CoeExamController::class, 'store'])->name('coe.exams.store');
        Route::get('exams/{id}', [CoeExamController::class, 'show'])->name('coe.exams.show');
        Route::get('exams/{id}/edit', [CoeExamController::class, 'edit'])->name('coe.exams.edit');
        Route::put('exams/{id}', [CoeExamController::class, 'update'])->name('coe.exams.update');
        Route::delete('exams/{id}', [CoeExamController::class, 'destroy'])->name('coe.exams.destroy');

        // COE Dummy Numbers Routes
        Route::get('dummy-numbers', [DummyNumberController::class, 'index'])->name('coe.dummy-numbers.index');
        Route::get('dummy-numbers/create', [DummyNumberController::class, 'create'])->name('coe.dummy-numbers.create');
        Route::get('dummy-numbers/export', [DummyNumberController::class, 'export'])->name('coe.dummy-numbers.export');
        Route::post('dummy-numbers', [DummyNumberController::class, 'store'])->name('coe.dummy-numbers.store');
        Route::post('dummy-numbers/auto-generate', [DummyNumberController::class, 'autoGenerate'])->name('coe.dummy-numbers.auto-generate');
        Route::post('dummy-numbers/lock', [DummyNumberController::class, 'lock'])->name('coe.dummy-numbers.lock');
        Route::post('dummy-numbers/unlock', [DummyNumberController::class, 'unlock'])->name('coe.dummy-numbers.unlock');
        Route::get('dummy-numbers/{id}', [DummyNumberController::class, 'show'])->name('coe.dummy-numbers.show');
        Route::get('dummy-numbers/{id}/edit', [DummyNumberController::class, 'edit'])->name('coe.dummy-numbers.edit');
        Route::put('dummy-numbers/{id}', [DummyNumberController::class, 'update'])->name('coe.dummy-numbers.update');
        Route::delete('dummy-numbers/{id}', [DummyNumberController::class, 'destroy'])->name('coe.dummy-numbers.destroy');

        // COE Regulation Management Routes
        Route::get('regulations', [CoeRegulationController::class, 'index'])->name('coe.regulations.index');
        Route::get('regulations/create', [CoeRegulationController::class, 'create'])->name('coe.regulations.create');
        Route::post('regulations', [CoeRegulationController::class, 'store'])->name('coe.regulations.store');
        Route::get('regulations/{id}', [CoeRegulationController::class, 'show'])->name('coe.regulations.show');
        Route::get('regulations/{id}/edit', [CoeRegulationController::class, 'edit'])->name('coe.regulations.edit');
        Route::put('regulations/{id}', [CoeRegulationController::class, 'update'])->name('coe.regulations.update');
        Route::delete('regulations/{id}', [CoeRegulationController::class, 'destroy'])->name('coe.regulations.destroy');

        // COE Marks Entry Routes
        Route::get('marks', [ExamMarksController::class, 'index'])->name('coe.marks.index');
        Route::get('marks/entry', [ExamMarksController::class, 'entry'])->name('coe.marks.entry');
        Route::post('marks/store-single', [ExamMarksController::class, 'storeSingle'])->name('coe.marks.store-single')->middleware('check.device.access');
        Route::post('marks/bulk-entry', [ExamMarksController::class, 'bulkEntry'])->name('coe.marks.bulk-entry')->middleware('check.device.access');

        // COE Marks Lock/Unlock Routes
        Route::post('marks/lock', [ExamMarksController::class, 'lockMarks'])->name('coe.marks.lock');
        Route::post('marks/unlock', [ExamMarksController::class, 'unlockMarks'])->name('coe.marks.unlock');
        Route::post('marks/coe-override', [ExamMarksController::class, 'coeOverrideUpdate'])->name('coe.marks.coe-override');
        Route::get('marks/audit-log', [ExamMarksController::class, 'auditLog'])->name('coe.marks.audit-log');
        Route::get('marks/locks', [ExamMarksController::class, 'locksIndex'])->name('coe.marks.locks');

        // COE MAC Whitelist Routes
        Route::get('marks/whitelist', [ExamMarksController::class, 'whitelistIndex'])->name('coe.marks.whitelist');
        Route::post('marks/whitelist', [ExamMarksController::class, 'whitelistStore'])->name('coe.marks.whitelist.store');
        Route::delete('marks/whitelist/{id}', [ExamMarksController::class, 'whitelistDestroy'])->name('coe.marks.whitelist.destroy');

        Route::get('marks/{id}', [ExamMarksController::class, 'show'])->name('coe.marks.show');

        // COE Packet Generation Routes
        Route::get('packets', [ExamPacketController::class, 'index'])->name('coe.packets.index');
        Route::get('packets/generate', [ExamPacketController::class, 'generate'])->name('coe.packets.generate');
        Route::post('packets/generate', [ExamPacketController::class, 'store'])->name('coe.packets.store');
        Route::post('packets/assign-evaluator', [ExamPacketController::class, 'assignEvaluator'])->name('coe.packets.assign-evaluator');
        Route::post('packets/update-status', [ExamPacketController::class, 'updateStatus'])->name('coe.packets.update-status');
        Route::get('packets/{id}', [ExamPacketController::class, 'show'])->name('coe.packets.show');

        // COE Packet Barcode Tracking Routes
        Route::post('packets/barcodes/generate', [ExamPacketBarcodeController::class, 'generateBarcodes'])->name('coe.packets.barcodes.generate');
        Route::get('packets/barcodes/print', [ExamPacketBarcodeController::class, 'printLabels'])->name('coe.packets.barcodes.print');
        Route::get('packets/barcodes/scanner', [ExamPacketBarcodeController::class, 'scanner'])->name('coe.packets.barcodes.scanner');
        Route::post('packets/barcodes/scan', [ExamPacketBarcodeController::class, 'processScan'])->name('coe.packets.barcodes.scan');
        Route::get('packets/barcodes/lookup', [ExamPacketBarcodeController::class, 'lookup'])->name('coe.packets.barcodes.lookup');
        Route::get('packets/barcodes/tracking', [ExamPacketBarcodeController::class, 'tracking'])->name('coe.packets.barcodes.tracking');
        Route::get('packets/barcodes/history/{packetId}', [ExamPacketBarcodeController::class, 'scanHistory'])->name('coe.packets.barcodes.history');

        // COE Internal Marks Review (FA Change Log)
        Route::get('internal-marks-review', [CoeInternalMarksReviewController::class, 'index'])->name('coe.internal-marks-review.index');

        // D.COE Management Routes (COE only)
        Route::group(['prefix' => 'dcoe-management'], function () {
            Route::get('/', [DcoeManagementController::class, 'index'])->name('coe.dcoe.index');
            Route::post('/', [DcoeManagementController::class, 'store'])->name('coe.dcoe.store');
            Route::get('/{id}/edit', [DcoeManagementController::class, 'edit'])->name('coe.dcoe.edit');
            Route::put('/{id}', [DcoeManagementController::class, 'update'])->name('coe.dcoe.update');
            Route::post('/{id}/toggle-status', [DcoeManagementController::class, 'toggleStatus'])->name('coe.dcoe.toggle-status');
            Route::delete('/{id}', [DcoeManagementController::class, 'destroy'])->name('coe.dcoe.destroy');
        });
    });
    // ========================================================
    // Principal Module Routes

    Route::group(['prefix' => '/principal',], function () {
        Route::get('dashboard', [PrincipalController::class, 'dashboard'])->name('principal.dashboard');
        Route::get('students', [PrincipalController::class, 'students'])->name('principal.students.index');
        Route::get('{id}/student-profile/{rollno}', [PrincipalController::class, 'studentProfile'])->name('principal.student.profile');
        Route::get('faculty', [PrincipalController::class, 'faculty'])->name('principal.faculty.index');
        Route::get('faculty/{id}', [PrincipalController::class, 'facultyDetail'])->name('principal.faculty.detail');
        Route::get('faculty/{id}/timetable', [PrincipalController::class, 'facultyTimetable'])->name('principal.faculty.timetable');
        Route::get('faculty/{id}/work-diary', [PrincipalController::class, 'facultyWorkDiary'])->name('principal.faculty.work-diary');
        Route::get('courses', [PrincipalController::class, 'courses'])->name('principal.courses.index');
        Route::get('courses/{id}', [PrincipalController::class, 'courseDetail'])->name('principal.courses.detail');
        Route::get('syllabus', [PrincipalController::class, 'subjectSyllabus'])->name('principal.syllabus.index');
        Route::get('syllabus/{id}', [PrincipalController::class, 'subjectSyllabusDetail'])->name('principal.syllabus.detail');
        Route::get('classes', [PrincipalController::class, 'classes'])->name('principal.classes.index');
        Route::get('admissions', [PrincipalController::class, 'admissions'])->name('principal.admissions.index');

        // Fee Management
        Route::get('fees', [PrincipalController::class, 'studentFees'])->name('principal.fees.index');
        Route::get('fees/defaulters', [PrincipalController::class, 'feeDefaulters'])->name('principal.fees.defaulters');

        // API Score Reports (Academic Performance Indicator)
        Route::get('api-scores/reports', [ApiScoreController::class, 'reports'])->name('principal.api-scores.reports');
        Route::get('api-scores/faculty/{facultyId}/report', [ApiScoreController::class, 'facultyReport'])->name('principal.api-scores.faculty-report');

        // Leave Management
        Route::get('leaves', [PrincipalController::class, 'leaves'])->name('principal.leaves.index');
        Route::post('leaves/{id}/action', [PrincipalController::class, 'leaveAction'])->name('principal.leaves.action');

        // Work Diary
        Route::get('work-diary', [PrincipalController::class, 'workDiaryOverview'])->name('principal.work-diary.overview');
        Route::post('work-diary/{id}/approve', [PrincipalController::class, 'approveWorkDiary'])->name('principal.work-diary.approve');
        Route::post('work-diary/bulk-approve', [PrincipalController::class, 'bulkApproveWorkDiary'])->name('principal.work-diary.bulk-approve');

        // Vice-Principal Management (Principal only)
        Route::get('vp-management', [PrincipalController::class, 'vpIndex'])->name('principal.vp.index');
        Route::post('vp-management', [PrincipalController::class, 'vpStore'])->name('principal.vp.store');
        Route::put('vp-management/{id}', [PrincipalController::class, 'vpUpdate'])->name('principal.vp.update');
        Route::post('vp-management/{id}/toggle-status', [PrincipalController::class, 'vpToggleStatus'])->name('principal.vp.toggle-status');
        Route::delete('vp-management/{id}', [PrincipalController::class, 'vpDestroy'])->name('principal.vp.destroy');
    });

    // ========================================================
    // Event Coordinator
    Route::group(['prefix' => '/event-coordinator',], function () {
        Route::get('dashboard', [EventCoordinatorController::class, 'dashboard'])->name('event-coordinator.dashboard');

        // Events
        Route::get('events', [EventCoordinatorController::class, 'eventsIndex'])->name('event-coordinator.events.index');
        Route::get('events/create', [EventCoordinatorController::class, 'eventsCreate'])->name('event-coordinator.events.create');
        Route::post('events', [EventCoordinatorController::class, 'eventsStore'])->name('event-coordinator.events.store');
        Route::get('events/{event}', [EventCoordinatorController::class, 'eventsShow'])->name('event-coordinator.events.show');
        Route::get('events/{event}/edit', [EventCoordinatorController::class, 'eventsEdit'])->name('event-coordinator.events.edit');
        Route::put('events/{event}', [EventCoordinatorController::class, 'eventsUpdate'])->name('event-coordinator.events.update');
        Route::delete('events/{event}', [EventCoordinatorController::class, 'eventsDestroy'])->name('event-coordinator.events.destroy');

        // Programs (nested under event)
        Route::get('programs/{program}/edit', [EventCoordinatorController::class, 'programsEdit'])->name('event-coordinator.programs.edit');
        Route::post('events/{event}/programs', [EventCoordinatorController::class, 'programsStore'])->name('event-coordinator.programs.store');
        Route::put('programs/{program}', [EventCoordinatorController::class, 'programsUpdate'])->name('event-coordinator.programs.update');
        Route::delete('programs/{program}', [EventCoordinatorController::class, 'programsDestroy'])->name('event-coordinator.programs.destroy');

        // Faculty Duties
        Route::post('events/{event}/duties', [EventCoordinatorController::class, 'dutiesStore'])->name('event-coordinator.duties.store');
        Route::put('duties/{duty}', [EventCoordinatorController::class, 'dutiesUpdate'])->name('event-coordinator.duties.update');
        Route::delete('duties/{duty}', [EventCoordinatorController::class, 'dutiesDestroy'])->name('event-coordinator.duties.destroy');

        // Fund Transactions
        Route::post('events/{event}/fund', [EventCoordinatorController::class, 'fundStore'])->name('event-coordinator.fund.store');
        Route::delete('fund/{transaction}', [EventCoordinatorController::class, 'fundDestroy'])->name('event-coordinator.fund.destroy');

        // Sponsors
        Route::post('events/{event}/sponsors', [EventCoordinatorController::class, 'sponsorsStore'])->name('event-coordinator.sponsors.store');
        Route::put('sponsors/{sponsor}', [EventCoordinatorController::class, 'sponsorsUpdate'])->name('event-coordinator.sponsors.update');
        Route::delete('sponsors/{sponsor}', [EventCoordinatorController::class, 'sponsorsDestroy'])->name('event-coordinator.sponsors.destroy');

        // Report
        Route::get('events/{event}/report', [EventCoordinatorController::class, 'report'])->name('event-coordinator.report');
    });

    // ========================================================
    // HR Module
    Route::group(['prefix' => '/hr'], function () {
        Route::get('dashboard', function () {
            return view('hr.dashboard');
        })->name('hr.dashboard');

        // Faculty Management
        Route::get('faculty', [HrFacultyController::class, 'index'])->name('hr.faculty.index');
        Route::get('faculty/create', [HrFacultyController::class, 'create'])->name('hr.faculty.create');
        Route::post('faculty', [HrFacultyController::class, 'store'])->name('hr.faculty.store');
        Route::get('faculty/{id}', [HrFacultyController::class, 'show'])->name('hr.faculty.show');
        Route::get('faculty/{id}/edit', [HrFacultyController::class, 'edit'])->name('hr.faculty.edit');
        Route::put('faculty/{id}', [HrFacultyController::class, 'update'])->name('hr.faculty.update');
        Route::delete('faculty/{id}', [HrFacultyController::class, 'destroy'])->name('hr.faculty.destroy');
        Route::post('faculty/{id}/mark-left', [HrFacultyController::class, 'markAsLeft'])->name('hr.faculty.mark-left');
        Route::post('faculty/{id}/restore', [HrFacultyController::class, 'restore'])->name('hr.faculty.restore');
        Route::post('faculty/left', [HrFacultyController::class, 'deactivateFaculty'])->name('hr.faculty.left');
        // Leave Management
        Route::get('leave', [HrLeaveController::class, 'index'])->name('hr.leave.index');
        Route::get('leave/statistics', [HrLeaveController::class, 'statistics'])->name('hr.leave.statistics');
        Route::post('leave/bulk-approve', [HrLeaveController::class, 'bulkApprove'])->name('hr.leave.bulk-approve');
        Route::get('leave/{id}', [HrLeaveController::class, 'show'])->name('hr.leave.show');
        Route::get('leave/{id}/review', [HrLeaveController::class, 'reviewForm'])->name('hr.leave.review');
        Route::post('leave/{id}/approve', [HrLeaveController::class, 'approve'])->name('hr.leave.approve');
        Route::post('leave/{id}/reject', [HrLeaveController::class, 'reject'])->name('hr.leave.reject');
        Route::post('leave/{id}/forward', [HrLeaveController::class, 'forward'])->name('hr.leave.forward');
        Route::post('leave/{id}/change-type', [HrLeaveController::class, 'changeLeaveType'])->name('hr.leave.change-type');

        // Leave Category Master (Moved from Department to HR)
        Route::get('leave-categories', [HrLeaveController::class, 'categoryIndex'])->name('hr.leave.categories');
        Route::post('leave-categories', [HrLeaveController::class, 'categoryStore'])->name('hr.leave.categories.store');
        Route::put('leave-categories/{id}', [HrLeaveController::class, 'categoryUpdate'])->name('hr.leave.categories.update');
        Route::post('leave-categories/{id}/toggle', [HrLeaveController::class, 'categoryToggle'])->name('hr.leave.categories.toggle');

        // FDP (Faculty Development Program) Management
        Route::get('fdp', [HrFdpController::class, 'index'])->name('hr.fdp.index');
        Route::get('fdp/create', [HrFdpController::class, 'create'])->name('hr.fdp.create');
        Route::post('fdp', [HrFdpController::class, 'store'])->name('hr.fdp.store');
        Route::get('fdp/{id}', [HrFdpController::class, 'show'])->name('hr.fdp.show');
        Route::get('fdp/{id}/edit', [HrFdpController::class, 'edit'])->name('hr.fdp.edit');
        Route::put('fdp/{id}', [HrFdpController::class, 'update'])->name('hr.fdp.update');
        Route::delete('fdp/{id}', [HrFdpController::class, 'destroy'])->name('hr.fdp.destroy');

        // FDP Participants
        Route::get('fdp/{id}/add-participant', [HrFdpController::class, 'addParticipantForm'])->name('hr.fdp.add-participant');
        Route::post('fdp/{id}/participants', [HrFdpController::class, 'addParticipant'])->name('hr.fdp.participants.store');
        Route::post('fdp/{id}/participants/{participantId}/approve', [HrFdpController::class, 'approveParticipant'])->name('hr.fdp.participants.approve');
        Route::post('fdp/{id}/participants/{participantId}/complete', [HrFdpController::class, 'completeParticipant'])->name('hr.fdp.participants.complete');

        // FDP Faculty Tracker
        Route::get('fdp/tracker/faculty', [HrFdpController::class, 'facultyTracker'])->name('hr.fdp.faculty-tracker');

        // Vacancy Management
        Route::get('vacancy', [HrVacancyController::class, 'index'])->name('hr.vacancy.index');
        Route::get('vacancy/create', [HrVacancyController::class, 'create'])->name('hr.vacancy.create');
        Route::post('vacancy', [HrVacancyController::class, 'store'])->name('hr.vacancy.store');
        Route::get('vacancy/{id}', [HrVacancyController::class, 'show'])->name('hr.vacancy.show');
        Route::get('vacancy/{id}/edit', [HrVacancyController::class, 'edit'])->name('hr.vacancy.edit');
        Route::put('vacancy/{id}', [HrVacancyController::class, 'update'])->name('hr.vacancy.update');
        Route::delete('vacancy/{id}', [HrVacancyController::class, 'destroy'])->name('hr.vacancy.destroy');
        Route::post('vacancy/{id}/publish', [HrVacancyController::class, 'publish'])->name('hr.vacancy.publish');
        Route::post('vacancy/{id}/close', [HrVacancyController::class, 'close'])->name('hr.vacancy.close');

        // Vacancy Applications
        Route::get('vacancy/{id}/applications', [HrVacancyController::class, 'applications'])->name('hr.vacancy.applications');
        Route::get('vacancy/{vacancyId}/applications/{applicationId}', [HrVacancyController::class, 'showApplication'])->name('hr.vacancy.application.show');
        Route::post('vacancy/{vacancyId}/applications/{applicationId}/update-status', [HrVacancyController::class, 'updateApplicationStatus'])->name('hr.vacancy.application.update-status');

        // Pay Matrix Management
        Route::get('pay-matrix', [HrPayMatrixController::class, 'index'])->name('hr.pay-matrix.index');
        Route::get('pay-matrix/create', [HrPayMatrixController::class, 'create'])->name('hr.pay-matrix.create');
        Route::post('pay-matrix', [HrPayMatrixController::class, 'store'])->name('hr.pay-matrix.store');
        Route::get('pay-matrix/{id}', [HrPayMatrixController::class, 'show'])->name('hr.pay-matrix.show');
        Route::get('pay-matrix/{id}/edit', [HrPayMatrixController::class, 'edit'])->name('hr.pay-matrix.edit');
        Route::put('pay-matrix/{id}', [HrPayMatrixController::class, 'update'])->name('hr.pay-matrix.update');
        Route::delete('pay-matrix/{id}', [HrPayMatrixController::class, 'destroy'])->name('hr.pay-matrix.destroy');
        Route::post('pay-matrix/{id}/archive', [HrPayMatrixController::class, 'archive'])->name('hr.pay-matrix.archive');
        Route::post('pay-matrix/{id}/duplicate', [HrPayMatrixController::class, 'duplicate'])->name('hr.pay-matrix.duplicate');
        Route::post('pay-matrix/{id}/apply-to-faculty', [HrPayMatrixController::class, 'applyToFaculty'])->name('hr.pay-matrix.apply-to-faculty');
        Route::get('pay-matrix/{id}/preview', [HrPayMatrixController::class, 'preview'])->name('hr.pay-matrix.preview');

        // Designation Master
        Route::resource('designations', HrDesignationController::class, ['as' => 'hr']);

        // Grade Level Master
        Route::resource('grade-levels', HrGradeLevelController::class, ['as' => 'hr']);

        // Payroll Management
        Route::get('payroll', [HrPayrollController::class, 'index'])->name('hr.payroll.index');
        Route::get('payroll/generate', [HrPayrollController::class, 'generateForm'])->name('hr.payroll.generate');
        Route::post('payroll/assign-pay-matrix', [HrPayrollController::class, 'assignPayMatrix'])->name('hr.payroll.assign-pay-matrix');
        Route::post('payroll/bulk-generate', [HrPayrollController::class, 'bulkGenerate'])->name('hr.payroll.bulk-generate');
        Route::get('payroll/{id}', [HrPayrollController::class, 'show'])->name('hr.payroll.show');
        Route::post('payroll/{id}/approve', [HrPayrollController::class, 'approve'])->name('hr.payroll.approve');
        Route::post('payroll/{id}/mark-paid', [HrPayrollController::class, 'markPaid'])->name('hr.payroll.mark-paid');
        Route::delete('payroll/{id}', [HrPayrollController::class, 'destroy'])->name('hr.payroll.destroy');
        Route::get('payroll/statistics/overview', [HrPayrollController::class, 'statistics'])->name('hr.payroll.statistics');

        // API Score Management (Academic Performance Indicator)
        Route::get('api-scores', [ApiScoreController::class, 'index'])->name('hr.api-scores.index');
        Route::get('api-scores/create', [ApiScoreController::class, 'create'])->name('hr.api-scores.create');
        Route::post('api-scores', [ApiScoreController::class, 'store'])->name('hr.api-scores.store');
        Route::get('api-scores/{id}', [ApiScoreController::class, 'show'])->name('hr.api-scores.show');
        Route::get('api-scores/{id}/edit', [ApiScoreController::class, 'edit'])->name('hr.api-scores.edit');
        Route::put('api-scores/{id}', [ApiScoreController::class, 'update'])->name('hr.api-scores.update');
        Route::delete('api-scores/{id}', [ApiScoreController::class, 'destroy'])->name('hr.api-scores.destroy');
        Route::post('api-scores/{id}/mark-final', [ApiScoreController::class, 'markFinal'])->name('hr.api-scores.mark-final');
        Route::get('api-scores/faculty/{facultyId}/report', [ApiScoreController::class, 'facultyReport'])->name('hr.api-scores.faculty-report');

        // API Score Academic Years
        Route::get('api-scores/years/manage', [ApiScoreController::class, 'academicYears'])->name('hr.api-scores.academic-years');
        Route::post('api-scores/years', [ApiScoreController::class, 'storeAcademicYear'])->name('hr.api-scores.academic-years.store');

        // API Score Reports
        Route::get('api-scores/reports/analytics', [ApiScoreController::class, 'reports'])->name('hr.api-scores.reports');
    });

    // Public Vacancy Routes (for website)
    Route::get('careers', [HrVacancyController::class, 'publicIndex'])->name('vacancies.public.index');
    Route::get('careers/{id}', [HrVacancyController::class, 'publicShow'])->name('vacancies.public.show');
    Route::get('careers/{id}/apply', [HrVacancyController::class, 'publicApplyForm'])->name('vacancies.public.apply-form');
    Route::post('careers/{id}/apply', [HrVacancyController::class, 'publicApply'])->name('vacancies.public.apply');


    //Testing route
    Route::group(['prefix' => '/test',], function () {
        Route::get('fix-admission-enrollment', [TestController::class, 'fixAdmissionEnrollment']); //run once to fix enrollment number in student master table
        // Route::get('fee-issue-fix', [TestController::class, 'feesIssueFixing']); //run once to test fee structure creation logic
        // Route::get('create-student-login', [TestController::class, 'createStudentLogin']); //run once to create student login for all students in student master
        // Route::get('delete-student-login', [TestController::class, 'delAllStudentAccount']); //run once to delete all student login (if needed)
        // Route::get('fix', [TestController::class, 'rollnoFixStudentPayment']);   //run once to fix rollno in student payment table
        // Route::get('dept-campus-mapping', [TestController::class, 'DeptCampusMapping']); //run once to fix department campus mapping
        // Route::get('mailing', [TestController::class, 'mailTest']);//run once to test mailing configuration
        // Route::get('sms', [TestController::class, 'smsTest']);//run once to test sms configuration
        // Route::get('install-new-programid', [TestController::class, 'studentMasterProgramFixing']);//run once to fix program id in student master table
    });
});
