<?php

use App\Http\Controllers\AuthController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('profile', [AuthController::class, 'userProfile']);

  Route::prefix('queue')->group(function () {
    Route::get('/', [QueueController::class, 'index']); // Fetch queue list
    Route::post('/add', [QueueController::class, 'store']); // Add patient to queue
    Route::post('/next', [QueueController::class, 'callNext']); // Call next patient
    Route::post('/print', [QueueController::class, 'printTicket']); // Print queue ticket
  });
  
});
