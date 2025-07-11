<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeadOffice\StoreHeadOfficeRequest;
use App\Http\Requests\HeadOffice\UpdateHeadOfficeRequest;
use App\Http\Requests\HeadOffice\FiltersHeadOfficeRequest;
use App\Http\Resources\HeadOfficeResource;
use App\Services\HeadOfficeService;
use App\Helpers\ApiIndexBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * Controller for Head Office operations
 *
 * Handles HTTP requests for head office CRUD operations
 * using the HeadOfficeService and catchSync helper with proper resources.
 */
class HeadOfficeController extends Controller
{
    protected HeadOfficeService $headOfficeService;

    public function __construct(HeadOfficeService $headOfficeService)
    {
        $this->headOfficeService = $headOfficeService;
    }

    /**
     * @OA\Get(
     *     path="/head-offices",
     *     operationId="getHeadOffices",
     *     tags={"HeadOffices"},
     *     summary="Obtener listado de sedes",
     *     description="Retorna el listado de sedes con soporte para múltiples formatos: paginación, colección, minimal, dropdown, pluck",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre o código",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Formato de respuesta (collection, paginate, minimal, dropdown, pluck)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"collection", "paginate", "minimal", "dropdown", "pluck"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sedes obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/HeadOffice")
     *                         ),
     *                         @OA\Property(property="count", type="integer", example=1)
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/HeadOffice")
     *                         ),
     *                         @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(FiltersHeadOfficeRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            // Usar filtros validados
            $filters = $request->getValidatedFilters();

            return ApiIndexBuilder::build(
                $this->headOfficeService,
                \App\Http\Resources\HeadOfficeResource::class,
                $request,
                $filters
            );
        }, 'Sedes obtenidas exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/head-offices",
     *     operationId="createHeadOffice",
     *     tags={"HeadOffices"},
     *     summary="Crear nueva sede",
     *     description="Crea una nueva sede con los datos proporcionados",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la sede a crear",
     *         @OA\JsonContent(
     *             required={"name","code"},
     *             @OA\Property(property="name", type="string", description="Nombre de la sede"),
     *             @OA\Property(property="code", type="string", description="Código único de la sede (alfanumérico, mayúsculas)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sede creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOffice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El nombre es requerido")
     *                 ),
     *                 @OA\Property(
     *                     property="code",
     *                     type="array",
     *                     @OA\Items(type="string", example="El código es requerido")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(StoreHeadOfficeRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $data = $request->validated();
            $data['created_by'] = 'system';

            // Create head office
            $headOffice = $this->headOfficeService->create($data);

            return new HeadOfficeResource($headOffice);
        }, 'Sede creada exitosamente', 201);
    }

    /**
     * @OA\Get(
     *     path="/head-offices/{id}",
     *     operationId="getHeadOffice",
     *     tags={"HeadOffices"},
     *     summary="Obtener sede por ID",
     *     description="Retorna una sede específica con vista detallada y metadata",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Relaciones a incluir (departments, statistics, hierarchy)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOfficeDetailed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sede no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $headOffice = $this->headOfficeService->findById($id);

            if (!$headOffice) {
                throw new \InvalidArgumentException('Sede no encontrada');
            }

            $includes = explode(',', $request->get('include', ''));
            $this->headOfficeService->resolveIncludes($includes, $headOffice);

            return (new HeadOfficeResource($headOffice))->detailed();
        }, 'Sede obtenida exitosamente');
    }



    /**
     * @OA\Put(
     *     path="/head-offices/{id}",
     *     operationId="updateHeadOffice",
     *     tags={"HeadOffices"},
     *     summary="Actualizar sede",
     *     description="Actualiza una sede existente con los datos proporcionados",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos a actualizar de la sede",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nombre de la sede"),
     *             @OA\Property(property="code", type="string", description="Código único de la sede")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOffice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     *
     * @OA\Patch(
     *     path="/head-offices/{id}",
     *     operationId="patchHeadOffice",
     *     tags={"HeadOffices"},
     *     summary="Actualizar sede (PATCH)",
     *     description="Actualiza parcialmente una sede existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos a actualizar de la sede",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nombre de la sede"),
     *             @OA\Property(property="code", type="string", description="Código único de la sede")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOffice")
     *         )
     *     )
     * )
     */
    public function update(UpdateHeadOfficeRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $data = $request->validated();
            $data['updated_by'] = 'system';

            // Update head office
            $headOffice = $this->headOfficeService->update($id, $data);

            return new HeadOfficeResource($headOffice);
        }, 'Sede actualizada exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/head-offices/{id}",
     *     operationId="deleteHeadOffice",
     *     tags={"HeadOffices"},
     *     summary="Eliminar sede",
     *     description="Elimina una sede (soft delete). La sede se marca como eliminada pero no se borra físicamente.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede eliminada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="deleted", type="boolean", example=true),
     *                 @OA\Property(property="id", type="string", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="No se puede eliminar (tiene departamentos asociados)",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->headOfficeService->delete($id);

            return ['deleted' => true, 'id' => $id];
        }, 'Sede eliminada exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/head-offices/{id}/restore",
     *     operationId="restoreHeadOffice",
     *     tags={"HeadOffices"},
     *     summary="Restaurar sede",
     *     description="Restaura una sede previamente eliminada (soft delete)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede eliminada",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede restaurada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede restaurada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOffice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="La sede no está eliminada",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function restore(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $headOffice = $this->headOfficeService->restore($id);

            return new HeadOfficeResource($headOffice);
        }, 'Sede restaurada exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/head-offices/{id}/hierarchy",
     *     operationId="getHeadOfficeHierarchy",
     *     tags={"HeadOffices"},
     *     summary="Obtener jerarquía de sede",
     *     description="Retorna la jerarquía completa de una sede con sus departamentos y carreras",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jerarquía obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Jerarquía obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOfficeHierarchy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function hierarchy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $headOffice = $this->headOfficeService->getFullHierarchy($id);

            if (!$headOffice) {
                throw new \InvalidArgumentException('Sede no encontrada');
            }

            return (new HeadOfficeResource($headOffice))->withHierarchy();
        }, 'Jerarquía obtenida exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/head-offices/{id}/statistics",
     *     operationId="getHeadOfficeStatistics",
     *     tags={"HeadOffices"},
     *     summary="Obtener estadísticas de sede",
     *     description="Retorna estadísticas detalladas de una sede (departamentos, carreras, etc.)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estadísticas obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="departments_count", type="integer", example=0),
     *                 @OA\Property(property="careers_count", type="integer", example=0),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="last_updated", type="string", format="date-time"),
     *                 @OA\Property(property="version", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function statistics(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            return $this->headOfficeService->getStatistics($id);
        }, 'Estadísticas obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/head-offices/code/{code}",
     *     operationId="findHeadOfficeByCode",
     *     tags={"HeadOffices"},
     *     summary="Buscar sede por código",
     *     description="Busca y retorna una sede por su código único",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Código único de la sede",
     *         required=true,
     *         @OA\Schema(type="string", example="CENTRAL")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sede encontrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sede encontrada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/HeadOffice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sede no encontrada con el código especificado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sede no encontrada con el código especificado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function findByCode(string $code): JsonResponse
    {
        return catchSync(function () use ($code) {
            $headOffice = $this->headOfficeService->findByCode($code);

            if (!$headOffice) {
                throw new \InvalidArgumentException('Sede no encontrada con el código especificado');
            }

            return new HeadOfficeResource($headOffice);
        }, 'Sede encontrada exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/head-offices/bulk-delete",
     *     operationId="bulkDeleteHeadOffices",
     *     tags={"HeadOffices"},
     *     summary="Eliminación masiva de sedes",
     *     description="Elimina múltiples sedes en una sola operación (soft delete)",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Array de IDs de sedes a eliminar",
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid"),
     *                 example={"0197d795-7572-7331-903b-3aeed9fb34c2", "0197d795-7572-7331-903b-3aeed9fb34c3"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Eliminación masiva completada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Eliminación masiva completada"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="deleted_count", type="integer", example=2, description="Número de sedes eliminadas exitosamente"),
     *                 @OA\Property(property="total_requested", type="integer", example=3, description="Número total de sedes solicitadas para eliminar")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Se requiere un array de IDs"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="ids",
     *                     type="array",
     *                     @OA\Items(type="string", example="Se requiere un array de IDs")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $ids = $request->input('ids', []);

            if (empty($ids) || !is_array($ids)) {
                throw new \InvalidArgumentException('Se requiere un array de IDs');
            }

            $deletedCount = $this->headOfficeService->bulkDelete($ids);

            return [
                'deleted_count' => $deletedCount,
                'total_requested' => count($ids)
            ];
        }, 'Eliminación masiva completada');
    }
}
