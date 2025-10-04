<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatus;
use App\Helpers\ApiIndexBuilder;
use App\Http\Resources\ProcessResource;
use App\Services\ProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class ProcessController extends Controller
{
    protected ProcessService $processService;
    public function __construct(ProcessService $processService){
        $this->processService = $processService;
    }

    /**
     * @OA\Get(
     *     path="/processes",
     *     operationId="getProcesses",
     *     tags={"Processes"},
     *     summary="Listar procesos",
     *     description="Obtiene un listado de procesos con soporte para paginación, formatos alternativos y filtros por categoría, código y creador.",
     *     @OA\Parameter(name="search", in="query", description="Buscar por nombre o código", @OA\Schema(type="string")),
     *     @OA\Parameter(name="processCategoryId", in="query", description="Filtrar por ID de categoría de proceso", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="parentId", in="query", description="Filtrar por ID de proceso padre", @OA\Schema(type="string", format="uuid", nullable=true)),
     *     @OA\Parameter(name="code", in="query", description="Filtrar por código exacto", @OA\Schema(type="string")),
     *     @OA\Parameter(name="createdBy", in="query", description="Filtrar por usuario creador", @OA\Schema(type="string")),
     *     @OA\Parameter(name="format", in="query", description="Formato de respuesta (collection, paginate, minimal, dropdown, pluck)", @OA\Schema(type="string", enum={"collection","paginate","minimal","dropdown","pluck"})),
     *     @OA\Parameter(name="perPage", in="query", description="Elementos por página cuando se usa paginación", @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="page", in="query", description="Número de página cuando se usa paginación", @OA\Schema(type="integer", minimum=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Procesos obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Procesos obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Process")),
     *                         @OA\Property(property="count", type="integer", example=10)
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Process")),
     *                         @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            return ApiIndexBuilder::build(
                $this->processService,
                ProcessResource::class,
                $request,
                ApiIndexBuilder::extractFilters($request, ['processCategoryId', 'parentId'])
            );
        }, 'Procesos obtenidos exitosamente');
    }

    /**
     * @OA\Post(
     *     path="/processes",
     *     operationId="storeProcess",
     *     tags={"Processes"},
     *     summary="Crear proceso",
     *     description="Crea un nuevo proceso dentro de una categoría especificada.",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProcessCreateRequest")),
     *     @OA\Response(
     *         response=201,
     *         description="Proceso creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proceso creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProcessDetailed")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Error de negocio o datos duplicados", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function store(Request $request) : JsonResponse
    {
        return catchSync(function () use ($request) {
            $process = $this->processService->create($request->all());

            return new ProcessResource($process);
        }, 'Proceso creado exitosamente', HttpStatus::CREATED);
    }

    /**
     * @OA\Get(
     *     path="/processes/{id}",
     *     operationId="showProcess",
     *     tags={"Processes"},
     *     summary="Obtener proceso",
     *     description="Retorna la información detallada de un proceso por su identificador.",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proceso", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="include", in="query", description="Relaciones a incluir (processCategory,parent,children,requiredDocuments)", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Proceso obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proceso obtenido exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProcessDetailed")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Proceso no encontrado", @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function show(Request $request, $id) : JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $process = $this->processService->findById($id);
            $includes = ApiIndexBuilder::extractIncludes($request);
            $this->processService->resolveIncludes($includes, $process);

            return (new ProcessResource($process))->withContext([
                'include_relations' => $includes,
            ]);
        }, 'Proceso obtenido exitosamente');
    }

    /**
     * @OA\Put(
     *     path="/processes/{id}",
     *     operationId="updateProcess",
     *     tags={"Processes"},
     *     summary="Actualizar proceso",
     *     description="Actualiza los datos de un proceso existente.",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proceso", @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProcessUpdateRequest")),
     *     @OA\Response(
     *         response=200,
     *         description="Proceso actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proceso actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProcessDetailed")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Error de negocio o datos duplicados", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=404, description="Proceso no encontrado", @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function update(Request $request, $id) : JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $process = $this->processService->update($request->all(), $id);

            return new ProcessResource($process);
        }, 'Proceso actualizado exitosamente');
    }

    /**
     * @OA\Delete(
     *     path="/processes/{id}",
     *     operationId="deleteProcess",
     *     tags={"Processes"},
     *     summary="Eliminar proceso",
     *     description="Elimina un proceso existente.",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del proceso", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Proceso eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Proceso eliminado exitosamente"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="string", format="uuid"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Proceso no encontrado", @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function destroy($id) : JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->processService->delete($id);
            return ['id' => $id];
        }, 'Proceso eliminado exitosamente');
    }
}

