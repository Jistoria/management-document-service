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

    /**
     * @OA\Get(
     *     path="/storage-unit-types",
     *     operationId="getStorageUnitTypes",
     *     tags={"StorageUnitTypes"},
     *     summary="Listar tipos de unidad",
     *     description="Retorna el listado de tipos de unidad de almacenamiento",
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", description="Filtrar por código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="level", in="query", description="Filtrar por nivel", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="created_by", in="query", description="Filtrar por creador", @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", description="Elementos por página", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="format", in="query", description="Formato de respuesta", @OA\Schema(type="string", enum={"collection","paginate","minimal","dropdown","pluck"})),
     *     @OA\Parameter(name="include", in="query", description="Relaciones a incluir", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sortBy", in="query", description="Campo para ordenar", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sortDir", in="query", description="Dirección de orden", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Response(response=200, description="Tipos de unidad obtenidos exitosamente")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/storage-unit-types",
     *     operationId="storeStorageUnitType",
     *     tags={"StorageUnitTypes"},
     *     summary="Crear tipo de unidad",
     *     description="Crea un nuevo tipo de unidad de almacenamiento",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","code","level"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="level", type="integer"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tipo de unidad creado exitosamente")
     * )
     */
    public function store(StoreStorageUnitTypeRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $type = $this->storageUnitTypeService->create($request->validated());
            return new StorageUnitTypeResource($type);
        }, 'Tipo de unidad creado exitosamente', 201);
    }

    /**
     * @OA\Get(
     *     path="/storage-unit-types/{id}",
     *     operationId="showStorageUnitType",
     *     tags={"StorageUnitTypes"},
     *     summary="Mostrar tipo de unidad",
     *     description="Obtiene los detalles de un tipo de unidad de almacenamiento",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Tipo de unidad obtenido exitosamente")
     * )
     */
    public function show(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $type = $this->storageUnitTypeService->findById($id);
            return new StorageUnitTypeResource($type);
        }, 'Tipo de unidad obtenido exitosamente');
    }

    /**
     * @OA\Put(
     *     path="/storage-unit-types/{id}",
     *     operationId="updateStorageUnitType",
     *     tags={"StorageUnitTypes"},
     *     summary="Actualizar tipo de unidad",
     *     description="Actualiza un tipo de unidad de almacenamiento",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="code", type="string"),
     *             @OA\Property(property="level", type="integer"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tipo de unidad actualizado exitosamente")
     * )
     */
    public function update(UpdateStorageUnitTypeRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $type = $this->storageUnitTypeService->update($id, $request->validated());
            return new StorageUnitTypeResource($type);
        }, 'Tipo de unidad actualizado exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/storage-unit-types/{id}",
     *     operationId="deleteStorageUnitType",
     *     tags={"StorageUnitTypes"},
     *     summary="Eliminar tipo de unidad",
     *     description="Elimina un tipo de unidad de almacenamiento",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Tipo de unidad eliminado exitosamente")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->storageUnitTypeService->delete($id);
            return ['id' => $id];
        }, 'Tipo de unidad eliminado exitosamente');
    }
}
