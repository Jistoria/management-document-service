<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatus;
use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\Process\FiltersProcessCategoryRequest;
use App\Http\Requests\Process\StoreProcessCategoryRequest;
use App\Http\Requests\Process\UpdateProcessCategoryRequest;
use App\Http\Resources\ProcessCategoryResource;
use App\Http\Resources\ProcessResource;
use App\Services\ProcessCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class ProcessCategoryController extends Controller
{

    protected $processCategoryService;

    public function __construct(ProcessCategoryService $processCategoryService)
    {
        $this->processCategoryService = $processCategoryService;
    }


    /**
     * @OA\Get(
     *     path="/process-categories",
     *     summary="Listar categorías de procesos",
     *     description="Obtiene una lista paginada de categorías de procesos con filtros opcionales",
     *     operationId="getProcessCategories",
     *     tags={"Process Categories"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página para la paginación",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Término de búsqueda para filtrar por nombre o código",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Filtra por código específico de la categoría",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="createdBy",
     *         in="query",
     *         description="Filtra por el usuario que creó la categoría",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="subsystem_id",
     *         in="query",
     *         description="Filtra por UUID del subsistema asociado",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Campo por el cual ordenar los resultados",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "code", "created_at", "updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Dirección del ordenamiento",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías de procesos obtenida exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/ProcessCategory")
     *                     ),
     *                     @OA\Property(property="meta", ref="#/components/schemas/Pagination")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los filtros proporcionados",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(FiltersProcessCategoryRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {

            return ApiIndexBuilder::build(
                $this->processCategoryService,
                ProcessCategoryResource::class,
                $request,
                $request->validated(),
            );
        });
    }

    /**
     * @OA\Get(
     *     path="/process-categories/{categoryId}",
     *     summary="Obtener una categoría de proceso específica",
     *     description="Obtiene los detalles de una categoría de proceso por su ID",
     *     operationId="getProcessCategory",
     *     tags={"Process Categories"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         description="UUID de la categoría de proceso a obtener",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Relaciones adicionales a incluir (processes, subsystem)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría de proceso obtenida exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", ref="#/components/schemas/ProcessCategory")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría de proceso no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($categoryId) : JsonResponse
    {
        return catchSync(function () use ($categoryId) {
            $category = $this->processCategoryService->findById($categoryId);
            return new ProcessCategoryResource($category);
        });
    }

    /**
     * @OA\Post(
     *     path="/process-categories",
     *     summary="Crear una nueva categoría de proceso",
     *     description="Crea una nueva categoría de proceso con los datos proporcionados",
     *     operationId="createProcessCategory",
     *     tags={"Process Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la categoría de proceso a crear",
     *         @OA\JsonContent(ref="#/components/schemas/StoreProcessCategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoría de proceso creada exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", ref="#/components/schemas/ProcessCategory")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación en los datos enviados",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflicto - Ya existe una categoría con el mismo código o nombre",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(StoreProcessCategoryRequest $request) : JsonResponse
    {
        return catchSync(function () use ($request) {

            $validated = $request->validated();

            $category = $this->processCategoryService->create($validated);

            return new ProcessCategoryResource($category);
        },
        status: HttpStatus::CREATED);
    }

    /**
     * @OA\Put(
     *     path="/process-categories/{categoryId}",
     *     summary="Actualizar una categoría de proceso",
     *     description="Actualiza los datos de una categoría de proceso existente",
     *     operationId="updateProcessCategory",
     *     tags={"Process Categories"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         description="UUID de la categoría de proceso a actualizar",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos actualizados de la categoría de proceso",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProcessCategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Categoría de proceso actualizada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría de proceso no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación en los datos enviados",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflicto - Ya existe otra categoría con el mismo código o nombre",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(UpdateProcessCategoryRequest $request, $category): JsonResponse
    {
        return catchSync(function () use ($request, $category) {

            $validated = $request->validated();

            $category = $this->processCategoryService->update($validated, $category);

            return new ProcessCategoryResource($category);
        },
        status: HttpStatus::NO_CONTENT);
    }

    /**
     * @OA\Delete(
     *     path="/process-categories/{categoryId}",
     *     summary="Eliminar una categoría de proceso",
     *     description="Elimina (soft delete) una categoría de proceso específica",
     *     operationId="deleteProcessCategory",
     *     tags={"Process Categories"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         description="UUID de la categoría de proceso a eliminar",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Categoría de proceso eliminada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría de proceso no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflicto - La categoría tiene procesos asociados y no puede eliminarse",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($category): JsonResponse
    {
        return catchSync(function () use ($category) {
            $this->processCategoryService->delete($category);

            return ['deleted' => true, 'id' => $category];
        },
        status: HttpStatus::NO_CONTENT);
    }

    /**
     * @OA\Get(
     *     path="/process-categories/{categoryId}/processes",
     *     summary="Obtener procesos de una categoría",
     *     description="Obtiene todos los procesos asociados a una categoría específica",
     *     operationId="getProcessCategoryProcesses",
     *     tags={"Process Categories"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         description="UUID de la categoría de proceso",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="include_children",
     *         in="query",
     *         description="Incluir procesos hijos (subprocesos)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Procesos de la categoría obtenidos exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/Process")
     *                     ),
     *                     @OA\Property(property="total", type="integer")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría de proceso no encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     )
     * )
     */
    public function processes($categoryId, Request $request): JsonResponse
    {
        return catchSync(function () use ($categoryId, $request) {
            $includeChildren = $request->boolean('include_children', false);
            $processes = $this->processCategoryService->getProcesses($categoryId, $includeChildren);

            return [
                'data' => ProcessResource::collection($processes),
                'total' => $processes->count()
            ];
        });
    }
}