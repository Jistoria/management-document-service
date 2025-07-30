<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Requests\Department\FiltersDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use App\Helpers\ApiIndexBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * Controller for Department operations
 *
 * Handles HTTP requests for department CRUD operations
 * using the DepartmentService and catchSync helper with proper resources.
 */
class DepartmentController extends Controller
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * @OA\Get(
     *     path="/departments",
     *     operationId="getDepartments",
     *     tags={"Departments"},
     *     summary="Obtener listado de departamentos",
     *     description="Retorna el listado de departamentos con soporte para múltiples formatos: paginación, colección, minimal, dropdown, pluck",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre o código",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="headOfficeId",
     *         in="query",
     *         description="Filtrar por sede específica",
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
     *     @OA\Parameter(
     *        name="has_subsystems",
     *        in="query",
     *        description="Filtra por si tiene subsistemas (1 o 0)",
     *        required=false,
     *        @OA\Schema(type="integer", enum={0,1})
     *      ),
     *      @OA\Parameter(
     *          name="exclude_subsystem_id",
     *          in="query",
     *          description="Filtra por ID de subsistema excluido",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *         name="subsystem_id",
     *         in="query",
     *         description="Filtra por ID de subsistema",
     *         required=false,
     *         @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamentos obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Department")
     *                         ),
     *                         @OA\Property(property="count", type="integer", example=1)
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Department")
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
    public function index(FiltersDepartmentRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            // Usar filtros validados
            $filters = $request->getValidatedFilters();

            return ApiIndexBuilder::build(
                $this->departmentService,
                \App\Http\Resources\DepartmentResource::class,
                $request,
                $filters
            );
        }, 'Departamentos obtenidos exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/departments",
     *     operationId="createDepartment",
     *     tags={"Departments"},
     *     summary="Crear nuevo departamento",
     *     description="Crea un nuevo departamento con los datos proporcionados",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del departamento a crear",
     *         @OA\JsonContent(
     *             required={"name","headOfficeId"},
     *             @OA\Property(property="name", type="string", description="Nombre del departamento"),
     *             @OA\Property(property="code", type="string", description="Código único del departamento (alfanumérico, mayúsculas)"),
     *             @OA\Property(property="headOfficeId", type="string", format="uuid", description="ID de la sede a la que pertenece")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Departamento creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamento creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Department")
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
     *                     property="headOfficeId",
     *                     type="array",
     *                     @OA\Items(type="string", example="La sede es requerida")
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
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $department = $this->departmentService->create($request->validated());
            return new DepartmentResource($department);
        }, 'Departamento creado exitosamente', 201);
    }

    /**
     * @OA\Get(
     *     path="/departments/{id}",
     *     operationId="getDepartment",
     *     tags={"Departments"},
     *     summary="Obtener departamento por ID",
     *     description="Retorna un departamento específico con vista detallada y metadata",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Relaciones a incluir (headOffice, careers, statistics, hierarchy)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamento obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamento obtenido exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Department")
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
    public function show(Request $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $department = $this->departmentService->findById($id);

            if (!$department) {
                throw new \InvalidArgumentException('Departamento no encontrado');
            }

            $includes = explode(',', $request->get('include', ''));
            $this->departmentService->resolveIncludes($includes, $department);

            return (new DepartmentResource($department))->detailed();
        }, 'Departamento obtenido exitosamente');
    }

    /**
     * @OA\Put(
     *     path="/departments/{id}",
     *     operationId="updateDepartment",
     *     tags={"Departments"},
     *     summary="Actualizar departamento",
     *     description="Actualiza un departamento existente con los datos proporcionados",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del departamento a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nombre del departamento"),
     *             @OA\Property(property="code", type="string", description="Código único del departamento"),
     *             @OA\Property(property="headOfficeId", type="string", format="uuid", description="ID de la sede")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamento actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamento actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Department")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Departamento no encontrado",
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
    public function update(UpdateDepartmentRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $department = $this->departmentService->update($id, $request->validated());
            return new DepartmentResource($department);
        }, 'Departamento actualizado exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/departments/{id}",
     *     operationId="deleteDepartment",
     *     tags={"Departments"},
     *     summary="Eliminar departamento",
     *     description="Elimina (soft delete) un departamento específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamento eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamento eliminado exitosamente"),
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No se puede eliminar el departamento porque tiene carreras activas",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $result = $this->departmentService->delete($id);
            return $result;
        }, 'Departamento eliminado exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/departments/{id}/restore",
     *     operationId="restoreDepartment",
     *     tags={"Departments"},
     *     summary="Restaurar departamento eliminado",
     *     description="Restaura un departamento previamente eliminado (soft delete)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamento restaurado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamento restaurado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Department")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="El departamento no está eliminado",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
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
    public function restore(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $department = $this->departmentService->restore($id);
            return new DepartmentResource($department);
        }, 'Departamento restaurado exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/departments/{id}/hierarchy",
     *     operationId="getDepartmentHierarchy",
     *     tags={"Departments"},
     *     summary="Obtener jerarquía completa del departamento",
     *     description="Retorna la jerarquía completa del departamento incluyendo sede, carreras y subsistemas",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jerarquía obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Jerarquía del departamento obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Department")
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
    public function hierarchy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $department = $this->departmentService->getFullHierarchy($id);

            if (!$department) {
                throw new \InvalidArgumentException('Departamento no encontrado');
            }

            return (new DepartmentResource($department))->withHierarchy();
        }, 'Jerarquía del departamento obtenida exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/departments/{id}/statistics",
     *     operationId="getDepartmentStatistics",
     *     tags={"Departments"},
     *     summary="Obtener estadísticas del departamento",
     *     description="Retorna estadísticas detalladas del departamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estadísticas del departamento obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="careers_count", type="integer", example=5),
     *                 @OA\Property(property="head_office", type="object"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="last_updated", type="string", format="date-time"),
     *                 @OA\Property(property="version", type="integer", example=1)
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
    public function statistics(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $statistics = $this->departmentService->getStatistics($id);
            return $statistics;
        }, 'Estadísticas del departamento obtenidas exitosamente');
    }

    /**
     * @OA\Get(
     *     path="/departments/code/{code}",
     *     operationId="getDepartmentByCode",
     *     tags={"Departments"},
     *     summary="Obtener departamento por código",
     *     description="Busca y retorna un departamento específico por su código único",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Código único del departamento",
     *         required=true,
     *         @OA\Schema(type="string", example="INFO")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Departamento encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Departamento encontrado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Department")
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
    public function findByCode(string $code): JsonResponse
    {
        return catchSync(function () use ($code) {
            $department = $this->departmentService->findByCode($code);

            if (!$department) {
                throw new \InvalidArgumentException('Departamento no encontrado');
            }

            return new DepartmentResource($department);
        }, 'Departamento encontrado exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/departments/bulk-delete",
     *     operationId="bulkDeleteDepartments",
     *     tags={"Departments"},
     *     summary="Eliminación masiva de departamentos",
     *     description="Elimina múltiples departamentos en una sola operación",
     *     @OA\RequestBody(
     *         required=true,
     *         description="IDs de los departamentos a eliminar",
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
            'ids.*' => 'uuid|exists:departments,id'
        ]);

        return catchSync(function () use ($request) {
            $ids = $request->input('ids');
            $deletedCount = $this->departmentService->bulkDelete($ids);
            $totalRequested = count($ids);
            $successRate = round(($deletedCount / $totalRequested) * 100, 2);

            return [
                'deleted_count' => $deletedCount,
                'total_requested' => $totalRequested,
                'success_rate' => $successRate . '%'
            ];
        }, 'Eliminación masiva completada');
    }
}
