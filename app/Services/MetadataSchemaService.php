<?php

namespace App\Services;

use App\Models\MetadataSchema;
use App\Models\MetadataSchemaField;
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
        $query->with('metadataFields');
        $this->applyFilters($query, $filters);
        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = MetadataSchema::query();
        $query->with('metadataFields');
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findById(string $id): MetadataSchema
    {
        $this->validateUuid($id);
        return MetadataSchema::with('metadataFields')->findOrFail($id);
    }

    public function create(array $data): MetadataSchema
    {
        $data = MetadataSchema::convertToSnakeCase($data);
        $schema = MetadataSchema::create($data);

        if (!empty($data['fields'])) {
            $this->syncFields($schema, $data['fields']);
        }

        return $schema->load('metadataFields');
    }

    public function update(string $id, array $data): MetadataSchema
    {
        $this->validateUuid($id);
        $schema = $this->findById($id);

        $data = MetadataSchema::convertToSnakeCase($data);

        $schema->update($data);

        if (!empty($data['fields'])) {
            $this->syncFields($schema, $data['fields']);
        }

        return $schema->load('metadataFields');
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
        if (isset($filters['version'])) {
            $query->where('version', (int) $filters['version']);
        }
    }

    public function resolveIncludes(array $requestedIncludes, MetadataSchema $schema): void
    {
        // No dynamic includes for now
    }

    private function syncFields(MetadataSchema $schema, array $fields): void
    {
        $pivotData = [];

        foreach ($fields as $field) {
            $fieldData = MetadataSchemaField::convertToSnakeCase($field);
            $fieldId = $fieldData['metadata_field_id'] ?? null;

            if (!$fieldId) {
                continue;
            }

            $pivotData[$fieldId] = [
                'is_required' => $fieldData['is_required'] ?? false,
                'sort_order' => $fieldData['sort_order'] ?? null,
                'default_value' => $fieldData['default_value'] ?? null,
            ];
        }

        if (!empty($pivotData)) {
            $schema->metadataFields()->sync($pivotData);
        }
    }
}

