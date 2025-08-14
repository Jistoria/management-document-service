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
     *     summary="List required documents",
     *     description="Returns a list of required documents with support for pagination and collection formats",
     *     @OA\Response(
     *         response=200,
     *         description="Required documents retrieved successfully"
     *     )
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
                        'process_id', 'document_type_id', 'academic_role_id', 'metadata_schema_id', 'mandatory'
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
     *     summary="Create required document",
     *     description="Creates a new required document",
     *     @OA\Response(
     *         response=201,
     *         description="Required document created successfully"
     *     )
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
     *     summary="Show required document",
     *     description="Returns the details of a required document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Required document retrieved successfully"
     *     )
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
     *     summary="Update required document",
     *     description="Updates an existing required document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Required document updated successfully"
     *     )
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
     *     summary="Delete required document",
     *     description="Soft deletes a required document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Required document deleted successfully"
     *     )
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
     *     summary="Restore required document",
     *     description="Restores a previously deleted required document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Required document restored successfully"
     *     )
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
     *     summary="Required document statistics",
     *     description="Returns statistics for a required document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully"
     *     )
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
     *     summary="Bulk delete required documents",
     *     description="Deletes multiple required documents in a single operation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="string", format="uuid"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk deletion performed successfully"
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
                $deletedCount = $this->requiredDocumentService->bulkDelete($request->input('ids'));
                return ['deleted_count' => $deletedCount];
            },
            'Required documents deleted successfully'
        );
    }
}
