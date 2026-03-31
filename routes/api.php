<?php

use App\Http\Controllers\FeePaymentController;
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
