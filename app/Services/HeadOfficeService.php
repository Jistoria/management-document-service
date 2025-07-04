<?php

namespace App\Services;

use App\Models\HeadOffice;
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
    public function getAll(): Collection
    {
        return HeadOffice::active()
            ->with(['departments'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get paginated head offices
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = HeadOffice::active()->with(['departments']);

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

        if (!empty($filters['created_by'])) {
            $query->createdBy($filters['created_by']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Find head office by ID
     */
    public function findById(string $id): ?HeadOffice
    {
        return HeadOffice::active()
            ->with(['departments.careers'])
            ->find($id);
    }

    /**
     * Find head office by code
     */
    public function findByCode(string $code): ?HeadOffice
    {
        return HeadOffice::active()
            ->byCode($code)
            ->with(['departments'])
            ->first();
    }

    /**
     * Create new head office
     */
    public function create(array $data): HeadOffice
    {
        // Validate unique code
        if ($this->codeExists($data['code'])) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        // Validate unique name
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
        $headOffice = $this->findById($id);

        if (!$headOffice) {
            throw new ModelNotFoundException("Sede no encontrada.");
        }

        // Validate unique code (excluding current record)
        if (isset($data['code']) && $this->codeExists($data['code'], $id)) {
            throw new \InvalidArgumentException("El código '{$data['code']}' ya existe.");
        }

        // Validate unique name (excluding current record)
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
            throw new \InvalidArgumentException("No se puede eliminar la sede porque tiene departamentos activos.");
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
     * Validate head office data
     */
    public function validate(array $data, ?string $excludeId = null): array
    {
        $errors = [];

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido.';
        }

        if (empty($data['code'])) {
            $errors['code'] = 'El código es requerido.';
        }

        // Format validations
        if (!empty($data['code']) && !preg_match('/^[A-Z0-9_-]+$/', $data['code'])) {
            $errors['code'] = 'El código solo puede contener letras mayúsculas, números, guiones y guiones bajos.';
        }

        // Length validations
        if (!empty($data['name']) && strlen($data['name']) > 255) {
            $errors['name'] = 'El nombre no puede exceder 255 caracteres.';
        }

        if (!empty($data['code']) && strlen($data['code']) > 255) {
            $errors['code'] = 'El código no puede exceder 255 caracteres.';
        }

        // Uniqueness validations
        if (!empty($data['code']) && $this->codeExists($data['code'], $excludeId)) {
            $errors['code'] = 'El código ya existe.';
        }

        if (!empty($data['name']) && $this->nameExists($data['name'], $excludeId)) {
            $errors['name'] = 'El nombre ya existe.';
        }

        return $errors;
    }
}
