<?php

namespace App\Services;

use App\Constants\HttpStatus;
use App\Models\HeadOffice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Service for Head Office business logic
 *
 * Handles all business operations for head offices including
 * CRUD operations, validation, and complex queries.
 */
class HeadOfficeService
{
    /**
     * Get all active head offices
     */
    public function getAll(array $filters = []): Collection
    {
        $query = HeadOffice::query()->with(['departments']);

        $this->applyFilters($filters, $query);

        return $query->get();
    }
    /**
     * Get paginated head offices
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = HeadOffice::query()->with(['departments']);

        $this->applyFilters($filters, $query);

        return $query->paginate($perPage);
    }

    /**
     * Find head office by ID
     */
    public function findById(string $id): ?HeadOffice
    {
        return HeadOffice::with(['departments.careers'])
            ->findOrFail($id);
    }

    /**
     * Find head office by code
     */
    public function findByCode(string $code): ?HeadOffice
    {
        return HeadOffice::byCode($code)
            ->with(['departments'])
            ->first();
    }

    /**
     * Create new head office
     */
    public function create(array $data): HeadOffice
    {
        // Convert camelCase to snake_case for database operations
        $data = HeadOffice::convertToSnakeCase($data);

        if ($this->codeExists($data['code'])) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        if ($this->nameExists($data['name'])) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe.");
        }

        return HeadOffice::create($data);
    }

    /**
     * Update existing head office
     */
    public function update(string $id, array $data): HeadOffice
    {
        // Convert camelCase to snake_case for database operations
        $data = HeadOffice::convertToSnakeCase($data);

        $headOffice = $this->findById($id);

        if (!$headOffice) {
            throw new ModelNotFoundException("Sede no encontrada.");
        }

        if (isset($data['code']) && $this->codeExists($data['code'], $id)) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        if (isset($data['name']) && $this->nameExists($data['name'], $id)) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe.");
        }

        $headOffice->update($data);

        return $headOffice->fresh(['departments']);
    }

    /**
     * Soft delete head office
     */
    public function delete(string $id): bool
    {
        $headOffice = $this->findById($id);

        if (!$headOffice) {
            throw new ModelNotFoundException("Sede no encontrada.");
        }

        // Check if has active departments
        if ($headOffice->activeDepartments()->exists()) {
            throw new \InvalidArgumentException("No se puede eliminar la sede porque tiene departamentos activos.", HttpStatus::CONFLICT);
        }

        return $headOffice->delete();
    }

    /**
     * Restore soft deleted head office
     */
    public function restore(string $id): HeadOffice
    {
        $headOffice = HeadOffice::withTrashed()->find($id);

        if (!$headOffice) {
            throw new ModelNotFoundException("Sede no encontrada.");
        }

        if (!$headOffice->trashed()) {
            throw new \InvalidArgumentException("La sede no está eliminada.");
        }

        $headOffice->restore();

        return $headOffice->fresh(['departments']);
    }

    /**
     * Get head office hierarchy with all relationships
     */
    public function getFullHierarchy(string $id): ?HeadOffice
    {
        $headOffice = $this->findById($id);

        if (!$headOffice) {
            return null;
        }

        return $headOffice->getFullHierarchy();
    }

    /**
     * Get statistics for head office
     */
    public function getStatistics(string $id): array
    {
        $headOffice = $this->findById($id);

        if (!$headOffice) {
            throw new ModelNotFoundException("Sede no encontrada.");
        }

        return [
            'departments_count' => $headOffice->activeDepartments()->count(),
            'careers_count' => $headOffice->departments()
                ->withCount(['activeCareers'])
                ->get()
                ->sum('active_careers_count'),
            'created_at' => $headOffice->created_at,
            'last_updated' => $headOffice->updated_at,
            'version' => $headOffice->version
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
                Log::warning("Failed to delete head office {$id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Check if code exists (excluding specific ID)
     */
    private function codeExists(string $code, ?string $excludeId = null): bool
    {
        $query = HeadOffice::where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if name exists (excluding specific ID)
     */
    private function nameExists(string $name, ?string $excludeId = null): bool
    {
        $query = HeadOffice::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Resolve includes for head office relationships
     */
    public function resolveIncludes(array $requestedIncludes, $headOffice): void
    {
        $resolved = [];
        $context = []; // Store what was requested for the resource

        foreach ($requestedIncludes as $include) {
            $include = trim($include); // Clean whitespace

            match ($include) {
                'departments' => $resolved[] = 'departments',
                'hierarchy' => $resolved = array_merge($resolved, [
                    'departments.careers.subsystems',
                ]),
                'statistics' => $resolved[] = 'departments', // Load departments for statistics calculations
                default => null,
            };

            // Store the original request for resource context
            $context[] = $include;
        }

        // Load the resolved relationships
        if (!empty($resolved)) {
            $headOffice->load(array_unique($resolved));
        }

        // Store requested includes as an attribute for the resource to check
        $headOffice->setAttribute('_requested_includes', $context);
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
     * Apply filters to the query
     */
    private function applyFilters(array $filters, Builder $query): void
    {
        $query
            ->when($filters['exclude_subsystem_id'] ?? null, function ($q, $id) {
                $q->withoutSubsystemId($id);
            })
            ->when($filters['subsystem_id'] ?? null, function ($q, $id) {
                $q->withSubsystemId($id);
            })
            ->when(isset($filters['has_subsystems']), function ($q) use ($filters) {
                $q->hasSubsystems($filters['has_subsystems']);
            })
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('code', 'ILIKE', "%{$search}%");
                });
            })
            ->when(!empty($filters['code']), function ($q) use ($filters) {
                $q->byCode($filters['code']);
            })
            ->when(!empty($filters['created_by']), function ($q) use ($filters) {
                $q->where('created_by', $filters['created_by']);
            });

        $this->applySorting($query, $filters);
    }
}