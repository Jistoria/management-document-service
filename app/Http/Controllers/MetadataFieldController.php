<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Requests\MetadataField\FiltersMetadataFieldRequest;
use App\Http\Requests\MetadataField\StoreMetadataFieldRequest;
use App\Http\Requests\MetadataField\UpdateMetadataFieldRequest;
use App\Http\Resources\MetadataFieldResource;
use App\Services\MetadataFieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * @OA\Tag(
 *     name="Metadata Fields",
 *     description="API endpoints for managing metadata fields"
 * )
 */
class MetadataFieldController extends Controller
{
    public function __construct(private readonly MetadataFieldService $metadataFieldService)
    {
    }

    /**
     * @OA\Get(
     *     path="/metadata-fields",
     *     summary="List metadata fields",
     *     tags={"Metadata Fields"},
     *     @OA\Parameter(name="search", in="query", description="Search by name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="schema_id", in="query", description="Filter by schema ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="data_type", in="query", description="Filter by data type", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_required", in="query", description="Filter by required flag", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="is_reference", in="query", description="Filter by reference flag", @OA\Schema(type="boolean")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata fields retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata fields retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MetadataField"))
     *         )
     *     )
     * )
     */
    public function index(FiltersMetadataFieldRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            return ApiIndexBuilder::build(
                $this->metadataFieldService,
                MetadataFieldResource::class,
                $request,
                ApiIndexBuilder::extractFilters($request, ['field_key', 'data_type', 'type_input_id', 'entity_type_id', 'is_reference'])
            );
        }, 'Metadata fields retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/metadata-fields/{id}",
     *     summary="Retrieve a metadata field",
     *     tags={"Metadata Fields"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata field ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata field retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata field retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataField")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $field = $this->metadataFieldService->findById($id);
            return new MetadataFieldResource($field);
        }, 'Metadata field retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/metadata-fields",
     *     summary="Create a metadata field",
     *     tags={"Metadata Fields"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fieldKey","label","typeInputId","dataType"},
     *             @OA\Property(property="fieldKey", type="string"),
     *             @OA\Property(property="label", type="string"),
     *             @OA\Property(property="entityTypeId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="typeInputId", type="string"),
     *             @OA\Property(property="dataType", type="string"),
     *             @OA\Property(property="isReference", type="boolean"),
     *             @OA\Property(property="referenceEntity", type="string", nullable=true),
     *             @OA\Property(property="referenceColumn", type="string", nullable=true),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Metadata field created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata field created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataField")
     *         )
     *     )
     * )
     */
    public function store(StoreMetadataFieldRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $field = $this->metadataFieldService->create($request->validated());
            return new MetadataFieldResource($field);
        }, 'Metadata field created successfully');
    }

    /**
     * @OA\Put(
     *     path="/metadata-fields/{id}",
     *     summary="Update a metadata field",
     *     tags={"Metadata Fields"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata field ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="fieldKey", type="string"),
     *             @OA\Property(property="label", type="string"),
     *             @OA\Property(property="entityTypeId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="typeInputId", type="string"),
     *             @OA\Property(property="dataType", type="string"),
     *             @OA\Property(property="isReference", type="boolean"),
     *             @OA\Property(property="referenceEntity", type="string", nullable=true),
     *             @OA\Property(property="referenceColumn", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata field updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata field updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/MetadataField")
     *         )
     *     )
     * )
     */
    public function update(UpdateMetadataFieldRequest $request, string $id): JsonResponse
    {
        return catchSync(function () use ($request, $id) {
            $field = $this->metadataFieldService->update($id, $request->validated());
            return new MetadataFieldResource($field);
        }, 'Metadata field updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/metadata-fields/{id}",
     *     summary="Delete a metadata field",
     *     tags={"Metadata Fields"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Metadata field ID", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Metadata field deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata field deleted successfully"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="string", format="uuid"))
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return catchSync(function () use ($id) {
            $this->metadataFieldService->delete($id);
            return ['id' => $id];
        }, 'Metadata field deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/metadata-fields/bulk-delete",
     *     summary="Bulk delete metadata fields",
     *     tags={"Metadata Fields"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="string", format="uuid"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk deletion completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bulk deletion completed"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="deleted_count", type="integer", example=2))
     *         )
     *     )
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid'
        ]);

        return catchSync(function () use ($request) {
            $deleted = $this->metadataFieldService->bulkDelete($request->input('ids'));
            return ['deleted_count' => $deleted];
        }, 'Bulk deletion completed');
    }
}
