<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class StorageUnitTypeResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'level' => $this->level,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,

            'storageUnits' => StorageUnitResource::collection($this->whenLoaded('storageUnits')),
            'storageUnitsCount' => $this->when(
                $this->relationLoaded('storageUnits'),
                fn () => $this->storageUnits->count()
            ),
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'level' => $this->level,
        ];
    }

    protected function getResourceType(): string
    {
        return 'storage_unit_type';
    }

    public static function forDropdown($collection): array
    {
        return parent::dropdown($collection);
    }
}
