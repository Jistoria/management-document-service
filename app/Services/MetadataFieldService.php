<?php

namespace App\Services;

use App\Models\MetadataField;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service layer for metadata field operations.
 */
class MetadataFieldService
{
    use ValidatesUuid;

    /**
     * Retrieve all metadata fields with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = MetadataField::query();
        $this->applyFilters($query, $filters);
        return $query->get();
    }

    /**
     * Retrieve paginated metadata fields.
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = MetadataField::query();
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    /**
     * Find a metadata field by its identifier.
     */
    public function findById(string $id): MetadataField
    {
        $this->validateUuid($id);
        return MetadataField::findOrFail($id);
    }

    /**
     * Create a new metadata field.
     */
    public function create(array $data): MetadataField
    {
        return MetadataField::create($data);
    }

    /**
     * Update an existing metadata field.
     */
    public function update(string $id, array $data): MetadataField
    {
        $this->validateUuid($id);
        $field = $this->findById($id);
        $field->update($data);
        return $field;
    }

    /**
     * Delete a metadata field.
     */
    public function delete(string $id): bool
    {
        $this->validateUuid($id);
        $field = $this->findById($id);
        return (bool) $field->delete();
    }

    /**
     * Delete multiple metadata fields by their IDs.
     *
     * @throws \InvalidArgumentException
     */
    public function bulkDelete(array $ids): int
    {
        $validIds = array_filter($ids, fn ($id) => $this->isValidUuid($id));
        if (empty($validIds)) {
            throw new \InvalidArgumentException('No valid IDs provided');
        }
        return MetadataField::whereIn('id', $validIds)->delete();
    }

    /**
     * Apply filter conditions to the query.
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'LIKE', "%{$search}%");
        }
        if (!empty($filters['schema_id'])) {
            $query->where('schema_id', $filters['schema_id']);
        }
        if (!empty($filters['data_type'])) {
            $query->where('data_type', $filters['data_type']);
        }
        if (isset($filters['is_required'])) {
            $query->where('is_required', filter_var($filters['is_required'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
        if (isset($filters['is_reference'])) {
            $query->where('is_reference', filter_var($filters['is_reference'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
    }

    /**
     * Resolve dynamic includes on the resource.
     */
    public function resolveIncludes(array $requestedIncludes, $metadataField): void
    {
        // No extra includes yet
    }
}
