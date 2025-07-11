<?php

namespace App\Services;

use App\Constants\HttpStatus;
use App\Models\Department;
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
    /**
     * Get all active departments
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Department::active()->with(['headOffice', 'careers']);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('code', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['code'])) {
            $query->byCode($filters['code']);
        }

        if (!empty($filters['head_office_id'])) {
            $query->byHeadOffice($filters['head_office_id']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Apply sorting
        $this->applySorting($query, $filters);

        return $query->get();
    }

    /**
     * Get paginated departments
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Department::active()->with(['headOffice', 'careers']);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('code', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['code'])) {
            $query->byCode($filters['code']);
        }

        if (!empty($filters['head_office_id'])) {
            $query->byHeadOffice($filters['head_office_id']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Apply sorting
        $this->applySorting($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Find department by ID
     */
    public function findById(string $id): ?Department
    {
        return Department::active()
            ->with(['headOffice', 'careers.subsystems'])
            ->find($id);
    }

    /**
     * Find department by code
     */
    public function findByCode(string $code): ?Department
    {
        return Department::active()
            ->byCode($code)
            ->with(['headOffice', 'careers'])
            ->first();
    }

    /**
     * Create new department
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
        // Convert camelCase to snake_case for database operations
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
        $department = Department::active()
            ->with(['headOffice', 'careers.subsystems'])
            ->find($id);

        if (!$department) {
            return null;
        }

        return $department;
    }

    /**
     * Get statistics for department
     */
    public function getStatistics(string $id): array
    {
        $department = $this->findById($id);

        if (!$department) {
            throw new ModelNotFoundException("Departamento no encontrado.");
        }

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
}
