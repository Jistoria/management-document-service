<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\StorageUnitType\FiltersStorageUnitTypeRequest;
use App\Http\Requests\StorageUnitType\StoreStorageUnitTypeRequest;
use App\Http\Requests\StorageUnitType\UpdateStorageUnitTypeRequest;
use App\Http\Resources\StorageUnitTypeResource;
use App\Services\StorageUnitTypeService;
use Illuminate\Http\JsonResponse;
use function App\Helpers\catchSync;

class StorageUnitTypeController extends Controller
{
    public function __construct(private readonly StorageUnitTypeService $storageUnitTypeService)
    {
    }

    public function index(FiltersStorageUnitTypeRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            return ApiIndexBuilder::build(
                $this->storageUnitTypeService,
                StorageUnitTypeResource::class,
                $request,
                ApiIndexBuilder::extractFilters($request, ['code', 'level', 'created_by'])
            );
        }, 'Tipos de unidad obtenidos exitosamente');
    }

    public function store(StoreStorageUnitTypeRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $type = $this->storageUnitTypeService->create($request->validated());
            return new StorageUnitTypeResource($type);
        }, 'Tipo de unidad creado exitosamente', 201);
    }

    public function show(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $type = $this->storageUnitTypeService->findById($id);
            return new StorageUnitTypeResource($type);
        }, 'Tipo de unidad obtenido exitosamente');
    }

    public function update(UpdateStorageUnitTypeRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $type = $this->storageUnitTypeService->update($id, $request->validated());
            return new StorageUnitTypeResource($type);
        }, 'Tipo de unidad actualizado exitosamente');
    }

    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->storageUnitTypeService->delete($id);
            return ['id' => $id];
        }, 'Tipo de unidad eliminado exitosamente');
    }
}
