<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HeadOfficeController;
use Illuminate\Support\Facades\Route;

// Head Offices Routes
Route::prefix('head-offices')->group(function () {
    // Standard CRUD routes
    Route::get('/', [HeadOfficeController::class, 'index']);
    Route::post('/', [HeadOfficeController::class, 'store']);
    Route::get('/{head_office}', [HeadOfficeController::class, 'show']);
    Route::put('/{head_office}', [HeadOfficeController::class, 'update']);
    Route::patch('/{head_office}', [HeadOfficeController::class, 'update']);
    Route::delete('/{head_office}', [HeadOfficeController::class, 'destroy']);

    // Additional routes
    Route::post('/{head_office}/restore', [HeadOfficeController::class, 'restore']);
    Route::get('/{head_office}/hierarchy', [HeadOfficeController::class, 'hierarchy']);
    Route::get('/{head_office}/statistics', [HeadOfficeController::class, 'statistics']);
    Route::get('/code/{code}', [HeadOfficeController::class, 'findByCode']);
    Route::post('/bulk-delete', [HeadOfficeController::class, 'bulkDelete']);
});

// Departments Routes
Route::prefix('departments')->group(function () {
    // Standard CRUD routes
    Route::get('/', [DepartmentController::class, 'index']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::get('/{department}', [DepartmentController::class, 'show']);
    Route::put('/{department}', [DepartmentController::class, 'update']);
    Route::patch('/{department}', [DepartmentController::class, 'update']);
    Route::delete('/{department}', [DepartmentController::class, 'destroy']);

    // Additional routes
    Route::post('/{department}/restore', [DepartmentController::class, 'restore']);
    Route::get('/{department}/hierarchy', [DepartmentController::class, 'hierarchy']);
    Route::get('/{department}/statistics', [DepartmentController::class, 'statistics']);
    Route::get('/code/{code}', [DepartmentController::class, 'findByCode']);
    Route::post('/bulk-delete', [DepartmentController::class, 'bulkDelete']);
});

Route::fallback(function () {
    return response()->json(['message' => 'API connection successful.'], 200);
});
