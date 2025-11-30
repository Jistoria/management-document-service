<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\MetadataSchema\FiltersMetadataSchemaRequest;
use App\Http\Requests\MetadataSchema\StoreMetadataSchemaRequest;
use App\Http\Requests\MetadataSchema\UpdateMetadataSchemaRequest;
use App\Http\Resources\MetadataSchemaResource;
use App\Services\MetadataSchemaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * @OA\Tag(
 *     name="Metadata Schemas",
 *     description="API endpoints for managing metadata schemas"
 * )
 */
class MetadataSchemaController extends Controller
{
    public function __construct(private readonly MetadataSchemaService $metadataSchemaService)
    {
    }

    /**
     * @OA\Get(
     *     path="/metadata-schemas",
     *     summary="List metadata schemas",
     *     tags={"Metadata Schemas"},
     *     @OA\Parameter(name="search", in="query", description="Search by name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="version", in="query", description="Filter by version", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata schemas retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata schemas retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MetadataSchema"))
     *         )
     *     )
     * )
     */
    public function index(FiltersMetadataSchemaRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            return ApiIndexBuilder::build(
                $this->metadataSchemaService,
                MetadataSchemaResource::class,
                $request,
                ApiIndexBuilder::extractFilters($request, ['version'])
            );
        }, 'Metadata schemas retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/metadata-schemas/{id}",
     *     summary="Retrieve a metadata schema",
     *     tags={"Metadata Schemas"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata schema ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata schema retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata schema retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataSchema")
     *         )
     *     )
     * )
     */
    public function show(string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($metadata_schema) {
            $schema = $this->metadataSchemaService->findById($metadata_schema);
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/metadata-schemas",
     *     summary="Create a metadata schema",
     *     tags={"Metadata Schemas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="version", type="integer", nullable=true, example=1),
     *             @OA\Property(
     *                 property="fields",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"metadataFieldId"},
     *                     @OA\Property(property="metadataFieldId", type="string", format="uuid"),
     *                     @OA\Property(property="isRequired", type="boolean"),
     *                     @OA\Property(property="sortOrder", type="integer", nullable=true),
     *                     @OA\Property(property="defaultValue", type="string", nullable=true)
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Metadata schema created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata schema created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataSchema")
     *         )
     *     )
     * )
     */
    public function store(StoreMetadataSchemaRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $schema = $this->metadataSchemaService->create($request->validated());
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema created successfully', 201);
    }

    /**
     * @OA\Put(
     *     path="/metadata-schemas/{id}",
     *     summary="Update a metadata schema",
     *     tags={"Metadata Schemas"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata schema ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", nullable=true),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="version", type="integer", nullable=true),
     *             @OA\Property(
     *                 property="fields",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="metadataFieldId", type="string", format="uuid"),
     *                     @OA\Property(property="isRequired", type="boolean"),
     *                     @OA\Property(property="sortOrder", type="integer", nullable=true),
     *                     @OA\Property(property="defaultValue", type="string", nullable=true)
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata schema updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata schema updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataSchema")
     *         )
     *     )
     * )
     */
    public function update(UpdateMetadataSchemaRequest $request, string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($request, $metadata_schema) {
            $schema = $this->metadataSchemaService->update($metadata_schema, $request->validated());
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/metadata-schemas/{id}",
     *     summary="Delete a metadata schema",
     *     tags={"Metadata Schemas"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata schema ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata schema deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata schema deleted successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="string", format="uuid"))
     *         )
     *     )
     * )
     */
    public function destroy(string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($metadata_schema) {
            $this->metadataSchemaService->delete($metadata_schema);
            return ['id' => $metadata_schema];
        }, 'Metadata schema deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/metadata-schemas/{id}/restore",
     *     summary="Restore a metadata schema",
     *     tags={"Metadata Schemas"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata schema ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata schema restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata schema restored successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataSchema")
     *         )
     *     )
     * )
     */
    public function restore(string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($metadata_schema) {
            $schema = $this->metadataSchemaService->restore($metadata_schema);
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema restored successfully');
    }

    /**
     * @OA\Post(
     *     path="/metadata-schemas/bulk-delete",
     *     summary="Bulk delete metadata schemas",
     *     tags={"Metadata Schemas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid"),
     *                 description="Array of metadata schema IDs to delete"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk deletion completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bulk deletion completed"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="deleted_count", type="integer", example=3))
     *         )
     *     )
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'uuid'
        ]);

        return catchSync(function () use ($request) {
            $deleted = $this->metadataSchemaService->bulkDelete($request->input('ids'));
            return ['deleted_count' => $deleted];
        }, 'Bulk deletion completed');
    }
}

