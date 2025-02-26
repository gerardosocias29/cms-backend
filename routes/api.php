<?php

use App\Http\Controllers\{AuthController, DepartmentController, UserController};

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('profile', [AuthController::class, 'userProfile']);

  Route::prefix('queue')->group(function () {
    Route::get('/', [QueueController::class, 'index']);
    Route::post('/add', [QueueController::class, 'store']);
    Route::post('/next', [QueueController::class, 'callNext']);
    Route::post('/print', [QueueController::class, 'printTicket']);
  });

  Route::prefix('departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'get']);
   
  });

  Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'get']);
    Route::post('/', [UserController::class, 'saveUser']);
    Route::post('/{id}', [UserController::class, 'saveUser']);
    Route::get('/card-total', [UserController::class, 'cardTotals']); 
   
  });
  
});
