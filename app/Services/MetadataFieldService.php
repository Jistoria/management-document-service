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
        $data = MetadataField::convertToSnakeCase($data);
        return MetadataField::create($data);
    }

    /**
     * Update an existing metadata field.
     */
    public function update(string $id, array $data): MetadataField
    {
        $this->validateUuid($id);
        $field = $this->findById($id);

        $data = MetadataField::convertToSnakeCase($data);

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
            $query->where(function ($q) use ($search) {
                $q->where('field_key', 'LIKE', "%{$search}%")
                    ->orWhere('label', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($filters['data_type'])) {
            $query->where('data_type', $filters['data_type']);
        }
        if (!empty($filters['field_key'])) {
            $query->where('field_key', 'LIKE', "%{$filters['field_key']}%");
        }
        if (!empty($filters['type_input_id'])) {
            $query->where('type_input_id', (int) $filters['type_input_id']);
        }
        if (!empty($filters['entity_type_id'])) {
            $query->where('entity_type_id', (int) $filters['entity_type_id']);
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
