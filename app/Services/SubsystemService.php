<?php

namespace App\Services;

use App\Constants\HttpStatus;
use App\Models\Subsystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SubsystemService
{
    public function getAll(array $filters = []): Collection
    {
        $query = Subsystem::query()->with(['processCategories']);
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($filters['code'])) {
            $query->where('code', $filters['code']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        $this->applySorting($query, $filters);
        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Subsystem::query()->with(['careers', 'processCategories']);
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($filters['code'])) {
            $query->where('code', $filters['code']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        $this->applySorting($query, $filters);
        return $query->paginate($perPage);
    }

    public function findById(string $id): ?Subsystem
    {
        return Subsystem::with(['careers', 'processCategories.processes'])->find($id);
    }

    public function findByCode(string $code): ?Subsystem
    {
        return Subsystem::with(['careers', 'processCategories'])->where('code', $code)->first();
    }

    public function create(array $data): Subsystem
    {
        $data = Subsystem::convertToSnakeCase($data);
        return Subsystem::create($data);
    }

    public function update(string $id, array $data): Subsystem
    {
        $data = Subsystem::convertToSnakeCase($data);
        $subsystem = Subsystem::find($id);
        if (empty($subsystem)) throw new \Exception('Subsistema no encontrado', code: HttpStatus::NOT_FOUND);

        $subsystem->update($data);
        return $subsystem->fresh(['careers', 'processCategories']);
    }

    public function delete(string $id): bool
    {
        $subsystem = Subsystem::findOrFail($id);
        return $subsystem->delete();
    }

    public function restore(string $id): Subsystem
    {
        $subsystem = Subsystem::withTrashed()->findOrFail($id);
        if (!$subsystem->trashed()) {
            throw new \InvalidArgumentException('El subsistema no está eliminado');
        }
        $subsystem->restore();
        return $subsystem->fresh(['careers', 'processCategories']);
    }

    public function getFullHierarchy(string $id): ?Subsystem
    {
        return Subsystem::with(['careers', 'processCategories.processes'])->find($id);
    }

    public function getStatistics(string $id): array
    {
        $subsystem = Subsystem::withCount(['careers', 'processCategories'])
            ->findOrFail($id);
        return [
            'careers_count' => $subsystem->careers_count,
            'process_categories_count' => $subsystem->process_categories_count,
            'created_at' => $subsystem->created_at?->toISOString(),
            'last_updated' => $subsystem->updated_at?->toISOString(),
            'version' => $subsystem->version,
        ];
    }

    public function bulkDelete(array $ids): int
    {
        $deletedCount = 0;
        foreach ($ids as $id) {
            try {
                $subsystem = Subsystem::findOrFail($id);
                $subsystem->delete();
                $deletedCount++;
            } catch (\Exception $e) {}
        }
        return $deletedCount;
    }

    private function applySorting($query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $allowedSortFields = ['name', 'code', 'created_at', 'updated_at', 'created_by'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
    }
}
