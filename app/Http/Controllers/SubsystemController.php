<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subsystem\StoreSubsystemRequest;
use App\Http\Requests\Subsystem\UpdateSubsystemRequest;
use App\Http\Requests\Subsystem\FiltersSubsystemRequest;
use App\Http\Resources\SubsystemResource;
use App\Services\SubsystemService;
use App\Helpers\ApiIndexBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * @OA\Tag(name="Subsystems", description="Operaciones CRUD y extras para subsistemas")
 */
class SubsystemController extends Controller
{
    protected SubsystemService $subsystemService;

    public function __construct(SubsystemService $subsystemService)
    {
        $this->subsystemService = $subsystemService;
    }

    /**
     * @OA\Get(
     *     path="/subsystems",
     *     operationId="getSubsystems",
     *     tags={"Subsystems"},
     *     summary="Obtener listado de subsistemas",
     *     description="Retorna el listado de subsistemas con soporte para múltiples formatos: paginación, colección, minimal, dropdown, pluck",
     *     @OA\Parameter(name="search", in="query", description="Búsqueda por nombre o código", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", description="Elementos por página", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="format", in="query", description="Formato de respuesta (collection, paginate, minimal, dropdown, pluck)", required=false, @OA\Schema(type="string", enum={"collection", "paginate", "minimal", "dropdown", "pluck"})),
     *     @OA\Response(response=200, description="Listado obtenido exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function index(FiltersSubsystemRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $filters = $request->getValidatedFilters();
            return ApiIndexBuilder::build($this->subsystemService, SubsystemResource::class, $request, $filters);
        }, 'Subsistemas obtenidos exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/subsystems",
     *     operationId="createSubsystem",
     *     tags={"Subsystems"},
     *     summary="Crear nuevo subsistema",
     *     description="Crea un nuevo subsistema con los datos proporcionados",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=201, description="Subsistema creado exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function store(StoreSubsystemRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $subsystem = $this->subsystemService->create($request->validated());
            return new SubsystemResource($subsystem);
        }, 'Subsistema creado exitosamente', 201);
    }

    /**
     * @OA\Get(
     *     path="/subsystems/{id}",
     *     operationId="getSubsystem",
     *     tags={"Subsystems"},
     *     summary="Obtener subsistema por ID",
     *     description="Retorna un subsistema específico con vista detallada y metadata",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Subsistema obtenido exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $subsystem = $this->subsystemService->findById($id);
            return new SubsystemResource($subsystem);
        }, 'Subsistema obtenido exitosamente');
    }

    /**
     * @OA\Put(
     *     path="/subsystems/{id}",
     *     operationId="updateSubsystem",
     *     tags={"Subsystems"},
     *     summary="Actualizar subsistema",
     *     description="Actualiza un subsistema existente con los datos proporcionados",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=200, description="Subsistema actualizado exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     * @OA\Patch(
     *     path="/subsystems/{id}",
     *     operationId="patchSubsystem",
     *     tags={"Subsystems"},
     *     summary="Actualizar subsistema (PATCH)",
     *     description="Actualiza parcialmente un subsistema existente",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=200, description="Subsistema actualizado exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem"))
     * )
     */
    public function update(UpdateSubsystemRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $subsystem = $this->subsystemService->update($id, $request->validated());
            return new SubsystemResource($subsystem);
        }, 'Subsistema actualizado exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/subsystems/{id}",
     *     operationId="deleteSubsystem",
     *     tags={"Subsystems"},
     *     summary="Eliminar subsistema",
     *     description="Elimina un subsistema (soft delete)",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Subsistema eliminado exitosamente", @OA\JsonContent(@OA\Property(property="deleted", type="boolean", example=true), @OA\Property(property="id", type="string"))),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $result = $this->subsystemService->delete($id);
            return ['deleted' => $result, 'id' => $id];
        }, 'Subsistema eliminado exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/subsystems/{id}/restore",
     *     operationId="restoreSubsystem",
     *     tags={"Subsystems"},
     *     summary="Restaurar subsistema eliminado",
     *     description="Restaura un subsistema previamente eliminado (soft delete)",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Subsistema restaurado exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=400, description="El subsistema no está eliminado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function restore(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $subsystem = $this->subsystemService->restore($id);
            return new SubsystemResource($subsystem);
        }, 'Subsistema restaurado exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/subsystems/{id}/hierarchy",
     *     operationId="hierarchySubsystem",
     *     tags={"Subsystems"},
     *     summary="Obtener jerarquía completa del subsistema",
     *     description="Retorna la jerarquía completa del subsistema incluyendo categorías y procesos",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Jerarquía obtenida exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function hierarchy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $subsystem = $this->subsystemService->getFullHierarchy($id);
            return new SubsystemResource($subsystem);
        }, 'Jerarquía obtenida exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/subsystems/{id}/statistics",
     *     operationId="statisticsSubsystem",
     *     tags={"Subsystems"},
     *     summary="Obtener estadísticas del subsistema",
     *     description="Retorna estadísticas relevantes del subsistema",
     *     @OA\Parameter(name="id", in="path", description="ID único del subsistema", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Estadísticas obtenidas exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function statistics(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $stats = $this->subsystemService->getStatistics($id);
            return $stats;
        }, 'Estadísticas obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/subsystems/code/{code}",
     *     operationId="findSubsystemByCode",
     *     tags={"Subsystems"},
     *     summary="Buscar subsistema por código",
     *     description="Retorna un subsistema por su código único",
     *     @OA\Parameter(name="code", in="path", description="Código único del subsistema", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Subsistema obtenido exitosamente", @OA\JsonContent(ref="#/components/schemas/Subsystem")),
     *     @OA\Response(response=404, description="Subsistema no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function findByCode(string $code): JsonResponse
    {
        return catchSync(function () use ($code) {
            $subsystem = $this->subsystemService->findByCode($code);
            return new SubsystemResource($subsystem);
        }, 'Subsistema obtenido exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/subsystems/bulk-delete",
     *     operationId="bulkDeleteSubsystems",
     *     tags={"Subsystems"},
     *     summary="Eliminar múltiples subsistemas",
     *     description="Elimina múltiples subsistemas por sus IDs",
     *     @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="ids", type="array", @OA\Items(type="string", format="uuid"))), description="IDs de subsistemas a eliminar"),
     *     @OA\Response(response=200, description="Subsistemas eliminados exitosamente", @OA\JsonContent(@OA\Property(property="deletedCount", type="integer", example=2))),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $ids = $request->input('ids', []);
            $deletedCount = $this->subsystemService->bulkDelete($ids);
            return ['deletedCount' => $deletedCount];
        }, 'Subsistemas eliminados exitosamente');
    }
}
