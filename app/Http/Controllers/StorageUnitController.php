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

    /**
     * @OA\Get(
     *     path="/storage-units",
     *     operationId="getStorageUnits",
     *     tags={"StorageUnits"},
     *     summary="Listar unidades de almacenamiento",
     *     description="Retorna el listado de unidades de almacenamiento",
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por etiqueta o código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="storageUnitTypeId", in="query", description="Filtrar por tipo de unidad", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="parentId", in="query", description="Filtrar por unidad padre", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="code", in="query", description="Filtrar por código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", description="Elementos por página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="format", in="query", description="Formato de respuesta", @OA\Schema(type="string", enum={"collection","paginate","minimal","dropdown","pluck"})),
     *     @OA\Parameter(name="include", in="query", description="Relaciones a incluir", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sortBy", in="query", description="Campo para ordenar", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sortDir", in="query", description="Dirección de orden", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Response(response=200, description="Unidades de almacenamiento obtenidas exitosamente")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/storage-units",
     *     operationId="storeStorageUnit",
     *     tags={"StorageUnits"},
     *     summary="Crear unidad de almacenamiento",
     *     description="Crea una nueva unidad de almacenamiento",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"storageUnitTypeId","label"},
     *             @OA\Property(property="storageUnitTypeId", type="string", format="uuid"),
     *             @OA\Property(property="parentId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="label", type="string"),
     *             @OA\Property(property="code", type="string", nullable=true),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Unidad de almacenamiento creada exitosamente")
     * )
     */
    public function store(StoreStorageUnitRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $unit = $this->storageUnitService->create($request->validated());
            return new StorageUnitResource($unit);
        }, 'Unidad de almacenamiento creada exitosamente', 201);
    }

    /**
     * @OA\Get(
     *     path="/storage-units/{id}",
     *     operationId="showStorageUnit",
     *     tags={"StorageUnits"},
     *     summary="Mostrar unidad de almacenamiento",
     *     description="Obtiene los detalles de una unidad de almacenamiento",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Unidad de almacenamiento obtenida exitosamente")
     * )
     */
    public function show(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $unit = $this->storageUnitService->findById($id);
            return new StorageUnitResource($unit);
        }, 'Unidad de almacenamiento obtenida exitosamente');
    }

    /**
     * @OA\Put(
     *     path="/storage-units/{id}",
     *     operationId="updateStorageUnit",
     *     tags={"StorageUnits"},
     *     summary="Actualizar unidad de almacenamiento",
     *     description="Actualiza los datos de una unidad de almacenamiento",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="storageUnitTypeId", type="string", format="uuid"),
     *             @OA\Property(property="parentId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="label", type="string"),
     *             @OA\Property(property="code", type="string", nullable=true),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Unidad de almacenamiento actualizada exitosamente")
     * )
     */
    public function update(UpdateStorageUnitRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $unit = $this->storageUnitService->update($id, $request->validated());
            return new StorageUnitResource($unit);
        }, 'Unidad de almacenamiento actualizada exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/storage-units/{id}",
     *     operationId="deleteStorageUnit",
     *     tags={"StorageUnits"},
     *     summary="Eliminar unidad de almacenamiento",
     *     description="Elimina una unidad de almacenamiento",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Unidad de almacenamiento eliminada exitosamente")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->storageUnitService->delete($id);
            return ['id' => $id];
        }, 'Unidad de almacenamiento eliminada exitosamente');
    }
}
