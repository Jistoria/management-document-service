<?php

namespace App\Services;

use App\Models\Career;
use App\Models\HeadOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service class for Career operations
 *
 * Handles business logic for career management including CRUD operations,
 * filtering, pagination, and relationship management.
 */
class CareerService
{

    /**
     * Get all careers with optional filtering
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Career::query()->with(['department', 'department.headOffice']);

        // Apply filters
        $this->applyFilters($filters, $query);

        return $query->get();
    }

    /**
     * Get paginated careers with optional filtering
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Career::query()->with(['department', 'department.headOffice']);

        $this->applyFilters($filters, $query);

        return $query->paginate($perPage);
    }

    /**
     * Find a career by ID
     */
    public function findById(string $id): ?Career
    {
        return Career::with(['department', 'department.headOffice', 'subsystems'])->find($id);
    }

    /**
     * Find a career by code
     */
    public function findByCode(string $code): ?Career
    {
        return Career::with(['department', 'department.headOffice'])->where('code', $code)->first();
    }

    /**
     * Create a new career
     */
    public function create(array $data): Career
    {
        // Convert camelCase to snake_case for database operations
        $data = Career::convertToSnakeCase($data);

        // Validate department exists
        if (!$this->validateDepartmentExists($data['department_id'])) {
            throw new \InvalidArgumentException('El departamento especificado no existe');
        }

        // Check if code is unique (if provided)
        if (!empty($data['code']) && $this->codeExists($data['code'])) {
            throw new \InvalidArgumentException('El código ya está en uso');
        }

        $career = Career::create($data);
        return $career->load(['department', 'department.headOffice']);
    }

    /**
     * Update an existing career
     */
    public function update(string $id, array $data): Career
    {
        // Convert camelCase to snake_case for database operations
        $data = Career::convertToSnakeCase($data);

        $career = Career::findOrFail($id);

        // Validate department exists if being updated
        if (isset($data['department_id']) && !$this->validateDepartmentExists($data['department_id'])) {
            throw new \InvalidArgumentException('El departamento especificado no existe');
        }

        // Check if code is unique (if provided and different from current)
        if (!empty($data['code']) && $data['code'] !== $career->code && $this->codeExists($data['code'])) {
            throw new \InvalidArgumentException('El código ya está en uso');
        }

        // Increment version for optimistic locking
        $data['version'] = ($career->version ?? 0) + 1;

        $career->update($data);
        return $career->load(['department', 'department.headOffice']);
    }

    /**
     * Delete a career (soft delete)
     */
    public function delete(string $id): bool
    {
        $career = Career::findOrFail($id);

        // Check if career has active subsystems
        if ($career->activeSubsystems()->count() > 0) {
            throw new \InvalidArgumentException('No se puede eliminar la carrera porque tiene subsistemas activos');
        }

        return $career->delete();
    }

    /**
     * Restore a soft-deleted career
     */
    public function restore(string $id): Career
    {
        $career = Career::withTrashed()->findOrFail($id);

        if (!$career->trashed()) {
            throw new \InvalidArgumentException('La carrera no está eliminada');
        }

        $career->restore();
        return $career->load(['department', 'department.headOffice']);
    }

    /**
     * Get career statistics
     */
    public function getStatistics(string $id): array
    {
        $career = Career::withCount(['subsystems', 'activeSubsystems'])
            ->with(['department', 'department.headOffice'])
            ->findOrFail($id);

        return [
            'subsystems_count' => $career->subsystems_count,
            'active_subsystems_count' => $career->active_subsystems_count,
            'department' => $career->department ? [
                'id' => $career->department->id,
                'name' => $career->department->name,
                'code' => $career->department->code,
            ] : null,
            'head_office' => $career->department?->headOffice ? [
                'id' => $career->department->headOffice->id,
                'name' => $career->department->headOffice->name,
                'code' => $career->department->headOffice->code,
            ] : null,
            'created_at' => $career->created_at?->toISOString(),
            'last_updated' => $career->updated_at?->toISOString(),
            'version' => $career->version,
        ];
    }

    /**
     * Get full hierarchy including department and head office
     */
    public function getFullHierarchy(string $id): ?Career
    {
        return Career::with([
            'department.headOffice',
            'subsystems' => function ($query) {
                $query->whereNull('deleted_at');
            }
        ])->find($id);
    }

    /**
     * Bulk delete careers
     */
    public function bulkDelete(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $this->delete($id);
                $deletedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other deletions
                continue;
            }
        }

        return $deletedCount;
    }

    /**
     * Get careers by department
     */
    public function getByDepartment(string $departmentId, array $filters = []): Collection
    {
        $filters['department_id'] = $departmentId;
        return $this->getAll($filters);
    }

    /**
     * Validate if department exists
     */
    private function validateDepartmentExists(string $departmentId): bool
    {
        return \App\Models\Department::where('id', $departmentId)->exists();
    }

    /**
     * Check if code already exists
     */
    private function codeExists(string $code): bool
    {
        return Career::where('code', $code)->exists();
    }

    /**
     * Resolve includes for career relationships
     */
    public function resolveIncludes(array $requestedIncludes, $career): void
    {
        $resolved = [];
        $context = []; // Store what was requested for the resource

        foreach ($requestedIncludes as $include) {
            $include = trim($include); // Clean whitespace

            match ($include) {
                'department' => $resolved[] = 'department',
                'head_office' => $resolved[] = 'department.headOffice',
                'subsystems' => $resolved[] = 'subsystems',
                'hierarchy' => $resolved = array_merge($resolved, [
                    'department',
                    'department.headOffice',
                    'subsystems',
                ]),
                'statistics' => null, // Statistics don't require loading relationships
                default => null, // Ignora includes no válidos
            };

            // Store the original request for resource context
            $context[] = $include;
        }

        // Load the resolved relationships
        if (!empty($resolved)) {
            $career->load(array_unique($resolved));
        }

        // Store requested includes as an attribute for the resource to check
        $career->setAttribute('_requested_includes', $context);
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
            'updated_at',
            'created_by',
            'department_id',
        ];

        // Verificar que el campo sea válido
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Fallback por defecto
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

        // Apply sorting
        $this->applySorting($query, $filters);
    }
}
