<?php

use App\Http\Controllers\FacultyController;
use Illuminate\Support\Facades\Route;

Route::prefix('faculties')->group(function () {
    Route::get('/', [FacultyController::class, 'index'])
        ->name('faculties.index');
    Route::get('/{id}', [FacultyController::class, 'show'])
        ->name('faculties.show');
    Route::post('/', [FacultyController::class, 'store'])
        ->name('faculties.store');
    Route::put('/{id}', [FacultyController::class, 'update'])
        ->name('faculties.update');
    Route::delete('/{id}', [FacultyController::class, 'destroy'])
        ->name('faculties.destroy');
});

Route::prefix('careers')->group(function () {
    Route::get('/', [FacultyController::class, 'index'])
        ->name('careers.index');
    Route::get('/{id}', [FacultyController::class, 'show'])
        ->name('careers.show');
    Route::post('/', [FacultyController::class, 'store'])
        ->name('careers.store');
    Route::put('/{id}', [FacultyController::class, 'update'])
        ->name('careers.update');
    Route::delete('/{id}', [FacultyController::class, 'destroy'])
        ->name('careers.destroy');
});



Route::prefix('subsystems')->group(function () {
    Route::get('/', [FacultyController::class, 'index'])
        ->name('subsystems.index');
    Route::get('/{id}', [FacultyController::class, 'show'])
        ->name('subsystems.show');
    Route::post('/', [FacultyController::class, 'store'])
        ->name('subsystems.store');
    Route::put('/{id}', [FacultyController::class, 'update'])
        ->name('subsystems.update');
    Route::delete('/{id}', [FacultyController::class, 'destroy'])
        ->name('subsystems.destroy');
});
