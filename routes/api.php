<?php

use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\ErpNaacWebhookController;
use App\Http\Controllers\BiometricWebhookController;
use App\Http\Controllers\FacultyApiController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentAttendanceScanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Exam Registration
Route::middleware(['auth:sanctum'])->post('/exams/register', [\App\Http\Controllers\ExamRegistrationController::class, 'register']);

// Admit Card Download
Route::middleware(['auth:sanctum'])->get('/exams/admit-card', [\App\Http\Controllers\AdmitCardController::class, 'download']);

// Marks Submission (with device check)
Route::middleware(['auth:sanctum', 'check.device.access'])->post('/marks/submit', [\App\Http\Controllers\MarksController::class, 'submit']);

// Results Publish
Route::middleware(['auth:sanctum'])->post('/results/publish', [\App\Http\Controllers\ResultController::class, 'publish']);

// Backlog Registration
Route::middleware(['auth:sanctum'])->post('/backlog/register', [\App\Http\Controllers\BacklogController::class, 'register']);

// Revaluation Application
Route::middleware(['auth:sanctum'])->post('/revaluation/apply', [\App\Http\Controllers\RevaluationController::class, 'apply']);

// Invigilation Duty Management
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/invigilation/assign', [\App\Http\Controllers\InvigilationController::class, 'assign']);
    Route::get('/invigilation/duty-chart', [\App\Http\Controllers\InvigilationController::class, 'dutyChart']);
    Route::get('/invigilation/schedule/download', [\App\Http\Controllers\InvigilationController::class, 'downloadSchedule']);
});


Route::group(['prefix' => 'faculty'], function () {
    Route::post('login', [LoginController::class, 'facultyLogin']);
    Route::get('profile/{id}', [FacultyApiController::class, 'profile']);
});

Route::group(['prefix' => 'student'], function () {
    Route::post('login', [StudentApiController::class, 'login']);
    Route::post('student-profile', [StudentApiController::class, 'stdprofile']);


    // Student QR attendance scan endpoint (temporary signed URL) - POST only.
    Route::post('attendance/scan', [StudentAttendanceScanController::class, 'mark'])->name('student.attendance.scan');
});

//recieve biometric  attendance direct from Hikvision IVMS 4200 Device
Route::post('/biometric/attendance', [BiometricWebhookController::class, 'receiveAttendance']);

Route::prefix('webhooks/erp/naac')->middleware('verify.erp.naac.webhook')->group(function () {
    Route::get('ping', [ErpNaacWebhookController::class, 'ping']);
    Route::post('', [ErpNaacWebhookController::class, 'webhook']);

    Route::get('snapshot', [ErpNaacWebhookController::class, 'snapshot']);
    Route::get('cycles/full', [ErpNaacWebhookController::class, 'listCyclesFull']);
    Route::get('cycles', [ErpNaacWebhookController::class, 'listCycles']);

    Route::post('cycles', [ErpNaacWebhookController::class, 'upsertCycle']);
    Route::put('cycles/{id}', [ErpNaacWebhookController::class, 'updateCycle']);
    Route::delete('cycles/{id}', [ErpNaacWebhookController::class, 'deleteCycle']);

    Route::post('sessions', [ErpNaacWebhookController::class, 'upsertSession']);
    Route::put('sessions/{id}', [ErpNaacWebhookController::class, 'updateSession']);
    Route::delete('sessions/{id}', [ErpNaacWebhookController::class, 'deleteSession']);

    Route::post('supporting-docs', [ErpNaacWebhookController::class, 'upsertSupportingDoc']);
    Route::put('supporting-docs/{id}', [ErpNaacWebhookController::class, 'updateSupportingDoc']);
    Route::delete('supporting-docs/{id}', [ErpNaacWebhookController::class, 'deleteSupportingDoc']);

    Route::post('multi-docs', [ErpNaacWebhookController::class, 'upsertMultiDoc']);
    Route::put('multi-docs/{id}', [ErpNaacWebhookController::class, 'updateMultiDoc']);
    Route::delete('multi-docs/{id}', [ErpNaacWebhookController::class, 'deleteMultiDoc']);

    Route::post('criterian-docs', [ErpNaacWebhookController::class, 'upsertCriterianDoc']);
    Route::put('criterian-docs/{id}', [ErpNaacWebhookController::class, 'updateCriterianDoc']);
    Route::delete('criterian-docs/{id}', [ErpNaacWebhookController::class, 'deleteCriterianDoc']);

    Route::post('multi-doc-items', [ErpNaacWebhookController::class, 'upsertMultiDocItem']);
    Route::put('multi-doc-items/{id}', [ErpNaacWebhookController::class, 'updateMultiDocItem']);
    Route::delete('multi-doc-items/{id}', [ErpNaacWebhookController::class, 'deleteMultiDocItem']);

    Route::post('criterian-doc-items', [ErpNaacWebhookController::class, 'upsertCriterianDocItem']);
    Route::put('criterian-doc-items/{id}', [ErpNaacWebhookController::class, 'updateCriterianDocItem']);
    Route::delete('criterian-doc-items/{id}', [ErpNaacWebhookController::class, 'deleteCriterianDocItem']);

    Route::post('single-content', [ErpNaacWebhookController::class, 'createSingleContent']);
    Route::put('single-content/{id}', [ErpNaacWebhookController::class, 'updateSingleContent']);
});
