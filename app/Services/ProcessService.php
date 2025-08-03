<?php

namespace App\Services;

use App\Models\Process;
use App\Traits\ValidatesUuid;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProcessService
{
    use ValidatesUuid;

    public function getAll(array $filters ): Collection
    {
        $query = Process::query();

        $this->applyFilters($filters, $query);


        return $query->get();
    }

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Process::query();

        $this->applyFilters($filters, $query);

        return $query->paginate($perPage);
    }

    public function create(array $data): Process
    {

    }

    public function codeExists(string $code): bool
    {

    }

    public function nameExists(string $name, ?string $exceptId): bool
    {
        $query = Process::query()->where('name', $name)->where('deleted_at', null);
        if ($exceptId !== null) {
            $this->validateUuid($exceptId, 'exceptId');
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }





    protected function applyFilters(array $filters, Builder $query): void
    {
        $query->when(!empty($filters['search']), function ($q) use ($filters) {
            $search = $filters['search'];
            $q->where(function ($q2) use ($search) {
                $q2->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        });


        $query->when(!empty($filters['process_category_id']), function ($q) use ($filters) {
            $q->byProcessCategory($filters['process_category_id']);
        });

        $query->when(!empty($filters['code']), function ($q) use ($filters) {
            $q->ByCode($filters['code']);
        });

        $query->when(!empty($filters['created_by']), function ($q) use ($filters) {
            $q->where('created_by', $filters['created_by']);
        });
    }
}
