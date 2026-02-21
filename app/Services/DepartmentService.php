<?php

namespace App\Services;

use App\Constants\HttpStatus;
use App\Models\Department;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Service for Department business logic
 *
 * Handles all business operations for departments including
 * CRUD operations, validation, and complex queries.
 */
class DepartmentService
{

    use ValidatesUuid;

    /**
     * Get all active departments
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Department::query()->with(['headOffice', 'careers']);

        $this->applyFilters($filters, $query);

        return $query->get();
    }

    /**
     * Get paginated departments
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Department::query()->with(['headOffice', 'careers']);

        $this->applyFilters($filters, $query);

        return $query->paginate($perPage);
    }

    /**
     * Find department by ID
     */
    public function findById(string $id): ?Department
    {

        $this->validateUuid($id);

        return Department::with(['headOffice', 'careers.subsystems'])
            ->findOrFail($id);
    }

    /**
     * Find department by code
     */
    public function findByCode(string $code): ?Department
    {
        return Department::byCode($code)
            ->with(['headOffice', 'careers'])
            ->first();
    }

    /**
     * Create a new department
     */
    public function create(array $data): Department
    {
        // Convert camelCase to snake_case for database operations
        $data = Department::convertToSnakeCase($data);

        if ($this->codeExists($data['code'] ?? null)) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        if ($this->nameExists($data['name'], $data['head_office_id'])) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe en esta sede.");
        }

        return Department::create($data);
    }

    /**
     * Update existing department
     */
    public function update(string $id, array $data): Department
    {
        $this->validateUuid($id);

        $data = Department::convertToSnakeCase($data);

        $department = $this->findById($id);

        if (!$department) {
            throw new ModelNotFoundException("Departamento no encontrado.");
        }

        if (isset($data['code']) && $this->codeExists($data['code'], $id)) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        if (isset($data['name']) && $this->nameExists($data['name'], $data['head_office_id'] ?? $department->head_office_id, $id)) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe en esta sede.");
        }

        $department->update($data);

        return $department->fresh(['headOffice', 'careers']);
    }

    /**
     * Soft delete department
     */
    public function delete(string $id): bool
    {

        $this->validateUuid($id);

        $department = $this->findById($id);

        if (!$department) {
            throw new ModelNotFoundException("Departamento no encontrado.");
        }

        // Check if has active careers
        if ($department->activeCareers()->exists()) {
            throw new \InvalidArgumentException("No se puede eliminar el departamento porque tiene carreras activas.", HttpStatus::CONFLICT);
        }

        return $department->delete();
    }

    /**
     * Restore soft deleted department
     */
    public function restore(string $id): Department
    {

        $this->validateUuid($id);

        $department = Department::withTrashed()->find($id);

        if (!$department) {
            throw new ModelNotFoundException("Departamento no encontrado.");
        }

        if (!$department->trashed()) {
            throw new \InvalidArgumentException("El departamento no está eliminado.");
        }

        $department->restore();

        return $department->fresh(['headOffice', 'careers']);
    }

    /**
     * Get department hierarchy with all relationships
     */
    public function getFullHierarchy(string $id): ?Department
    {

        $this->validateUuid($id);

        return Department::with(['headOffice', 'careers.subsystems'])
            ->findOrFail($id);
    }

    /**
     * Get statistics for department
     */
    public function getStatistics(string $id): array
    {
        $department = $this->findById($id);

        return [
            'careers_count' => $department->activeCareers()->count(),
            'head_office' => [
                'id' => $department->headOffice->id,
                'name' => $department->headOffice->name,
            ],
            'created_at' => $department->created_at,
            'last_updated' => $department->updated_at,
            'version' => $department->version
        ];
    }

    /**
     * Bulk operations
     */
    public function bulkDelete(array $ids): int
    {
        $count = 0;

        $this->validateUuidArray($ids, 'departments');


        foreach ($ids as $id) {
            try {
                $this->delete($id);
                $count++;
            } catch (\Exception $e) {
                // Log error but continue with other records
                Log::warning("Failed to delete department {$id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Check if code exists (excluding specific ID)
     */
    private function codeExists(?string $code, ?string $excludeId = null): bool
    {

        if (!$code) {
            return false;
        }

        $query = Department::where('code', $code);

        if ($excludeId) {
            $this->validateUuid($excludeId);
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if name exists within the same head office (excluding specific ID)
     */
    private function nameExists(string $name, string $headOfficeId, ?string $excludeId = null): bool
    {
        $query = Department::where('name', $name)
            ->where('head_office_id', $headOfficeId);

        if ($excludeId) {
            $this->validateUuid($excludeId);
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Resolve includes for department relationships
     */
    public function resolveIncludes(array $requestedIncludes, $department): void
    {
        $resolved = [];
        $context = []; // Store what was requested for the resource

        foreach ($requestedIncludes as $include) {
            $include = trim($include); // Clean whitespace

            match ($include) {
                'head_office' => $resolved[] = 'headOffice',
                'careers' => $resolved[] = 'careers',
                'hierarchy' => $resolved = array_merge($resolved, [
                    'headOffice',
                    'careers.subsystems',
                ]),
                'statistics' => null, // Statistics don't need additional relationships
                default => null, // Ignora includes no válidos
            };

            // Store the original request for resource context
            $context[] = $include;
        }

        // Load the resolved relationships
        if (!empty($resolved)) {
            $department->load(array_unique($resolved));
        }

        // Store requested includes as an attribute for the resource to check
        $department->setAttribute('_requested_includes', $context);
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
    public function applyFilters(array $filters, Builder $query): void
    {
        $query->when(!empty($filters['search']), function ($q) use ($filters) {
            $search = $filters['search'];
            $q->where(function ($q2) use ($search) {
                $q2->whereRaw('unaccent(name) ILIKE unaccent(?)', ["%{$search}%"])
                    ->orWhereRaw('unaccent(code) ILIKE unaccent(?)', ["%{$search}%"]);
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

        $query->when(!empty($filters['headOfficeId']), function ($q) use ($filters) {
            $q->byheadOffice($filters['headOfficeId']);
        });

        $query->when(!empty($filters['created_by']), function ($q) use ($filters) {
            $q->where('created_by', $filters['created_by']);
        });
        // Apply sorting
        $this->applySorting($query, $filters);
    }
}
