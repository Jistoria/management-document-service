<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\RequiredDocument\FiltersRequiredDocumentRequest;
use App\Http\Requests\RequiredDocument\StoreRequiredDocumentRequest;
use App\Http\Requests\RequiredDocument\UpdateRequiredDocumentRequest;
use App\Http\Resources\RequiredDocumentResource;
use App\Services\RequiredDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * Controller for Required Document operations
 */
class RequiredDocumentController extends Controller
{
    protected RequiredDocumentService $requiredDocumentService;

    public function __construct(RequiredDocumentService $requiredDocumentService)
    {
        $this->requiredDocumentService = $requiredDocumentService;
    }

    /**
     * @OA\Get(
     *     path="/required-documents",
     *     operationId="getRequiredDocuments",
     *     tags={"RequiredDocuments"},
     *     summary="Listar documentos requeridos",
     *     description="Obtiene el listado de documentos requeridos con soporte para filtros, paginación y diferentes formatos de respuesta",
     *     @OA\Parameter(name="search", in="query", description="Buscar por código u otros campos", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="processId", in="query", description="Filtrar por ID de proceso", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="documentTypeId", in="query", description="Filtrar por ID de tipo de documento", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="academicRoleId", in="query", description="Filtrar por ID de rol académico", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="metadataSchemaId", in="query", description="Filtrar por ID de esquema de metadatos", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="mandatory", in="query", description="Filtrar por obligatoriedad", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="isPublic", in="query", description="Filtrar por visibilidad pública", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="externalUserId", in="query", description="Filtrar por usuario externo asociado", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="externalOrganizationId", in="query", description="Filtrar por organización externa asociada", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="include", in="query", description="Relaciones a incluir (documentType,process,metadataSchema)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", description="Elementos por página", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="format", in="query", description="Formato de respuesta (collection, paginate, minimal, dropdown, pluck)", required=false, @OA\Schema(type="string", enum={"collection","paginate","minimal","dropdown","pluck"})),
     *     @OA\Response(
     *         response=200,
     *         description="Documentos requeridos obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RequiredDocument")),
     *                         @OA\Property(property="count", type="integer", example=5)
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RequiredDocument")),
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
    public function index(FiltersRequiredDocumentRequest $request): JsonResponse
    {
        return catchSync(
            function () use ($request) {
                return ApiIndexBuilder::build(
                    $this->requiredDocumentService,
                    RequiredDocumentResource::class,
                    $request,
                    ApiIndexBuilder::extractFilters($request, [
                        'process_id',
                        'document_type_id',
                        'academic_role_id',
                        'metadata_schema_id',
                        'mandatory',
                        'is_public',
                        'external_user_id',
                        'external_organization_id'
                    ])
                );
            },
            'Required documents retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/required-documents",
     *     operationId="storeRequiredDocument",
     *     tags={"RequiredDocuments"},
     *     summary="Crear documento requerido",
     *     description="Crea un nuevo documento requerido",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RequiredDocumentCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Documento requerido creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required document created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/RequiredDocumentDetailed")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function store(StoreRequiredDocumentRequest $request): JsonResponse
    {
        return catchSync(
            function () use ($request) {
                $requiredDocument = $this->requiredDocumentService->create($request->validated());
                return new RequiredDocumentResource($requiredDocument);
            },
            'Required document created successfully',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/required-documents/{id}",
     *     operationId="showRequiredDocument",
     *     tags={"RequiredDocuments"},
     *     summary="Obtener documento requerido",
     *     description="Retorna el detalle de un documento requerido",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del documento requerido", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="include", in="query", required=false, description="Relaciones a incluir (documentType,process,metadataSchema)", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Documento requerido obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required document retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/RequiredDocumentDetailed")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Documento requerido no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        return catchSync(
            function () use ($request, $id) {
                $requiredDocument = $this->requiredDocumentService->findById($id);

                $includes = ApiIndexBuilder::extractIncludes($request);
                if (!empty($includes)) {
                    $this->requiredDocumentService->resolveIncludes($includes, $requiredDocument);
                }

                return new RequiredDocumentResource($requiredDocument);
            },
            'Required document retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/required-documents/{id}",
     *     operationId="updateRequiredDocument",
     *     tags={"RequiredDocuments"},
     *     summary="Actualizar documento requerido",
     *     description="Actualiza un documento requerido existente",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del documento requerido", @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RequiredDocumentUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documento requerido actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required document updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/RequiredDocumentDetailed")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Documento requerido no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function update(UpdateRequiredDocumentRequest $request, string $id): JsonResponse
    {
        return catchSync(
            function () use ($request, $id) {
                $requiredDocument = $this->requiredDocumentService->update($id, $request->validated());
                return new RequiredDocumentResource($requiredDocument);
            },
            'Required document updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/required-documents/{id}",
     *     operationId="destroyRequiredDocument",
     *     tags={"RequiredDocuments"},
     *     summary="Eliminar documento requerido",
     *     description="Elimina lógicamente un documento requerido",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del documento requerido", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Documento requerido eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required document deleted successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="deleted", type="boolean", example=true))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Documento requerido no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(
            function () use ($id) {
                $this->requiredDocumentService->delete($id);
                return ['deleted' => true];
            },
            'Required document deleted successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/required-documents/{id}/restore",
     *     operationId="restoreRequiredDocument",
     *     tags={"RequiredDocuments"},
     *     summary="Restaurar documento requerido",
     *     description="Restaura un documento requerido eliminado previamente",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del documento requerido", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Documento requerido restaurado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required document restored successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/RequiredDocumentDetailed")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Documento requerido no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function restore(string $id): JsonResponse
    {
        return catchSync(
            function () use ($id) {
                $requiredDocument = $this->requiredDocumentService->restore($id);
                return new RequiredDocumentResource($requiredDocument);
            },
            'Required document restored successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/required-documents/{id}/statistics",
     *     operationId="statisticsRequiredDocument",
     *     tags={"RequiredDocuments"},
     *     summary="Estadísticas de documento requerido",
     *     description="Obtiene estadísticas asociadas a un documento requerido",
     *     @OA\Parameter(name="id", in="path", required=true, description="ID del documento requerido", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required document statistics retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/RequiredDocumentStatistics")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Documento requerido no encontrado", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function statistics(string $id): JsonResponse
    {
        return catchSync(
            function () use ($id) {
                return $this->requiredDocumentService->getStatistics($id);
            },
            'Required document statistics retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/required-documents/bulk-delete",
     *     operationId="bulkDeleteRequiredDocuments",
     *     tags={"RequiredDocuments"},
     *     summary="Eliminación masiva de documentos requeridos",
     *     description="Elimina múltiples documentos requeridos en una sola operación",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RequiredDocumentBulkDeleteRequest")),
     *     @OA\Response(
     *         response=200,
     *         description="Documentos requeridos eliminados exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Required documents deleted successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="deleted_count", type="integer", example=3))
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Error interno del servidor", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid'
        ]);

        return catchSync(
            function () use ($request) {
                $deletedCount = $this->requiredDocumentService->bulkDelete($request->input('ids'));
                return ['deleted_count' => $deletedCount];
            },
            'Required documents deleted successfully'
        );
    }
}
