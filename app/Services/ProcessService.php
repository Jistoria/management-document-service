<?php

namespace App\Services;

use App\Models\Process;
use App\Models\ProcessCategory;
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
        $data = Process::convertToSnakeCase($data);

        if(!$this->parentExists($data['parent_id'])){
            throw new \InvalidArgumentException("Parent ID does not exist");
        }
        if ($this->codeExists($data['code'])) {
            throw new \InvalidArgumentException("Code already exists");
        }
        if($this->nameExists($data['name'])){
            throw new \InvalidArgumentException("Name already exists");
        }

        return Process::create($data);
    }

    public function update(array $data, string $id): Process
    {
        $data = Process::convertToSnakeCase($data);

        $process = $this->findById($id);

        if ($this->nameExists($data['name'], $id)) {
            throw new \InvalidArgumentException("El nombre '{$data['name']}' ya existe.");
        }

        if ($this->codeExists($data['code'], $id)) {
            throw new \InvalidArgumentException("Code already exists");
        }

        $process->update($data);

        return $process->fresh();
    }

    public function delete(string $id): bool
    {
        $process = $this->findById($id);

        return $process->delete();
    }


    public function findById(string $id): Process
    {
        $this->validateUuid($id);

        return Process::findOrFail($id);
    }

    public function parentExists(string $id): bool
    {
        $query = Process::query()->where('parent_id', $id)->where('deleted_at', NULL);
        return $query->exists();
    }

    public function codeExists(string $code, string|null $exceptId): bool
    {
        $query = Process::query()->where('code', $code)->where('deleted_at', NULL);
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        return $query->exists();
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
