<?php

namespace App\Services;

use App\Models\RequiredDocument;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service for Required Document business logic
 *
 * Handles CRUD operations and complex queries for required documents.
 */
class RequiredDocumentService
{
    use ValidatesUuid;

    /**
     * Retrieve all required documents matching filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = RequiredDocument::query()->with(['documentType', 'process', 'academicRole', 'metadataSchema']);
        $this->applyFilters($filters, $query);
        return $query->get();
    }

    /**
     * Retrieve paginated required documents.
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = RequiredDocument::query()->with(['documentType', 'process', 'academicRole', 'metadataSchema']);
        $this->applyFilters($filters, $query);
        return $query->paginate($perPage);
    }

    /**
     * Find a required document by ID.
     */
    public function findById(string $id): RequiredDocument
    {
        $this->validateUuid($id);
        return RequiredDocument::with(['documentType', 'process', 'academicRole', 'metadataSchema'])->findOrFail($id);
    }

    /**
     * Create a new required document.
     */
    public function create(array $data): RequiredDocument
    {
        $data = RequiredDocument::convertToSnakeCase($data);
        return RequiredDocument::create($data);
    }

    /**
     * Update an existing required document.
     */
    public function update(string $id, array $data): RequiredDocument
    {
        $this->validateUuid($id);
        $data = RequiredDocument::convertToSnakeCase($data);
        $requiredDocument = $this->findById($id);
        $requiredDocument->update($data);
        return $requiredDocument->fresh(['documentType', 'process', 'academicRole', 'metadataSchema']);
    }

    /**
     * Soft delete a required document.
     */
    public function delete(string $id): bool
    {
        $this->validateUuid($id);
        $requiredDocument = $this->findById($id);
        return $requiredDocument->delete();
    }

    /**
     * Restore a previously deleted required document.
     */
    public function restore(string $id): RequiredDocument
    {
        $this->validateUuid($id);
        $requiredDocument = RequiredDocument::withTrashed()->find($id);

        if (!$requiredDocument || !$requiredDocument->trashed()) {
            throw new ModelNotFoundException('Required document not found');
        }

        $requiredDocument->restore();
        return $requiredDocument->fresh(['documentType', 'process', 'academicRole', 'metadataSchema']);
    }

    /**
     * Get statistics for a required document.
     */
    public function getStatistics(string $id): array
    {
        $this->validateUuid($id);
        $requiredDocument = $this->findById($id);

        return [
            'total_documents_for_process' => RequiredDocument::where('process_id', $requiredDocument->process_id)->count(),
            'mandatory_documents_for_process' => RequiredDocument::where('process_id', $requiredDocument->process_id)
                ->where('mandatory', true)
                ->count(),
        ];
    }

    /**
     * Bulk delete required documents by IDs.
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
            throw new \InvalidArgumentException('No valid IDs provided');
        }

        return RequiredDocument::whereIn('id', $validIds)->delete();
    }

    /**
     * Resolve includes for resources.
     */
    public function resolveIncludes(array $requestedIncludes, $requiredDocument): void
    {
        $allowedIncludes = ['documentType', 'process', 'academicRole', 'metadataSchema', 'statistics'];

        foreach ($requestedIncludes as $include) {
            if (in_array($include, $allowedIncludes)) {
                switch ($include) {
                    case 'statistics':
                        $requiredDocument->statistics = $this->getStatistics($requiredDocument->id);
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to query.
     */
    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $allowedSortFields = ['order', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters(array $filters, Builder $query): void
    {
        if (isset($filters['process_id'])) {
            $query->where('process_id', $filters['process_id']);
        }
        if (isset($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }
        if (isset($filters['academic_role_id'])) {
            $query->where('academic_role_id', $filters['academic_role_id']);
        }
        if (isset($filters['metadata_schema_id'])) {
            $query->where('metadata_schema_id', $filters['metadata_schema_id']);
        }
        if (isset($filters['mandatory'])) {
            $query->where('mandatory', filter_var($filters['mandatory'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['external_user_id'])) {
            $query->where('external_user_id', $filters['external_user_id']);
        }
        if (isset($filters['external_organization_id'])) {
            $query->where('external_organization_id', $filters['external_organization_id']);
        }

        $this->applySorting($query, $filters);
    }
}
