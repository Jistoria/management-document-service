<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentType\StoreDocumentTypeRequest;
use App\Http\Requests\DocumentType\UpdateDocumentTypeRequest;
use App\Http\Requests\DocumentType\FiltersDocumentTypeRequest;
use App\Http\Resources\DocumentTypeResource;
use App\Services\DocumentTypeService;
use App\Helpers\ApiIndexBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * Controller for Document Type operations
 *
 * Handles HTTP requests for document type CRUD operations
 * using the DocumentTypeService and catchSync helper with proper resources.
 */
class DocumentTypeController extends Controller
{
    protected DocumentTypeService $documentTypeService;

    public function __construct(DocumentTypeService $documentTypeService)
    {
        $this->documentTypeService = $documentTypeService;
    }

    /**
     * @OA\Get(
     *     path="/document-types",
     *     operationId="getDocumentTypes",
     *     tags={"DocumentTypes"},
     *     summary="Obtener listado de tipos de documento",
     *     description="Retorna el listado de tipos de documento con soporte para múltiples formatos: paginación, colección, minimal, dropdown, pluck",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Búsqueda por nombre, código o descripción",
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
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Filtra por código",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_by",
     *         in="query",
     *         description="Filtra por creador",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipos de documento obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DocumentType"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function index(FiltersDocumentTypeRequest $request): JsonResponse
    {
        return catchSync(
            function () use ($request) {
                return ApiIndexBuilder::build(
                    $this->documentTypeService,
                    DocumentTypeResource::class,
                    $request,
                    ApiIndexBuilder::extractFilters($request, ['code', 'created_by'])
                );
            },
            'Tipos de documento obtenidos exitosamente'
        );
    }

    /**
     * @OA\Post(
     *     path="/document-types",
     *     operationId="storeDocumentType",
     *     tags={"DocumentTypes"},
     *     summary="Crear nuevo tipo de documento",
     *     description="Crea un nuevo tipo de documento en el sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", example="Certificado Académico"),
     *             @OA\Property(property="code", type="string", example="CERT_ACAD"),
     *             @OA\Property(property="description", type="string", example="Certificado de estudios académicos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tipo de documento creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de documento creado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/DocumentType")
     *         )
     *     )
     * )
     */
    public function store(StoreDocumentTypeRequest $request): JsonResponse
    {
        return catchSync(
            function () use ($request) {
                $documentType = $this->documentTypeService->create($request->validated());
                return new DocumentTypeResource($documentType);
            },
            'Tipo de documento creado exitosamente',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/document-types/{id}",
     *     operationId="showDocumentType",
     *     tags={"DocumentTypes"},
     *     summary="Obtener tipo de documento por ID",
     *     description="Retorna los detalles de un tipo de documento específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de documento",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Relaciones a incluir (statistics, requiredDocuments)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de documento obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de documento obtenido exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/DocumentTypeDetailed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tipo de documento no encontrado"
     *     )
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        return catchSync(
            function () use ($request, $id) {
                $documentType = $this->documentTypeService->findById($id);

                if (!$documentType) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Tipo de documento no encontrado');
                }

                // Process includes
                $includes = ApiIndexBuilder::extractIncludes($request);
                if (!empty($includes)) {
                    $this->documentTypeService->resolveIncludes($includes, $documentType);
                }

                return new DocumentTypeResource($documentType);
            },
            'Tipo de documento obtenido exitosamente'
        );
    }

    /**
     * @OA\Put(
     *     path="/document-types/{id}",
     *     operationId="updateDocumentType",
     *     tags={"DocumentTypes"},
     *     summary="Actualizar tipo de documento",
     *     description="Actualiza un tipo de documento existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de documento",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Certificado Académico Actualizado"),
     *             @OA\Property(property="code", type="string", example="CERT_ACAD_UPD"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de documento actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tipo de documento actualizado exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/DocumentType")
     *         )
     *     )
     * )
     */
    public function update(UpdateDocumentTypeRequest $request, string $id): JsonResponse
    {
        return catchSync(
            function () use ($request, $id) {
                $documentType = $this->documentTypeService->update($id, $request->validated());
                return new DocumentTypeResource($documentType);
            },
            'Tipo de documento actualizado exitosamente'
        );
    }

    /**
     * @OA\Delete(
     *     path="/document-types/{id}",
     *     operationId="destroyDocumentType",
     *     tags={"DocumentTypes"},
     *     summary="Eliminar tipo de documento",
     *     description="Elimina (soft delete) un tipo de documento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de documento",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de documento eliminado exitosamente"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(
            function () use ($id) {
                $this->documentTypeService->delete($id);
                return ['deleted' => true];
            },
            'Tipo de documento eliminado exitosamente'
        );
    }

    /**
     * @OA\Post(
     *     path="/document-types/{id}/restore",
     *     operationId="restoreDocumentType",
     *     tags={"DocumentTypes"},
     *     summary="Restaurar tipo de documento eliminado",
     *     description="Restaura un tipo de documento previamente eliminado",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de documento",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de documento restaurado exitosamente"
     *     )
     * )
     */
    public function restore(string $id): JsonResponse
    {
        return catchSync(
            function () use ($id) {
                $documentType = $this->documentTypeService->restore($id);
                return new DocumentTypeResource($documentType);
            },
            'Tipo de documento restaurado exitosamente'
        );
    }

    /**
     * @OA\Get(
     *     path="/document-types/{id}/statistics",
     *     operationId="statisticsDocumentType",
     *     tags={"DocumentTypes"},
     *     summary="Obtener estadísticas del tipo de documento",
     *     description="Retorna estadísticas detalladas de un tipo de documento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del tipo de documento",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente"
     *     )
     * )
     */
    public function statistics(string $id): JsonResponse
    {
        return catchSync(
            function () use ($id) {
                return $this->documentTypeService->getStatistics($id);
            },
            'Estadísticas del tipo de documento obtenidas exitosamente'
        );
    }

    /**
     * @OA\Get(
     *     path="/document-types/code/{code}",
     *     operationId="findDocumentTypeByCode",
     *     tags={"DocumentTypes"},
     *     summary="Buscar tipo de documento por código",
     *     description="Busca un tipo de documento específico por su código único",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="Código del tipo de documento",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tipo de documento encontrado exitosamente"
     *     )
     * )
     */
    public function findByCode(string $code): JsonResponse
    {
        return catchSync(
            function () use ($code) {
                $documentType = $this->documentTypeService->findByCode($code);

                if (!$documentType) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Tipo de documento no encontrado');
                }

                return new DocumentTypeResource($documentType);
            },
            'Tipo de documento encontrado exitosamente'
        );
    }

    /**
     * @OA\Post(
     *     path="/document-types/bulk-delete",
     *     operationId="bulkDeleteDocumentTypes",
     *     tags={"DocumentTypes"},
     *     summary="Eliminación masiva de tipos de documento",
     *     description="Elimina múltiples tipos de documento en una sola operación",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid"),
     *                 example={"uuid1", "uuid2", "uuid3"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Eliminación masiva realizada exitosamente"
     *     )
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
                $deletedCount = $this->documentTypeService->bulkDelete($request->input('ids'));
                return ['deleted_count' => $deletedCount];
            },
            'Eliminación masiva de tipos de documento realizada exitosamente'
        );
    }
}
