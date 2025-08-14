<?php

namespace App\Services;

use App\Constants\HttpStatus;
use App\Models\DocumentType;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Service for Document Type business logic
 *
 * Handles all business operations for document types including
 * CRUD operations, validation, and complex queries.
 */
class DocumentTypeService
{
    use ValidatesUuid;

    /**
     * Get all active document types
     */
    public function getAll(array $filters = []): Collection
    {
        $query = DocumentType::query()->with(['requiredDocuments']);

        $this->applyFilters($filters, $query);

        return $query->get();
    }

    /**
     * Get paginated document types
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = DocumentType::query()->with(['requiredDocuments']);

        $this->applyFilters($filters, $query);

        return $query->paginate($perPage);
    }

    /**
     * Find document type by ID
     */
    public function findById(string $id): ?DocumentType
    {
        $this->validateUuid($id);
        return DocumentType::with(['requiredDocuments'])
            ->find($id);
    }

    /**
     * Find document type by code
     */
    public function findByCode(string $code): ?DocumentType
    {
        return DocumentType::byCode($code)
            ->with(['requiredDocuments'])
            ->first();
    }

    /**
     * Create new document type
     */
    public function create(array $data): DocumentType
    {
        // Convert camelCase to snake_case for database operations
        $data = DocumentType::convertToSnakeCase($data);

        if ($this->codeExists($data['code'])) {
            throw new \InvalidArgumentException('Ya existe un tipo de documento con este código');
        }

        if ($this->nameExists($data['name'])) {
            throw new \InvalidArgumentException('Ya existe un tipo de documento con este nombre');
        }

        return DocumentType::create($data);
    }

    /**
     * Update existing document type
     */
    public function update(string $id, array $data): DocumentType
    {
        $this->validateUuid($id);

        // Convert camelCase to snake_case for database operations
        $data = DocumentType::convertToSnakeCase($data);

        $documentType = $this->findById($id);

        if (!$documentType) {
            throw new ModelNotFoundException('Tipo de documento no encontrado');
        }

        if (isset($data['code']) && $this->codeExists($data['code'], $id)) {
            throw new \InvalidArgumentException('Ya existe un tipo de documento con este código');
        }

        if (isset($data['name']) && $this->nameExists($data['name'], $id)) {
            throw new \InvalidArgumentException('Ya existe un tipo de documento con este nombre');
        }

        $documentType->update($data);

        return $documentType->fresh(['requiredDocuments']);
    }

    /**
     * Soft delete document type
     */
    public function delete(string $id): bool
    {
        $this->validateUuid($id);

        $documentType = $this->findById($id);

        if (!$documentType) {
            throw new ModelNotFoundException('Tipo de documento no encontrado');
        }

        // Check if it has active required documents
        if ($documentType->activeRequiredDocuments()->exists()) {
            throw new \InvalidArgumentException('No se puede eliminar el tipo de documento porque tiene documentos requeridos asociados activos');
        }

        return $documentType->delete();
    }

    /**
     * Restore soft deleted document type
     */
    public function restore(string $id): DocumentType
    {
        $this->validateUuid($id);

        $documentType = DocumentType::withTrashed()->find($id);

        if (!$documentType) {
            throw new ModelNotFoundException('Tipo de documento no encontrado');
        }

        if (!$documentType->trashed()) {
            throw new \InvalidArgumentException('El tipo de documento no está eliminado');
        }

        $documentType->restore();

        return $documentType->fresh(['requiredDocuments']);
    }

    /**
     * Get statistics for document type
     */
    public function getStatistics(string $id): array
    {
        $this->validateUuid($id);

        $documentType = $this->findById($id);

        if (!$documentType) {
            throw new ModelNotFoundException('Tipo de documento no encontrado');
        }

        return [
            'required_documents_count' => $documentType->requiredDocuments()->count(),
            'active_required_documents_count' => $documentType->activeRequiredDocuments()->count(),
            'processes_count' => $documentType->getProcesses()->count(),
        ];
    }

    /**
     * Bulk delete document types
     */
    public function bulkDelete(array $ids): int
    {
        $validIds = [];
        
        foreach ($ids as $id) {
            if ($this->isValidUuid($id)) {
                $validIds[] = $id;
            }
        }

        if (empty($validIds)) {
            throw new \InvalidArgumentException('No se proporcionaron IDs válidos');
        }

        // Check for dependencies
        $documentsWithDependencies = DocumentType::whereIn('id', $validIds)
            ->whereHas('activeRequiredDocuments')
            ->get();

        if ($documentsWithDependencies->isNotEmpty()) {
            $names = $documentsWithDependencies->pluck('name')->implode(', ');
            throw new \InvalidArgumentException("No se pueden eliminar los siguientes tipos de documento porque tienen documentos requeridos asociados: $names");
        }

        return DocumentType::whereIn('id', $validIds)->delete();
    }

    /**
     * Check if code exists
     */
    private function codeExists(string $code, ?string $excludeId = null): bool
    {
        $query = DocumentType::byCode($code);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if name exists
     */
    private function nameExists(string $name, ?string $excludeId = null): bool
    {
        $query = DocumentType::where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Resolve includes for resources
     */
    public function resolveIncludes(array $requestedIncludes, $documentType): void
    {
        $allowedIncludes = ['requiredDocuments', 'statistics'];
        
        foreach ($requestedIncludes as $include) {
            if (in_array($include, $allowedIncludes)) {
                switch ($include) {
                    case 'statistics':
                        $documentType->statistics = $this->getStatistics($documentType->id);
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSortFields = ['name', 'code', 'created_at', 'updated_at'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(array $filters, Builder $query): void
    {
        // Search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Code filter
        if (isset($filters['code']) && !empty($filters['code'])) {
            $query->where('code', 'LIKE', "%{$filters['code']}%");
        }

        // Created by filter
        if (isset($filters['created_by']) && !empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Apply sorting
        $this->applySorting($query, $filters);
    }
}
