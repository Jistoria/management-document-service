<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HeadOfficeController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ProcessCategoryController;
use App\Http\Controllers\SubsystemController;
use App\Http\Controllers\SubsystemEntityLinkController;
use App\Http\Controllers\SubsystemGroupController;
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

// Careers Routes
Route::prefix('careers')->group(function () {
    // Standard CRUD routes
    Route::get('/', [CareerController::class, 'index']);
    Route::post('/', [CareerController::class, 'store']);
    Route::get('/{career}', [CareerController::class, 'show']);
    Route::put('/{career}', [CareerController::class, 'update']);
    Route::patch('/{career}', [CareerController::class, 'update']);
    Route::delete('/{career}', [CareerController::class, 'destroy']);

    // Additional routes
    Route::post('/{career}/restore', [CareerController::class, 'restore']);
    Route::get('/{career}/hierarchy', [CareerController::class, 'hierarchy']);
    Route::get('/{career}/statistics', [CareerController::class, 'statistics']);
    Route::get('/code/{code}', [CareerController::class, 'findByCode']);
    Route::post('/bulk-delete', [CareerController::class, 'bulkDelete']);
});

// Subsystems Routes
Route::prefix('subsystems')->group(function () {
    // Standard CRUD routes
    Route::get('/', [SubsystemController::class, 'index']);
    Route::post('/', [SubsystemController::class, 'store']);
    Route::get('/{subsystem}', [SubsystemController::class, 'show']);
    Route::put('/{subsystem}', [SubsystemController::class, 'update']);
    Route::patch('/{subsystem}', [SubsystemController::class, 'update']);
    Route::delete('/{subsystem}', [SubsystemController::class, 'destroy']);

    // Additional routes
    Route::post('/{subsystem}/restore', [SubsystemController::class, 'restore']);
    Route::get('/{subsystem}/hierarchy', [SubsystemController::class, 'hierarchy']);
    Route::get('/{subsystem}/statistics', [SubsystemController::class, 'statistics']);
    Route::get('/code/{code}', [SubsystemController::class, 'findByCode']);
    Route::post('/bulk-delete', [SubsystemController::class, 'bulkDelete']);
});

// Subsystem Groups Routes
Route::prefix('subsystem-groups')->group(function () {
    Route::get('/', [SubsystemGroupController::class, 'index']);
    Route::post('/', [SubsystemGroupController::class, 'store']);
    Route::get('/{subsystemGroup}', [SubsystemGroupController::class, 'show']);
    Route::put('/{subsystemGroup}', [SubsystemGroupController::class, 'update']);
    Route::patch('/{subsystemGroup}', [SubsystemGroupController::class, 'update']);
    Route::delete('/{subsystemGroup}', [SubsystemGroupController::class, 'destroy']);

    //Grouped Subsystems
    Route::put('/{subsystemGroup}/subsystems', [SubsystemGroupController::class, 'syncSubsystems']);
});

// Subsystem Entity Links Routes
Route::prefix('subsystem-entity-links')->group(function () {
    Route::get('/', [SubsystemEntityLinkController::class, 'index']);       // GET with query params
    Route::post('/', [SubsystemEntityLinkController::class, 'store']);      // POST attachment
    Route::put('/{subsystemId}', [SubsystemEntityLinkController::class, 'update']);
    Route::delete('/', [SubsystemEntityLinkController::class, 'destroy']);  // DELETE detach
});

// ProcessCategories Routes
Route::prefix('process-categories')->group(function () {
    Route::get('/', [ProcessCategoryController::class, 'index']);
    Route::get('/dropdown', [ProcessCategoryController::class, 'dropdown']);
    Route::get('/{category}', [ProcessCategoryController::class, 'show']);
    Route::get('/{category}/processes', [ProcessCategoryController::class, 'processes']);
    Route::post('/', [ProcessCategoryController::class, 'store']);
    Route::put('/{category}', [ProcessCategoryController::class, 'update']);
    Route::delete('/{category}', [ProcessCategoryController::class, 'destroy']);
});



// Nested route for careers by department
Route::get('/departments/{departmentId}/careers', [CareerController::class, 'getByDepartment']);

Route::fallback(function () {
    return response()->json(['message' => 'API connection successful.'], 200);
});
