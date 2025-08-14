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

class MetadataSchemaController extends Controller
{
    public function __construct(private readonly MetadataSchemaService $metadataSchemaService)
    {
    }

    public function index(FiltersMetadataSchemaRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            return ApiIndexBuilder::build(
                $this->metadataSchemaService,
                MetadataSchemaResource::class,
                $request,
                ApiIndexBuilder::extractFilters($request, ['parent_schema_id', 'is_canonical', 'external_system_id'])
            );
        }, 'Metadata schemas retrieved successfully');
    }

    public function show(string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($metadata_schema) {
            $schema = $this->metadataSchemaService->findById($metadata_schema);
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema retrieved successfully');
    }

    public function store(StoreMetadataSchemaRequest $request): JsonResponse
    {
        return catchSync(function () use ($request) {
            $schema = $this->metadataSchemaService->create($request->validated());
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema created successfully', 201);
    }

    public function update(UpdateMetadataSchemaRequest $request, string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($request, $metadata_schema) {
            $schema = $this->metadataSchemaService->update($metadata_schema, $request->validated());
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema updated successfully');
    }

    public function destroy(string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($metadata_schema) {
            $this->metadataSchemaService->delete($metadata_schema);
            return ['id' => $metadata_schema];
        }, 'Metadata schema deleted successfully');
    }

    public function restore(string $metadata_schema): JsonResponse
    {
        return catchSync(function () use ($metadata_schema) {
            $schema = $this->metadataSchemaService->restore($metadata_schema);
            return new MetadataSchemaResource($schema);
        }, 'Metadata schema restored successfully');
    }

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

