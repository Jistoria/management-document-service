<?php

namespace App\Services;

use App\Models\RequiredDocument;
use App\Traits\ValidatesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Service for Required Document business logic
 *
 * Handles CRUD operations and complex queries for required documents.
 */
class RequiredDocumentService
{
    use ValidatesUuid;


    /**
     * Patrón por defecto para generar el código cuando no se envía 'code_default'.
     * No incluye secuencial. Si en el futuro quieres soporte a {SEQ}, extiende la lógica
     * en generateDefaultCode().
     */
    private const DEFAULT_CODE_PATTERN = '{SUBSYSTEM}-{PROCESS}-{CATEGORY}-{YEAR}';


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
        $query = RequiredDocument::query();
        $this->applyFilters($filters, $query);
        return $query->paginate($perPage);
    }

    /**
     * Find a required document by ID.
     */
    public function findById(string $id): RequiredDocument
    {
        $this->validateUuid($id);
        return RequiredDocument::with(['documentType', 'process', 'metadataSchema.metadataFields'])->findOrFail($id);
    }

    /**
     * Create a new required document.
     */
    public function create(array $data): RequiredDocument
    {
        $data = RequiredDocument::convertToSnakeCase($data);

        $shouldGenerate = (bool)($data['generate_default_code'] ?? false);
        unset($data['generate_default_code']);

        $hasProvidedCode = array_key_exists('code_default', $data) && $data['code_default'] !== null && $data['code_default'] !== '';

        if (!$hasProvidedCode && $shouldGenerate) {
            $tmp = new RequiredDocument($data);
            $data['code_default'] = $this->generateDefaultCode($tmp);
        }

        $requiredDocument = RequiredDocument::create($data);

        return $requiredDocument;
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
            if ($this->validateUuid($id)) {
                $validIds[] = $id;
            }
        }

        if (empty($validIds)) {
            throw new \InvalidArgumentException('No valid IDs provided');
        }

        return RequiredDocument::whereIn('id', $validIds)->delete();
    }

    /**
     * Generate a preview of the default code for a RequiredDocument (sin modificar DB).
     * Útil para un endpoint tipo: GET /required-documents/{id}/code/preview
     */
    public function previewDefaultCode(string $id, ?string $isoDate = null): ?string
    {
        $this->validateUuid($id);

        /** @var RequiredDocument $rd */
        $rd = RequiredDocument::query()
            ->with([
                'process.processCategory.subsystem',
            ])
            ->findOrFail($id);

        return $this->generateDefaultCode($rd, $isoDate ? CarbonImmutable::parse($isoDate) : null, $preview = true);
    }

    /**
     * Genera el code_default por defecto usando {SUBSYSTEM}-{PROCESS}-{CATEGORY}-{YEAR}.
     * - Si faltan relaciones o códigos, devuelve null (no fuerza "NA" en BD).
     * - Si $preview=true, no persiste nada (sólo devuelve el string).
     * - Reutilizable: si mañana agregas secuencial o cambias estructura, extiende aquí.
     */
    public function generateDefaultCode(RequiredDocument $requiredDocument, ?CarbonImmutable $date = null, bool $preview = false): ?string
    {
        $requiredDocument->loadMissing(['process.processCategory.subsystem']);

        $process        = $requiredDocument->process;
        $category       = $process?->processCategory;
        $subsystem      = $category?->subsystem;

        if (!$process || !$category || !$subsystem) {
            return null;
        }

        $processCode   = trim((string) ($process->code ?? ''));
        $categoryCode  = trim((string) ($category->code ?? ''));
        $subsystemCode = trim((string) ($subsystem->code ?? ''));

        if ($processCode === '' || $categoryCode === '' || $subsystemCode === '') {
            return null;
        }

        $date = $date ?? CarbonImmutable::now();

        $pattern = self::DEFAULT_CODE_PATTERN;

        $map = [
            '{SUBSYSTEM}' => $subsystemCode,
            '{PROCESS}'   => $processCode,
            '{CATEGORY}'  => $categoryCode,
            '{YEAR}'      => (string) $date->year,
        ];

        $code = strtr($pattern, $map);

        // Normalización extra si se requiere (por ejemplo, evitar dobles guiones, etc.)
        $code = preg_replace('/-{2,}/', '-', $code ?? '') ?? $code;
        $code = trim($code, "- \t\n\r\0\x0B");

        return $code ?: null;
    }

    /**
     * Determina si debemos autogenerar 'code_default' al crear.
     */
    private function shouldAutogenerateCodeDefault(RequiredDocument $rd, $incomingValue): bool
    {
        if (!array_key_exists('code_default', (array) $incomingValue)) {
            return $this->isEmptyOrSN($rd->code_default);
        }

        // Si sí vino la clave, validamos su contenido
        $val = is_array($incomingValue) ? null : $incomingValue;
        return $this->isEmptyOrSN($val);
    }

    /**
     * Determina si un valor es vacío o "S/N" (case-insensitive, con espacios).
     */
    private function isEmptyOrSN($value): bool
    {
        $val = trim((string) ($value ?? ''));
        return $val === '' || Str::upper($val) === 'S/N';
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
