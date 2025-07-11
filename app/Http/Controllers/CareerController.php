<?php

namespace App\Http\Controllers;

use App\Http\Requests\Career\StoreCareerRequest;
use App\Http\Requests\Career\UpdateCareerRequest;
use App\Http\Requests\Career\FiltersCareerRequest;
use App\Http\Resources\CareerResource;
use App\Services\CareerService;
use App\Helpers\ApiIndexBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * Controller for Career operations
 *
 * Handles HTTP requests for career CRUD operations
 * using the CareerService and catchSync helper with proper resources.
 */
class CareerController extends Controller
{
    protected CareerService $careerService;

    public function __construct(CareerService $careerService)
    {
        $this->careerService = $careerService;
    }

    /**
     * @OA\Get(
     *     path="/careers",
     *     operationId="getCareers",
     *     tags={"Careers"},
     *     summary="Obtener listado de carreras",
     *     description="Retorna el listado de carreras con soporte para múltiples formatos: paginación, colección, minimal, dropdown, pluck",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre o código",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="departmentId",
     *         in="query",
     *         description="Filtrar por departamento específico",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
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
     *             @OA\Property(property="message", type="string", example="Carreras obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Career")
     *                         ),
     *                         @OA\Property(property="count", type="integer", example=1)
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Career")
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
    public function index(FiltersCareerRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            // Usar filtros validados
            $filters = $request->getValidatedFilters();

            return ApiIndexBuilder::build(
                $this->careerService,
                \App\Http\Resources\CareerResource::class,
                $request,
                $filters
            );
        }, 'Carreras obtenidas exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/careers",
     *     operationId="createCareer",
     *     tags={"Careers"},
     *     summary="Crear nueva carrera",
     *     description="Crea una nueva carrera con los datos proporcionados",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la carrera a crear",
     *         @OA\JsonContent(
     *             required={"name","departmentId"},
     *             @OA\Property(property="name", type="string", description="Nombre de la carrera"),
     *             @OA\Property(property="code", type="string", description="Código único de la carrera (alfanumérico, mayúsculas)"),
     *             @OA\Property(property="departmentId", type="string", format="uuid", description="ID del departamento al que pertenece")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Carrera creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carrera creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Career")
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
     *                     property="departmentId",
     *                     type="array",
     *                     @OA\Items(type="string", example="El departamento es requerido")
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
    public function store(StoreCareerRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $career = $this->careerService->create($request->validated());
            return new CareerResource($career);
        }, 'Carrera creada exitosamente', 201);
    }

    /**
     * @OA\Get(
     *     path="/careers/{id}",
     *     operationId="getCareer",
     *     tags={"Careers"},
     *     summary="Obtener carrera por ID",
     *     description="Retorna una carrera específica con vista detallada y metadata",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Relaciones a incluir (department, headOffice, subsystems, hierarchy)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carrera obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carrera obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Career")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
            $career = $this->careerService->findById($id);

            if (!$career) {
                throw new \InvalidArgumentException('Carrera no encontrada');
            }

            $includes = explode(',', $request->get('include', ''));
            $this->careerService->resolveIncludes($includes, $career);

            return (new CareerResource($career))->detailed();
        }, 'Carrera obtenida exitosamente');
    }

    /**
     * @OA\Put(
     *     path="/careers/{id}",
     *     operationId="updateCareer",
     *     tags={"Careers"},
     *     summary="Actualizar carrera",
     *     description="Actualiza una carrera existente con los datos proporcionados",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la carrera a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nombre de la carrera"),
     *             @OA\Property(property="code", type="string", description="Código único de la carrera"),
     *             @OA\Property(property="departmentId", type="string", format="uuid", description="ID del departamento")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carrera actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carrera actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Career")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(UpdateCareerRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $career = $this->careerService->update($id, $request->validated());
            return new CareerResource($career);
        }, 'Carrera actualizada exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/careers/{id}",
     *     operationId="deleteCareer",
     *     tags={"Careers"},
     *     summary="Eliminar carrera",
     *     description="Elimina (soft delete) una carrera específica",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carrera eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carrera eliminada exitosamente"),
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No se puede eliminar la carrera porque tiene subsistemas activos",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
            $result = $this->careerService->delete($id);
            return $result;
        }, 'Carrera eliminada exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/careers/{id}/restore",
     *     operationId="restoreCareer",
     *     tags={"Careers"},
     *     summary="Restaurar carrera eliminada",
     *     description="Restaura una carrera previamente eliminada (soft delete)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carrera restaurada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carrera restaurada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Career")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="La carrera no está eliminada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
            $career = $this->careerService->restore($id);
            return new CareerResource($career);
        }, 'Carrera restaurada exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/careers/{id}/hierarchy",
     *     operationId="getCareerHierarchy",
     *     tags={"Careers"},
     *     summary="Obtener jerarquía completa de la carrera",
     *     description="Retorna la jerarquía completa de la carrera incluyendo departamento, sede y subsistemas",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jerarquía obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Jerarquía de la carrera obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Career")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
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
            $career = $this->careerService->getFullHierarchy($id);

            if (!$career) {
                throw new \InvalidArgumentException('Carrera no encontrada');
            }

            return (new CareerResource($career))->withHierarchy();
        }, 'Jerarquía de la carrera obtenida exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/careers/{id}/statistics",
     *     operationId="getCareerStatistics",
     *     tags={"Careers"},
     *     summary="Obtener estadísticas de la carrera",
     *     description="Retorna estadísticas detalladas de la carrera",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estadísticas de la carrera obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="subsystems_count", type="integer", example=3),
     *                 @OA\Property(property="active_subsystems_count", type="integer", example=2),
     *                 @OA\Property(property="department", type="object"),
     *                 @OA\Property(property="head_office", type="object"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="last_updated", type="string", format="date-time"),
     *                 @OA\Property(property="version", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
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
            $statistics = $this->careerService->getStatistics($id);
            return $statistics;
        }, 'Estadísticas de la carrera obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/careers/code/{code}",
     *     operationId="getCareerByCode",
     *     tags={"Careers"},
     *     summary="Obtener carrera por código",
     *     description="Busca y retorna una carrera específica por su código único",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Código único de la carrera",
     *         required=true,
     *         @OA\Schema(type="string", example="ING_SISTEMAS")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carrera encontrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carrera encontrada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Career")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Carrera no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
            $career = $this->careerService->findByCode($code);

            if (!$career) {
                throw new \InvalidArgumentException('Carrera no encontrada');
            }

            return new CareerResource($career);
        }, 'Carrera encontrada exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/careers/bulk-delete",
     *     operationId="bulkDeleteCareers",
     *     tags={"Careers"},
     *     summary="Eliminación masiva de carreras",
     *     description="Elimina múltiples carreras en una sola operación",
     *     @OA\RequestBody(
     *         required=true,
     *         description="IDs de las carreras a eliminar",
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
     *                 @OA\Property(property="deleted_count", type="integer", example=2),
     *                 @OA\Property(property="total_requested", type="integer", example=3),
     *                 @OA\Property(property="success_rate", type="string", example="66.67%")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'uuid|exists:careers,id'
        ]);

        return catchSync(function () use ($request) {
            $ids = $request->input('ids');
            $deletedCount = $this->careerService->bulkDelete($ids);
            $totalRequested = count($ids);
            $successRate = round(($deletedCount / $totalRequested) * 100, 2);

            return [
                'deleted_count' => $deletedCount,
                'total_requested' => $totalRequested,
                'success_rate' => $successRate . '%'
            ];
        }, 'Eliminación masiva completada');
    }

    /**
     * @OA\Get(
     *     path="/departments/{departmentId}/careers",
     *     operationId="getCareersByDepartment",
     *     tags={"Careers"},
     *     summary="Obtener carreras por departamento",
     *     description="Retorna todas las carreras de un departamento específico",
     *     @OA\Parameter(
     *         name="departmentId",
     *         in="path",
     *         description="ID del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Formato de respuesta",
     *         required=false,
     *         @OA\Schema(type="string", enum={"paginate", "minimal", "dropdown", "pluck", "collection"}, example="collection")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carreras obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Carreras del departamento obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Career")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Departamento no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function getByDepartment(FiltersCareerRequest $request, string $departmentId): JsonResponse
    {
        return catchSync(function () use ($request, $departmentId) {
            // Extract validated filters
            $filters = $request->getValidatedFilters();

            // Add department filter
            $filters['department_id'] = $departmentId;

            return ApiIndexBuilder::build(
                $this->careerService,
                \App\Http\Resources\CareerResource::class,
                $request,
                $filters
            );
        }, 'Carreras del departamento obtenidas exitosamente');
    }
}
