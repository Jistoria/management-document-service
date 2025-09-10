<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\StorageUnit\FiltersStorageUnitRequest;
use App\Http\Requests\StorageUnit\StoreStorageUnitRequest;
use App\Http\Requests\StorageUnit\UpdateStorageUnitRequest;
use App\Http\Resources\StorageUnitResource;
use App\Services\StorageUnitService;
use Illuminate\Http\JsonResponse;
use function App\Helpers\catchSync;

class StorageUnitController extends Controller
{
    public function __construct(private readonly StorageUnitService $storageUnitService)
    {
    }

    public function index(FiltersStorageUnitRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            return ApiIndexBuilder::build(
                $this->storageUnitService,
                StorageUnitResource::class,
                $request,
                ApiIndexBuilder::extractFilters($request, ['storageUnitTypeId', 'parentId', 'code'])
            );
        }, 'Unidades de almacenamiento obtenidas exitosamente');
    }

    public function store(StoreStorageUnitRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $unit = $this->storageUnitService->create($request->validated());
            return new StorageUnitResource($unit);
        }, 'Unidad de almacenamiento creada exitosamente', 201);
    }

    public function show(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $unit = $this->storageUnitService->findById($id);
            return new StorageUnitResource($unit);
        }, 'Unidad de almacenamiento obtenida exitosamente');
    }

    public function update(UpdateStorageUnitRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $unit = $this->storageUnitService->update($id, $request->validated());
            return new StorageUnitResource($unit);
        }, 'Unidad de almacenamiento actualizada exitosamente');
    }

    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->storageUnitService->delete($id);
            return ['id' => $id];
        }, 'Unidad de almacenamiento eliminada exitosamente');
    }
}
