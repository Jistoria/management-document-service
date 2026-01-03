<?php

namespace App\Http\Controllers;

use App\Constants\EntityType;
use App\Constants\MetadataFieldDataType;
use App\Constants\TypeInput;
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
     *     @OA\Parameter(name="search", in="query", description="Search by field_key or label", @OA\Schema(type="string")),
     *     @OA\Parameter(name="data_type", in="query", description="Filter by data type", @OA\Schema(type="string")),
     *     @OA\Parameter(name="entity_type_id", in="query", description="Filter by entity type ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type_input_id", in="query", description="Filter by type input ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="withoutSchemaId", in="query", description="Return fields without schema association", @OA\Schema(type="boolean")),
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
                ApiIndexBuilder::extractFilters($request, ['field_key', 'data_type', 'type_input_id', 'entity_type_id', 'schema_id', 'without_schema_id'])
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
     *             required={"fieldKey","label","dataType"},
     *             @OA\Property(property="fieldKey", type="string", example="student_id"),
     *             @OA\Property(property="label", type="string", example="Student ID"),
     *             @OA\Property(property="entityTypeId", type="integer", nullable=true, example=1),
     *             @OA\Property(property="typeInputId", type="integer", nullable=true, example=1),
     *             @OA\Property(property="dataType", type="string", enum={"string", "integer", "decimal", "date", "boolean", "json", "uuid", "text", "email", "url"}, example="string"),
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
     *             @OA\Property(property="entityTypeId", type="integer", nullable=true),
     *             @OA\Property(property="typeInputId", type="integer", nullable=true),
     *             @OA\Property(property="dataType", type="string", enum={"string", "integer", "decimal", "date", "boolean", "json", "uuid", "text", "email", "url"})
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

    /**
     * @OA\Get(
     *     path="/metadata-fields/catalogs/entity-types",
     *     summary="Get entity types catalog",
     *     tags={"Metadata Fields"},
     *     @OA\Response(
     *         response=200,
     *         description="Entity types catalog retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Entity types catalog retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="key", type="string", example="user"),
     *                     @OA\Property(property="label", type="string", example="Usuario")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getEntityTypes(): JsonResponse
    {
        return catchSync(function () {
            return $this->buildEntityTypes();
        }, 'Entity types catalog retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/metadata-fields/catalogs/type-inputs",
     *     summary="Get type inputs catalog",
     *     tags={"Metadata Fields"},
     *     @OA\Response(
     *         response=200,
     *         description="Type inputs catalog retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Type inputs catalog retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="key", type="string", example="text"),
     *                     @OA\Property(property="label", type="string", example="Texto")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTypeInputs(): JsonResponse
    {
        return catchSync(function () {
            return $this->buildTypeInputs();
        }, 'Type inputs catalog retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/metadata-fields/catalogs/data-types",
     *     summary="Get data types catalog",
     *     tags={"Metadata Fields"},
     *     @OA\Response(
     *         response=200,
     *         description="Data types catalog retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data types catalog retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="key", type="string", example="string"),
     *                     @OA\Property(property="label", type="string", example="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getDataTypes(): JsonResponse
    {
        return catchSync(function () {
            return $this->buildDataTypes();
        }, 'Data types catalog retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/metadata-fields/catalogs",
     *     summary="Get all metadata catalogs",
     *     tags={"Metadata Fields"},
     *     @OA\Response(
     *         response=200,
     *         description="Metadata catalogs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Metadata catalogs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="entityTypes", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="typeInputs", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="dataTypes", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function getCatalogs(): JsonResponse
    {
        return catchSync(function () {
            return [
                'entityTypes' => $this->buildEntityTypes(),
                'typeInputs' => $this->buildTypeInputs(),
                'dataTypes' => $this->buildDataTypes(),
            ];
        }, 'Metadata catalogs retrieved successfully');
    }

    private function buildEntityTypes(): array
    {
        $entityTypes = [];
        foreach (EntityType::all() as $id) {
            $entityTypes[] = [
                'id' => $id,
                'key' => EntityType::getKey($id),
                'label' => EntityType::getLabel($id),
            ];
        }
        return $entityTypes;
    }

    private function buildTypeInputs(): array
    {
        $typeInputs = [];
        foreach (TypeInput::all() as $id) {
            $typeInputs[] = [
                'id' => $id,
                'key' => TypeInput::getKey($id),
                'label' => TypeInput::getLabel($id),
            ];
        }
        return $typeInputs;
    }

    private function buildDataTypes(): array
    {
        return array_map(
            fn (string $type) => ['key' => $type, 'label' => $type],
            MetadataFieldDataType::ALL
        );
    }
}
