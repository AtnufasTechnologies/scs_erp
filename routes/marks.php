<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'check.device.access'])->group(function () {
  // Example marks entry routes
  Route::post('marks/entry', [App\Http\Controllers\MarksController::class, 'store']);
  Route::put('marks/entry/{id}', [App\Http\Controllers\MarksController::class, 'update']);
});
