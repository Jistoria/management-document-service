<?php

namespace App\Services;

use App\Models\StorageUnitType;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StorageUnitTypeService
{
    use ValidatesUuid;

    public function getAll(array $filters = []): Collection
    {
        $query = StorageUnitType::query()->with(['storageUnits']);
        $this->applyFilters($filters, $query);
        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = StorageUnitType::query()->with(['storageUnits']);
        $this->applyFilters($filters, $query);
        return $query->paginate($perPage);
    }

    public function findById(string $id): StorageUnitType
    {
        $this->validateUuid($id);
        return StorageUnitType::with(['storageUnits'])->findOrFail($id);
    }

    public function create(array $data): StorageUnitType
    {
        return StorageUnitType::create($data);
    }

    public function update(string $id, array $data): StorageUnitType
    {
        $this->validateUuid($id);
        $type = $this->findById($id);
        $type->update($data);
        return $type->fresh(['storageUnits']);
    }

    public function delete(string $id): bool
    {
        $this->validateUuid($id);
        $type = $this->findById($id);
        if ($type->activeStorageUnits()->exists()) {
            throw new \InvalidArgumentException('No se puede eliminar el tipo porque tiene unidades asociadas activas');
        }
        return $type->delete();
    }

    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $allowed = ['name', 'code', 'level', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    private function applyFilters(array $filters, Builder $query): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw('unaccent(name) ILIKE unaccent(?)', ["%{$search}%"])
                  ->orWhereRaw('unaccent(code) ILIKE unaccent(?)', ["%{$search}%"]);
            });
        }
        if (!empty($filters['code'])) {
            $query->whereRaw('unaccent(code) ILIKE unaccent(?)', ["%{$filters['code']}%"]);
        }
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        $this->applySorting($query, $filters);
    }
}
