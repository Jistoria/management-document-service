<?php

namespace App\Services;

use App\Models\MetadataSchema;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MetadataSchemaService
{
    use ValidatesUuid;

    public function getAll(array $filters = []): Collection
    {
        $query = MetadataSchema::query();
        $this->applyFilters($query, $filters);
        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = MetadataSchema::query();
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findById(string $id): MetadataSchema
    {
        $this->validateUuid($id);
        return MetadataSchema::findOrFail($id);
    }

    public function create(array $data): MetadataSchema
    {
        return MetadataSchema::create($data);
    }

    public function update(string $id, array $data): MetadataSchema
    {
        $this->validateUuid($id);
        $schema = $this->findById($id);
        $schema->update($data);
        return $schema;
    }

    public function delete(string $id): bool
    {
        $this->validateUuid($id);
        $schema = $this->findById($id);
        return (bool) $schema->delete();
    }

    public function restore(string $id): MetadataSchema
    {
        $this->validateUuid($id);
        $schema = MetadataSchema::withTrashed()->findOrFail($id);
        $schema->restore();
        return $schema;
    }

    public function bulkDelete(array $ids): int
    {
        $validIds = array_filter($ids, fn($id) => $this->isValidUuid($id));
        if (empty($validIds)) {
            throw new \InvalidArgumentException('No valid IDs provided');
        }
        return MetadataSchema::whereIn('id', $validIds)->delete();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($filters['parent_schema_id'])) {
            $query->where('parent_schema_id', $filters['parent_schema_id']);
        }
        if (isset($filters['is_canonical'])) {
            $query->where('is_canonical', filter_var($filters['is_canonical'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
        }
        if (!empty($filters['external_system_id'])) {
            $query->where('external_system_id', $filters['external_system_id']);
        }
    }

    public function resolveIncludes(array $requestedIncludes, MetadataSchema $schema): void
    {
        // No dynamic includes for now
    }
}

