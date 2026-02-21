<?php

namespace App\Services;

use App\Models\Process;
use App\Models\ProcessCategory;
use App\Traits\ValidatesUuid;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProcessCategoryService
{

    use ValidatesUuid;

    /**
     * Get all process categories with optional filtering
     */
    public function getAll(array $filters = []): Collection
    {
        $query = ProcessCategory::query();

        $this->applyFilters($filters, $query);

        $this->applySorting($query, $filters);

        return $query->get();
    }


    /**
     * Get paginated careers with optional filtering
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = ProcessCategory::query();

        $this->applyFilters($filters, $query);

        $this->applySorting($query, $filters);

        return $query->paginate($perPage);
    }



    /**
     * Apply sorting to the query
     */
    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        // Campos permitidos para ordenamiento
        $allowedSortFields = [
            'name',
            'code',
            'created_at',
            'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
    }

    public function findById(string $id): ProcessCategory
    {
        $this->validateUuid($id);

        return ProcessCategory::findOrFail($id);
    }

    public function findByCode($code): ProcessCategory|null
    {
        return ProcessCategory::byCode($code)->first();
    }

    public function create(array $data): ProcessCategory
    {
        $data = ProcessCategory::convertToSnakeCase($data);

        if ($this->codeExists($data['code'])) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        if ($this->nameExists($data['name'])) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe.");
        }

        return ProcessCategory::create($data);
    }

    public function update(array $data, string $id): ProcessCategory
    {
        $this->validateUuid($id, ProcessCategory::class);

        $data = ProcessCategory::convertToSnakeCase($data);

        $processCategory = $this->findById($id);

        if ($this->codeExists($data['code'], $id)) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        if ($this->nameExists($data['name'], $id)) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe.");
        }

        $processCategory->update($data);

        return $processCategory->fresh();
    }

    public function delete(string $id): bool
    {
        $this->validateUuid($id, ProcessCategory::class);
        $processCategory = $this->findById($id);

        return $processCategory->delete();
    }

    public function getProcesses(string $categoryId): Collection
    {
        $this->validateUuid($categoryId, ProcessCategory::class);

        return Process::where('process_category_id', $categoryId)->get();
    }

    private function codeExists(string $code, ?string $excludeId = null): bool
    {
        $query = ProcessCategory::where('code', $code);

        if ($excludeId) {
            $this->validateUuid($excludeId);
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function nameExists(string $name, ?string $excludeId = null): bool
    {
        $query = ProcessCategory::where('name', $name);
        if ($excludeId) {
            $this->validateUuid($excludeId);
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * @param array $filters
     * @param Builder $query
     * @return void
     */
    protected function applyFilters(array $filters, Builder $query): void
    {
        $query->when(!empty($filters['search']), function ($q) use ($filters) {
            $search = $filters['search'];
            $q->where(function ($q2) use ($search) {
                $q2->whereRaw('unaccent(name) ILIKE unaccent(?)', ["%{$search}%"])
                    ->orWhereRaw('unaccent(code) ILIKE unaccent(?)', ["%{$search}%"]);
            });
        });

        $query->when(!empty($filters['subsystem_id']), function ($q) use ($filters) {
            $q->bySubsystem($filters['subsystem_id']);
        });

        $query->when(!empty($filters['code']), function ($q) use ($filters) {
            $q->where('code', $filters['code']);
        });

        $query->when(!empty($filters['created_by']), function ($q) use ($filters) {
            $q->where('created_by', $filters['created_by']);
        });
    }
}
