<?php

use App\Http\Controllers\{AuthController, DepartmentController, UserController, QueueController, PatientController, PatientQueueController, DashboardController};
use App\Http\Controllers\Api\PrinterSettingController; // Import the new controller

Route::post('login', [AuthController::class, 'login']);
Route::get('/phpinfo', fn () => phpinfo());

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
    Route::get('/stats', [DashboardController::class, 'getDepartmentStats']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::post('/{id}', [DepartmentController::class, 'store']);
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
    Route::post('/{departmentId}/specializations', [DepartmentController::class, 'updateSpecializations']);
  });

  Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'get']);
    Route::post('/', [UserController::class, 'saveUser']);

    Route::get('/{id}', [UserController::class, 'getUserById']);
    Route::post('/{id}', [UserController::class, 'saveUser']);
    Route::get('/get/staff', [UserController::class, 'getStaff']);
    Route::get('/get/card-total', [UserController::class, 'cardTotals']);
  });

  Route::prefix('patients')->group(function () {
    Route::get('/', [PatientController::class, 'get']);
    Route::post('/', [PatientController::class, 'savePatient']);
    Route::get('/queue', [PatientQueueController::class, 'index']);
    Route::get('/queue/history', [PatientQueueController::class, 'history']);
    Route::post('/{id}', [PatientController::class, 'savePatient']);
    Route::get('/card-totals', [PatientController::class, 'cardTotals']);
  });

  // Dashboard Routes
  Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStats']);
    Route::get('/recent-activity', [DashboardController::class, 'getRecentActivity']);
  });




  // Queue Management Routes
  Route::post('/queue/call-out/{patient}', [PatientQueueController::class, 'callOutQueue']);
  Route::post('/queue/start/{patient}', [PatientQueueController::class, 'startSession']);
  Route::post('/queue/end/{patient}', [PatientQueueController::class, 'endSession']);
  Route::post('/queue/next/{patient}', [PatientQueueController::class, 'nextStep']);

  Route::post('/messages', [App\Http\Controllers\ChatController::class, 'sendMessage']);

  // Printer Settings Routes
  Route::prefix('settings')->group(function () {
      Route::get('/default-printer', [PrinterSettingController::class, 'getDefaultPrinter']);
      Route::post('/default-printer', [PrinterSettingController::class, 'setDefaultPrinter']);
      Route::get('/video-url', [\App\Http\Controllers\VideoController::class, 'index']);
      Route::post('/video-file', [\App\Http\Controllers\VideoController::class, 'store']);
  });
});

