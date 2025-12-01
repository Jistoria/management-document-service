<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HeadOfficeController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ProcessCategoryController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\SubsystemController;
use App\Http\Controllers\SubsystemEntityLinkController;
use App\Http\Controllers\SubsystemGroupController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\RequiredDocumentController;
use App\Http\Controllers\MetadataFieldController;
use App\Http\Controllers\MetadataSchemaController;
use App\Http\Controllers\StorageUnitController;
use App\Http\Controllers\StorageUnitTypeController;
use Illuminate\Support\Facades\Route;

// Head Offices Routes
Route::prefix('head-offices')->group(function () {
    // Standard CRUD routes
    Route::get('/', [HeadOfficeController::class, 'index'])
        ->middleware(['auth.service', 'permission:head_office.read']);
    Route::post('/', [HeadOfficeController::class, 'store'])
        ->middleware(['auth.service', 'permission:head_office.create']);
    Route::get('/{head_office}', [HeadOfficeController::class, 'show'])
        ->middleware(['auth.service', 'permission:head_office.read']);
    Route::put('/{head_office}', [HeadOfficeController::class, 'update'])
        ->middleware(['auth.service', 'permission:head_office.update']);
    Route::patch('/{head_office}', [HeadOfficeController::class, 'update'])
        ->middleware(['auth.service', 'permission:head_office.update']);
    Route::delete('/{head_office}', [HeadOfficeController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:head_office.delete']);

    // Additional routes
    Route::post('/bulk-delete', [HeadOfficeController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:head_office.delete']);
});

// Departments Routes
Route::prefix('departments')->group(function () {
    // Standard CRUD routes
    Route::get('/', [DepartmentController::class, 'index'])
        ->middleware(['auth.service', 'permission:department.read']);
    Route::post('/', [DepartmentController::class, 'store'])
        ->middleware(['auth.service', 'permission:department.create']);
    Route::get('/{department}', [DepartmentController::class, 'show'])
        ->middleware(['auth.service', 'permission:department.read']);
    Route::put('/{department}', [DepartmentController::class, 'update'])
        ->middleware(['auth.service', 'permission:department.update']);
    Route::patch('/{department}', [DepartmentController::class, 'update'])
        ->middleware(['auth.service', 'permission:department.update']);
    Route::delete('/{department}', [DepartmentController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:department.delete']);

    // Additional routes
    Route::post('/bulk-delete', [DepartmentController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:department.delete']);
});

// Careers Routes
Route::prefix('careers')->group(function () {
    // Standard CRUD routes
    Route::get('/', [CareerController::class, 'index'])
        ->middleware(['auth.service', 'permission:career.read']);
    Route::post('/', [CareerController::class, 'store'])
        ->middleware(['auth.service', 'permission:career.create']);
    Route::get('/{career}', [CareerController::class, 'show'])
        ->middleware(['auth.service', 'permission:career.read']);
    Route::put('/{career}', [CareerController::class, 'update'])
        ->middleware(['auth.service', 'permission:career.update']);
    Route::patch('/{career}', [CareerController::class, 'update'])
        ->middleware(['auth.service', 'permission:career.update']);
    Route::delete('/{career}', [CareerController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:career.delete']);

    // Additional routes
    Route::post('/bulk-delete', [CareerController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:career.delete']);
});

// Subsystems Routes
Route::prefix('subsystems')->group(function () {
    // Standard CRUD routes
    Route::get('/', [SubsystemController::class, 'index'])
        ->middleware(['auth.service', 'permission:subsystem.read']);
    Route::post('/', [SubsystemController::class, 'store'])
        ->middleware(['auth.service', 'permission:subsystem.create']);
    Route::get('/{subsystem}', [SubsystemController::class, 'show'])
        ->middleware(['auth.service', 'permission:subsystem.read']);
    Route::put('/{subsystem}', [SubsystemController::class, 'update'])
        ->middleware(['auth.service', 'permission:subsystem.update']);
    Route::patch('/{subsystem}', [SubsystemController::class, 'update'])
        ->middleware(['auth.service', 'permission:subsystem.update']);
    Route::delete('/{subsystem}', [SubsystemController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:subsystem.delete']);

    // Additional routes
    Route::post('/bulk-delete', [SubsystemController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:subsystem.delete']);
});

// Subsystem Groups Routes
Route::prefix('subsystem-groups')->group(function () {
    Route::get('/', [SubsystemGroupController::class, 'index'])
        ->middleware(['auth.service', 'permission:subsystem_group.read']);
    Route::post('/', [SubsystemGroupController::class, 'store'])
        ->middleware(['auth.service', 'permission:subsystem_group.create']);
    Route::get('/{subsystemGroup}', [SubsystemGroupController::class, 'show'])
        ->middleware(['auth.service', 'permission:subsystem_group.read']);
    Route::put('/{subsystemGroup}', [SubsystemGroupController::class, 'update'])
        ->middleware(['auth.service', 'permission:subsystem_group.update']);
    Route::patch('/{subsystemGroup}', [SubsystemGroupController::class, 'update'])
        ->middleware(['auth.service', 'permission:subsystem_group.update']);
    Route::delete('/{subsystemGroup}', [SubsystemGroupController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:subsystem_group.delete']);

    // Grouped Subsystems (sync)
    Route::put('/{subsystemGroup}/subsystems', [SubsystemGroupController::class, 'syncSubsystems'])
        ->middleware(['auth.service', 'permission:subsystem_group.update']);
});

// Subsystem Entity Links Routes
Route::prefix('subsystem-entity-links')->group(function () {
    Route::get('/', [SubsystemEntityLinkController::class, 'index'])
        ->middleware(['auth.service', 'permission:subsystem_entity_link.read']);   // consulta
    Route::post('/', [SubsystemEntityLinkController::class, 'store'])
        ->middleware(['auth.service', 'permission:subsystem_entity_link.create']); // attach
    Route::put('/{subsystemId}', [SubsystemEntityLinkController::class, 'update'])
        ->middleware(['auth.service', 'permission:subsystem_entity_link.update']); // update_link
    Route::delete('/', [SubsystemEntityLinkController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:subsystem_entity_link.delete']); // detach
});

// Process Categories Routes
Route::prefix('process-categories')->group(function () {
    Route::get('/', [ProcessCategoryController::class, 'index'])
        ->middleware(['auth.service', 'permission:process_category.read']);
    Route::get('/dropdown', [ProcessCategoryController::class, 'dropdown'])
        ->middleware(['auth.service', 'permission:process_category.read']);
    Route::get('/{category}', [ProcessCategoryController::class, 'show'])
        ->middleware(['auth.service', 'permission:process_category.read']);
    Route::get('/{category}/processes', [ProcessCategoryController::class, 'processes'])
        ->middleware(['auth.service', 'permission:process_category.read']);
    Route::post('/', [ProcessCategoryController::class, 'store'])
        ->middleware(['auth.service', 'permission:process_category.create']);
    Route::put('/{category}', [ProcessCategoryController::class, 'update'])
        ->middleware(['auth.service', 'permission:process_category.update']);
    Route::delete('/{category}', [ProcessCategoryController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:process_category.delete']);
});

// Process Routes
Route::prefix('processes')->group(function () {
    Route::get('/', [ProcessController::class, 'index'])
        ->middleware(['auth.service', 'permission:process.read']);
    Route::post('/', [ProcessController::class, 'store'])
        ->middleware(['auth.service', 'permission:process.create']);
    Route::get('/{process}', [ProcessController::class, 'show'])
        ->middleware(['auth.service', 'permission:process.read']);
    Route::put('/{process}', [ProcessController::class, 'update'])
        ->middleware(['auth.service', 'permission:process.update']);
    Route::patch('/{process}', [ProcessController::class, 'update'])
        ->middleware(['auth.service', 'permission:process.update']);
    Route::delete('/{process}', [ProcessController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:process.delete']);

    // Extras
    Route::post('/{process}/restore', [ProcessController::class, 'restore'])
        ->middleware(['auth.service', 'permission:process.update']);   // restore -> update
    Route::post('/bulk-delete', [ProcessController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:process.delete']);   // bulk-delete -> delete
});


/*
 * The following routes will be for documentary entities.
 */

// Document Types Routes
Route::prefix('document-types')->group(function () {
    Route::get('/', [DocumentTypeController::class, 'index'])
        ->middleware(['auth.service', 'permission:document_type.read']);
    Route::post('/', [DocumentTypeController::class, 'store'])
        ->middleware(['auth.service', 'permission:document_type.create']);
    Route::get('/{document_type}', [DocumentTypeController::class, 'show'])
        ->middleware(['auth.service', 'permission:document_type.read']);
    Route::put('/{document_type}', [DocumentTypeController::class, 'update'])
        ->middleware(['auth.service', 'permission:document_type.update']);
    Route::patch('/{document_type}', [DocumentTypeController::class, 'update'])
        ->middleware(['auth.service', 'permission:document_type.update']);
    Route::delete('/{document_type}', [DocumentTypeController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:document_type.delete']);

    // Extra
    Route::post('/bulk-delete', [DocumentTypeController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:document_type.delete']); // bulk-delete -> delete
});

// Required Documents Routes
Route::prefix('required-documents')->group(function () {
    Route::get('/', [RequiredDocumentController::class, 'index'])
        ->middleware(['auth.service', 'permission:required_document.read']);
    Route::post('/', [RequiredDocumentController::class, 'store'])
        ->middleware(['auth.service', 'permission:required_document.create']);
    Route::get('/{required_document}', [RequiredDocumentController::class, 'show'])
        ->middleware(['auth.service', 'permission:required_document.read']);
    Route::put('/{required_document}', [RequiredDocumentController::class, 'update'])
        ->middleware(['auth.service', 'permission:required_document.update']);
    Route::patch('/{required_document}', [RequiredDocumentController::class, 'update'])
        ->middleware(['auth.service', 'permission:required_document.update']);
    Route::delete('/{required_document}', [RequiredDocumentController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:required_document.delete']);

    // Extra
    Route::post('/bulk-delete', [RequiredDocumentController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:required_document.delete']); // bulk-delete -> delete
});

// Metadata Schemas Routes
Route::prefix('metadata-schemas')->group(function () {
    Route::get('/', [MetadataSchemaController::class, 'index'])
        ->middleware(['auth.service', 'permission:metadata_schema.read']);
    Route::post('/', [MetadataSchemaController::class, 'store'])
        ->middleware(['auth.service', 'permission:metadata_schema.create']);
    Route::get('/{metadata_schema}', [MetadataSchemaController::class, 'show'])
        ->middleware(['auth.service', 'permission:metadata_schema.read']);
    Route::put('/{metadata_schema}', [MetadataSchemaController::class, 'update'])
        ->middleware(['auth.service', 'permission:metadata_schema.update']);
    Route::patch('/{metadata_schema}', [MetadataSchemaController::class, 'update'])
        ->middleware(['auth.service', 'permission:metadata_schema.update']);
    Route::delete('/{metadata_schema}', [MetadataSchemaController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:metadata_schema.delete']);

    // Extra
    Route::post('/bulk-delete', [MetadataSchemaController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:metadata_schema.delete']); // bulk-delete -> delete
});

// Metadata Fields Routes
Route::prefix('metadata-fields')->group(function () {
    // Catalogs must be before {metadata_field} to avoid route conflicts
    Route::get('/catalogs/entity-types', [MetadataFieldController::class, 'getEntityTypes'])
        ->middleware(['auth.service', 'permission:metadata_field.read']);
    Route::get('/catalogs/type-inputs', [MetadataFieldController::class, 'getTypeInputs'])
        ->middleware(['auth.service', 'permission:metadata_field.read']);
    
    Route::get('/', [MetadataFieldController::class, 'index'])
        ->middleware(['auth.service', 'permission:metadata_field.read']);
    Route::post('/', [MetadataFieldController::class, 'store'])
        ->middleware(['auth.service', 'permission:metadata_field.create']);
    Route::get('/{metadata_field}', [MetadataFieldController::class, 'show'])
        ->middleware(['auth.service', 'permission:metadata_field.read']);
    Route::put('/{metadata_field}', [MetadataFieldController::class, 'update'])
        ->middleware(['auth.service', 'permission:metadata_field.update']);
    Route::patch('/{metadata_field}', [MetadataFieldController::class, 'update'])
        ->middleware(['auth.service', 'permission:metadata_field.update']);
    Route::delete('/{metadata_field}', [MetadataFieldController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:metadata_field.delete']);

    // Extra
    Route::post('/bulk-delete', [MetadataFieldController::class, 'bulkDelete'])
        ->middleware(['auth.service', 'permission:metadata_field.delete']); // bulk-delete -> delete
});

// Storage Unit Types Routes
Route::prefix('storage-unit-types')->group(function () {
    Route::get('/', [StorageUnitTypeController::class, 'index'])
        ->middleware(['auth.service', 'permission:storage_unit_type.read']);
    Route::post('/', [StorageUnitTypeController::class, 'store'])
        ->middleware(['auth.service', 'permission:storage_unit_type.create']);
    Route::get('/{storage_unit_type}', [StorageUnitTypeController::class, 'show'])
        ->middleware(['auth.service', 'permission:storage_unit_type.read']);
    Route::put('/{storage_unit_type}', [StorageUnitTypeController::class, 'update'])
        ->middleware(['auth.service', 'permission:storage_unit_type.update']);
    Route::patch('/{storage_unit_type}', [StorageUnitTypeController::class, 'update'])
        ->middleware(['auth.service', 'permission:storage_unit_type.update']);
    Route::delete('/{storage_unit_type}', [StorageUnitTypeController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:storage_unit_type.delete']);
});

// Storage Units Routes
Route::prefix('storage-units')->group(function () {
    Route::get('/', [StorageUnitController::class, 'index'])
        ->middleware(['auth.service', 'permission:storage_unit.read']);
    Route::post('/', [StorageUnitController::class, 'store'])
        ->middleware(['auth.service', 'permission:storage_unit.create']);
    Route::get('/{storage_unit}', [StorageUnitController::class, 'show'])
        ->middleware(['auth.service', 'permission:storage_unit.read']);
    Route::put('/{storage_unit}', [StorageUnitController::class, 'update'])
        ->middleware(['auth.service', 'permission:storage_unit.update']);
    Route::patch('/{storage_unit}', [StorageUnitController::class, 'update'])
        ->middleware(['auth.service', 'permission:storage_unit.update']);
    Route::delete('/{storage_unit}', [StorageUnitController::class, 'destroy'])
        ->middleware(['auth.service', 'permission:storage_unit.delete']);
});


Route::post('me/entities', App\Http\Controllers\EntityLookupController::class)
    ->middleware(['auth.service']);


// Nested route for careers by department
Route::get('/departments/{departmentId}/careers', [CareerController::class, 'getByDepartment']);

// Route::middleware(['verify.jwt'])->group(function () {
//     Route::get('/secure/data', fn() => response()->json(['ok' => true]));
// });


Route::fallback(function () {
    return response()->json(['message' => 'API connection successful.']);
});
