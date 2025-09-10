<?php

namespace App\Services;

use App\Models\StorageUnit;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StorageUnitService
{
    use ValidatesUuid;

    public function getAll(array $filters = []): Collection
    {
        $query = StorageUnit::query()->with(['storageUnitType', 'parent', 'children']);
        $this->applyFilters($filters, $query);
        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = StorageUnit::query()->with(['storageUnitType', 'parent', 'children']);
        $this->applyFilters($filters, $query);
        return $query->paginate($perPage);
    }

    public function findById(string $id): StorageUnit
    {
        $this->validateUuid($id);
        return StorageUnit::with(['storageUnitType', 'parent', 'children'])->findOrFail($id);
    }

    public function create(array $data): StorageUnit
    {
        return StorageUnit::create($data);
    }

    public function update(string $id, array $data): StorageUnit
    {
        $this->validateUuid($id);
        $unit = $this->findById($id);
        $unit->update($data);
        return $unit->fresh(['storageUnitType', 'parent', 'children']);
    }

    public function delete(string $id): bool
    {
        $this->validateUuid($id);
        $unit = $this->findById($id);
        if ($unit->activeChildren()->exists()) {
            throw new \InvalidArgumentException('No se puede eliminar la unidad porque tiene subunidades activas');
        }
        return $unit->delete();
    }

    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'label';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $allowed = ['label', 'code', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    private function applyFilters(array $filters, Builder $query): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('label', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($filters['storage_unit_type_id'])) {
            $query->where('storage_unit_type_id', $filters['storage_unit_type_id']);
        }
        if (array_key_exists('parent_id', $filters)) {
            $query->where('parent_id', $filters['parent_id']);
        }
        if (!empty($filters['code'])) {
            $query->where('code', 'LIKE', "%{$filters['code']}%");
        }
        $this->applySorting($query, $filters);
    }
}
