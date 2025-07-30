<?php

namespace App\Services;

use App\Models\ProcessCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProcessCategoryService
{
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
                $q2->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        });

        $query->when(isset($filters['has_subsystems']), function ($q) use ($filters) {
            $q->hasSubsystems($filters['has_subsystems']);
        });

        $query->when(!empty($filters['exclude_subsystem_id']), function ($q) use ($filters) {
            $q->withoutSubsystemId($filters['exclude_subsystem_id']);
        });

        $query->when(!empty($filters['subsystem_id']), function ($q) use ($filters) {
            $q->withSubsystemId($filters['subsystem_id']);
        });

        $query->when(!empty($filters['code']), function ($q) use ($filters) {
            $q->where('code', $filters['code']);
        });

        $query->when(!empty($filters['department_id']), function ($q) use ($filters) {
            $q->where('department_id', $filters['department_id']);
        });

        $query->when(!empty($filters['created_by']), function ($q) use ($filters) {
            $q->where('created_by', $filters['created_by']);
        });
    }
}
